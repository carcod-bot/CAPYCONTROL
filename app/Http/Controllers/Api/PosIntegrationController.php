<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CashSession;
use App\Models\CashRegister;
use App\Models\User;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\InventoryAdjustment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PosIntegrationController extends Controller
{
    /**
     * Check if the user has an active cash session AND the client PC is authorized by IP
     */
    public function checkSession(Request $request)
    {
        // For local integration, we expect the frontend to pass the user_id
        $userId = $request->header('X-User-Id');
        
        if (!$userId) {
            return response()->json(['error' => 'Usuario no identificado'], 401);
        }

        $session = CashSession::with('cashRegister')
            ->where('user_id', $userId)
            ->where('status', 'open')
            ->first();

        if (!$session) {
            // No open session — tell CapyPOS to show the "Open Session" screen
            $user = User::find($userId);

            // Try to auto-detect the register for this PC by IP / Hostname
            $clientIp       = $request->ip();
            $clientHostname = $request->header('X-Hostname', '');

            $register = CashRegister::where('active', true)
                ->where(function ($q) use ($clientIp, $clientHostname) {
                    $q->where('ip_address', $clientIp)
                      ->orWhere(function ($q2) use ($clientHostname) {
                          if ($clientHostname) {
                              $q2->whereRaw('LOWER(hostname) = ?', [strtolower($clientHostname)]);
                          }
                      });
                })
                ->whereDoesntHave('sessions', fn($q) => $q->where('status', 'open'))
                ->first();

            // Fallback: any active register without open session
            if (!$register) {
                $allFreeRegisters = CashRegister::where('active', true)
                    ->whereDoesntHave('sessions', fn($q) => $q->where('status', 'open'))
                    ->orderBy('number')
                    ->get(['id', 'number', 'name']);
            } else {
                $allFreeRegisters = collect();
            }

            // Load Currencies and Payment Methods for POS early
            $currencies = \App\Models\Currency::where('is_active', true)
                ->where('used_in_pos', true)
                ->get();
            $paymentMethods = \App\Models\PaymentMethod::with('currency')
                ->where('used_in_pos', true)
                ->get();

            return response()->json([
                'success'          => false,
                'needs_opening'    => true,
                'user_role'        => $user ? $user->role : 'cashier',
                'register'         => $register ? ['id' => $register->id, 'number' => $register->number, 'name' => $register->name] : null,
                'free_registers'   => $allFreeRegisters->values(),
                'message'          => 'Sin turno abierto.',
                'pos_config'       => [
                    'currencies'      => $currencies,
                    'payment_methods' => $paymentMethods,
                    'tax_type'        => \App\Models\Setting::get('tax_type', 'percentage'),
                    'tax_amount'      => \App\Models\Setting::get('tax_amount', '16.00'),
                    'tax_included'    => \App\Models\Setting::get('tax_included', 'false'),
                ]
            ], 200);
        }

        // Access Control: validate IP and/or Hostname if registered on the cash register
        $register = $session->cashRegister;
        if ($register) {
            $clientIp       = $request->ip();
            $clientHostname = $request->header('X-Hostname', '');

            // When CapyPOS and CapyControl run on the same PC (XAMPP local),
            // Laravel sees the request as coming from loopback (::1 / 127.0.0.1).
            // In that case, skip IP validation and rely only on hostname.
            $isLoopback = in_array($clientIp, ['::1', '127.0.0.1', '::ffff:127.0.0.1']);

            $hasIp       = !empty($register->ip_address) && !$isLoopback;
            $hasHostname = !empty($register->hostname);

            if ($hasIp || $hasHostname) {
                $ipOk       = !$hasIp       || (trim($register->ip_address) === $clientIp);
                $hostnameOk = !$hasHostname  || (strtolower(trim($register->hostname)) === strtolower(trim($clientHostname)));

                if (!$ipOk || !$hostnameOk) {
                    $expected = [];
                    if (!empty($register->ip_address)) $expected[] = "IP: {$register->ip_address}";
                    if ($hasHostname)                  $expected[] = "Hostname: {$register->hostname}";

                    $got = [];
                    if (!empty($register->ip_address)) $got[] = "IP: {$clientIp}" . ($isLoopback ? ' (loopback — validación omitida)' : '');
                    if ($hasHostname)                  $got[] = "Hostname: " . ($clientHostname ?: 'no enviado');

                    return response()->json([
                        'success'  => false,
                        'message'  => "Este PC no está autorizado para la Caja {$register->number}. Esperado: " . implode(', ', $expected) . ". Recibido: " . implode(', ', $got) . ".",
                        'ip_error' => true,
                    ], 403);
                }
            }
        }

        // Load Currencies and Payment Methods for POS
        $currencies = \App\Models\Currency::where('is_active', true)
            ->where('used_in_pos', true)
            ->get();
        $paymentMethods = \App\Models\PaymentMethod::with('currency')
            ->where('used_in_pos', true)
            ->get();

        return response()->json([
            'success'   => true,
            'user_role' => optional(User::find($userId))->role ?? 'cashier',
            'session'   => [
                'id'             => $session->id,
                'register'       => $register ? ($register->name ?? $register->number) : '—',
                'register_number'=> $register ? $register->number : '—',
                'hostname'       => $register ? $register->hostname : null,
                'turn_number'    => $session->turn_number,
            ],
            'pos_config' => [
                'currencies'      => $currencies,
                'payment_methods' => $paymentMethods,
                'tax_type'        => \App\Models\Setting::get('tax_type', 'percentage'),
                'tax_amount'      => \App\Models\Setting::get('tax_amount', '16.00'),
                'tax_included'    => \App\Models\Setting::get('tax_included', 'false'),
            ]
        ]);
    }

    /**
     * Open a new cash session from CapyPOS.
     * - Admin users: open directly.
     * - Cashier users: require supervisor credentials (admin username + password).
     */
    public function openSession(Request $request)
    {
        $userId = $request->header('X-User-Id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Usuario no identificado.'], 401);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Usuario no encontrado.'], 404);
        }

        $request->validate([
            'cash_register_id' => 'required|exists:cash_registers,id',
            'opening_amount'   => 'required|numeric|min:0',
        ]);

        // --- CASHIER: needs supervisor authorization ---
        if (!$user->isAdmin()) {
            $request->validate([
                'supervisor_username' => 'required|string',
                'supervisor_password' => 'required|string',
            ]);

            $supervisor = User::where('username', $request->supervisor_username)
                ->where('role', 'admin')
                ->first();

            if (!$supervisor || !Hash::check($request->supervisor_password, $supervisor->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciales del supervisor incorrectas o el usuario no es administrador.',
                ], 403);
            }
        }

        // --- Check register is available ---
        $register = CashRegister::where('id', $request->cash_register_id)
            ->where('active', true)
            ->first();

        if (!$register) {
            return response()->json(['success' => false, 'message' => 'Caja no encontrada o inactiva.'], 404);
        }

        $alreadyOpen = CashSession::where('cash_register_id', $register->id)
            ->where('status', 'open')
            ->exists();

        if ($alreadyOpen) {
            return response()->json(['success' => false, 'message' => 'Esta caja ya tiene una sesión abierta.'], 409);
        }

        // --- Create session ---
        $lastTurn = CashSession::where('cash_register_id', $register->id)
            ->max('turn_number') ?? 0;

        $session = CashSession::create([
            'cash_register_id' => $register->id,
            'user_id'          => $userId,
            'status'           => 'open',
            'turn_number'      => $lastTurn + 1,
            'opening_amount'   => $request->opening_amount,
            'expected_amount'  => $request->opening_amount,
            'opened_at'        => now(),
        ]);

        // Load Currencies and Payment Methods for POS
        $currencies = \App\Models\Currency::where('is_active', true)
            ->where('used_in_pos', true)
            ->get();
        $paymentMethods = \App\Models\PaymentMethod::with('currency')
            ->where('used_in_pos', true)
            ->get();
        
        $posConfig = [
            'currencies'      => $currencies,
            'payment_methods' => $paymentMethods,
            'tax_type'        => \App\Models\Setting::get('tax_type', 'percentage'),
            'tax_amount'      => \App\Models\Setting::get('tax_amount', '16.00'),
            'tax_included'    => \App\Models\Setting::get('tax_included', 'false'),
        ];

        return response()->json([
            'success'     => true,
            'session_id'  => $session->id,
            'turn_number' => $session->turn_number,
            'user'        => $session->user->name,
            'user_role'   => $session->user->role ?? 'cashier',
            'register'    => $register ? $register->name : 'N/A',
            'pos_config'  => $posConfig
        ], 200);
    }

    /**
     * Process a sale from CapyPOS
     */
    public function storeSale(Request $request)
    {
        $userId = $request->header('X-User-Id');
        
        if (!$userId) {
            return response()->json(['error' => 'Usuario no identificado'], 401);
        }

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.code' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.price' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'payments' => 'required|array|min:1',
            'payments.*.payment_method_id' => 'required|exists:payment_methods,id',
            'payments.*.currency_id' => 'required|exists:currencies,id',
            'payments.*.amount_base' => 'required|numeric|min:0',
            'payments.*.amount_local' => 'required|numeric|min:0',
            'payments.*.exchange_rate' => 'required|numeric|min:0',
            'new_customer' => 'nullable|array',
            'new_customer.name' => 'required_with:new_customer|string|max:255',
            'new_customer.document_id' => 'required_with:new_customer|string|max:100',
        ]);

        try {
            DB::beginTransaction();

            // 1. Verify Session
            $session = CashSession::where('user_id', $userId)
                ->where('status', 'open')
                ->lockForUpdate()
                ->first();

            if (!$session) {
                throw new \Exception('No se encontró un turno abierto activo.');
            }

            // 2. Process Payments
            $totalTenderedBase = 0;
            $paymentMethodIds = [];
            
            foreach ($request->payments as $p) {
                $totalTenderedBase += $p['amount_base'];
                $paymentMethodIds[] = $p['payment_method_id'];
            }

            $primaryPaymentMethodId = count($paymentMethodIds) === 1 ? $paymentMethodIds[0] : null;

            // 2.5 Handle Customer
            $customerId = $request->customer_id;

            if ($request->has('new_customer') && !empty($request->new_customer)) {
                $newCust = \App\Models\Customer::firstOrCreate(
                    ['document_id' => $request->new_customer['document_id']],
                    [
                        'name' => $request->new_customer['name'],
                        'phone' => $request->new_customer['phone'] ?? null,
                        'email' => $request->new_customer['email'] ?? null,
                    ]
                );
                $customerId = $newCust->id;
            }

            // 3. Create Sale
            $changeAmount = max(0, $totalTenderedBase - $request->total_amount);

            $sale = Sale::create([
                'cash_session_id' => $session->id,
                'user_id' => $userId,
                'customer_id' => $customerId,
                'payment_method_id' => $primaryPaymentMethodId,
                'total_amount' => $request->total_amount,
                'tax_amount' => $request->tax_amount ?? 0,
                'tendered_amount' => $totalTenderedBase,
                'change_amount' => $changeAmount,
                'ticket_number' => Sale::generateTicketNumber(),
            ]);

            // Save individual payments
            foreach ($request->payments as $p) {
                \App\Models\SalePayment::create([
                    'sale_id' => $sale->id,
                    'payment_method_id' => $p['payment_method_id'],
                    'currency_id' => $p['currency_id'],
                    'payment_method_name' => $p['payment_method_name'] ?? 'Desconocido',
                    'amount_base' => $p['amount_base'],
                    'amount_local' => $p['amount_local'],
                    'exchange_rate' => $p['exchange_rate'],
                ]);
            }

            $calculatedTotal = 0;

            // 3. Process Items and update Stock
            foreach ($request->items as $itemData) {
                // Find product by private code or EAN
                $product = Product::where('private_code', $itemData['code'])
                    ->orWhere('ean_code', $itemData['code'])
                    ->lockForUpdate()
                    ->first();

                if (!$product) {
                    throw new \Exception("Producto no encontrado con código: {$itemData['code']}");
                }

                $qty = $itemData['quantity'];
                $subtotal = $qty * $itemData['price'];
                $calculatedTotal += $subtotal;

                // Create Sale Item
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_code' => $product->private_code,
                    'quantity' => $qty,
                    'unit_price' => $itemData['price'],
                    'subtotal' => $subtotal,
                ]);

                // Update Stock
                $previousStock = $product->stock;
                $newStock = $previousStock - $qty;
                
                if ($newStock < 0) {
                    throw new \Exception("Stock insuficiente para el producto: {$product->name}. Disponible: {$previousStock}");
                }

                $product->stock = $newStock;
                $product->save();

                // FIFO Batch Deduction
                $remainingToDeduct = $qty;
                $activeBatches = $product->getActiveBatches();
                $affectedBatches = [];
                foreach ($activeBatches as $batch) {
                    if ($remainingToDeduct <= 0) break;
                    
                    if ($batch->current_quantity >= $remainingToDeduct) {
                        $batch->current_quantity -= $remainingToDeduct;
                        $batch->save();
                        $affectedBatches[$batch->id] = ['quantity' => $remainingToDeduct];
                        $remainingToDeduct = 0;
                    } else {
                        $deductedHere = $batch->current_quantity;
                        $remainingToDeduct -= $deductedHere;
                        $batch->current_quantity = 0;
                        $batch->save();
                        if ($deductedHere > 0) {
                            $affectedBatches[$batch->id] = ['quantity' => $deductedHere];
                        }
                    }
                }

                // Record Inventory Adjustment for the sale
                $adj = InventoryAdjustment::create([
                    'product_id' => $product->id,
                    'user_id' => $userId,
                    'type' => 'out',
                    'quantity' => $qty,
                    'previous_stock' => $previousStock,
                    'new_stock' => $newStock,
                    'reason' => "Venta {$sale->ticket_number}",
                ]);

                if (count($affectedBatches) > 0) {
                    $adj->batches()->attach($affectedBatches);
                }
            }

            // 4. Update Cash Session totals
            $session->total_sales += 1;
            if ($request->payment_method === 'efectivo' || $request->payment_method === 'cash') {
                 $session->expected_amount += $request->total_amount;
            }
            $session->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta procesada exitosamente',
                'sale' => [
                    'ticket_number' => $sale->ticket_number,
                    'total' => $sale->total_amount,
                    'change' => $sale->change_amount
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la venta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search Customers
     */
    public function searchCustomers(Request $request)
    {
        $term = $request->input('term');
        
        $query = \App\Models\Customer::query();
        if ($term) {
            $query->where('name', 'LIKE', "%{$term}%")
                  ->orWhere('document_id', 'LIKE', "%{$term}%")
                  ->orWhere('phone', 'LIKE', "%{$term}%");
        }
        
        return response()->json([
            'success' => true,
            'customers' => $query->limit(20)->get()
        ]);
    }

    /**
     * Store a new Customer
     */
    public function storeCustomer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'document_id' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        $customer = \App\Models\Customer::firstOrCreate(
            ['document_id' => $request->document_id],
            [
                'name' => $request->name,
                'phone' => $request->phone ?? null,
                'email' => $request->email ?? null,
                'address' => $request->address ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'customer' => $customer
        ]);
    }

    /**
     * Withdraw Cash (F11 - Retiro de Efectivo)
     */
    public function withdrawCash(Request $request)
    {
        $userId = $request->header('X-User-Id');
        
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
            'payment_method_id' => 'required|exists:payment_methods,id',
        ]);

        try {
            DB::beginTransaction();

            $session = CashSession::where('user_id', $userId)
                ->where('status', 'open')
                ->lockForUpdate()
                ->first();

            if (!$session) {
                throw new \Exception('No hay turno abierto.');
            }

            $method = \App\Models\PaymentMethod::with('currency')->find($request->payment_method_id);
            $exchangeRate = $method && $method->currency ? $method->currency->exchange_rate : 1;
            $baseCurrency = \App\Models\Currency::where('is_base', true)->first();
            $baseRate = $baseCurrency ? $baseCurrency->exchange_rate : 1;

            $amountBase = ($request->amount * $exchangeRate) / $baseRate;

            \App\Models\CashMovement::create([
                'cash_session_id' => $session->id,
                'user_id' => $userId,
                'type' => 'withdrawal',
                'amount' => $request->amount,
                'reason' => $request->reason,
                'payment_method_id' => $request->payment_method_id,
            ]);

            $session->expected_amount -= $amountBase;
            $session->total_withdrawals += 1;
            $session->save();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Retiro registrado.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get Declaration Totals for non-auto-declare payment methods
     */
    public function getDeclarationTotals(Request $request)
    {
        $userId = $request->header('X-User-Id');
        
        $session = CashSession::where('user_id', $userId)
            ->where('status', 'open')
            ->first();

        if (!$session) {
            return response()->json(['success' => false, 'message' => 'No hay turno abierto.'], 404);
        }

        // Get all payment methods that do not auto declare
        $methods = \App\Models\PaymentMethod::with('currency')
            ->where('used_in_pos', true)
            ->where('auto_declare', false)
            ->get();

        $totals = [];
        $openingAndWithdrawalsApplied = false;

        foreach ($methods as $method) {
            // Calculate total from sale_payments for this session using amount_local (which is the actual currency of the payment method)
            $totalLocal = \App\Models\SalePayment::whereHas('sale', function ($query) use ($session) {
                $query->where('cash_session_id', $session->id);
            })
            ->where('payment_method_id', $method->id)
            ->sum('amount_local');

            // Apply opening amount to the main cash method
            if (!$openingAndWithdrawalsApplied && (str_contains(strtolower($method->description), 'efectivo') || str_contains(strtolower($method->description), 'cash'))) {
                $totalLocal += $session->opening_amount;
                
                // Subtract withdrawals that don't have a payment_method_id (legacy/fallback)
                $legacyWithdrawals = \App\Models\CashMovement::where('cash_session_id', $session->id)
                    ->where('type', 'withdrawal')
                    ->whereNull('payment_method_id')
                    ->sum('amount');
                $totalLocal -= $legacyWithdrawals;
                $openingAndWithdrawalsApplied = true;
            }

            // Subtract specific withdrawals for this payment method
            $specificWithdrawals = \App\Models\CashMovement::where('cash_session_id', $session->id)
                ->where('type', 'withdrawal')
                ->where('payment_method_id', $method->id)
                ->sum('amount');
            $totalLocal -= $specificWithdrawals;

            // Let's pass the expected amount
            $exchangeRate = $method->currency ? $method->currency->exchange_rate : 1;
            $baseCurrency = \App\Models\Currency::where('is_base', true)->first();
            $baseRate = $baseCurrency ? $baseCurrency->exchange_rate : 1;

            $totals[] = [
                'payment_method_id' => $method->id,
                'name' => $method->description,
                'currency_symbol' => $method->currency ? $method->currency->symbol : '$',
                'expected_base' => ($totalLocal * $exchangeRate) / $baseRate, // Optional backward compatibility if needed
                'expected_local' => $totalLocal, // This is the EXACT amount the cashier needs to count in this currency
                'exchange_rate' => $exchangeRate,
            ];
        }

        return response()->json([
            'success' => true,
            'totals' => $totals
        ]);
    }

    /**
     * Close Session (F11 - Reporte Z / Cierre)
     */
    public function closeSession(Request $request)
    {
        $userId = $request->header('X-User-Id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Usuario no identificado.'], 401);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Usuario no encontrado.'], 404);
        }

        $request->validate([
            'declarations' => 'nullable|array',
            'declarations.*.payment_method_id' => 'required|exists:payment_methods,id',
            'declarations.*.declared_amount' => 'required|numeric|min:0',
            'closing_notes' => 'nullable|string',
        ]);

        // --- CASHIER: needs supervisor authorization ---
        if (!$user->isAdmin()) {
            $request->validate([
                'supervisor_username' => 'required|string',
                'supervisor_password' => 'required|string',
            ]);

            $supervisor = User::where('username', $request->supervisor_username)
                ->where('role', 'admin')
                ->first();

            if (!$supervisor || !Hash::check($request->supervisor_password, $supervisor->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciales del supervisor incorrectas o el usuario no es administrador.',
                ], 403);
            }
        }

        try {
            DB::beginTransaction();

            $session = CashSession::where('user_id', $userId)
                ->where('status', 'open')
                ->lockForUpdate()
                ->first();

            if (!$session) {
                throw new \Exception('No hay turno abierto.');
            }

            $totalDeclaredBase = 0;
            if ($request->has('declarations')) {
                foreach ($request->declarations as $dec) {
                    $method = \App\Models\PaymentMethod::find($dec['payment_method_id']);
                    // Simple sum of base amounts for the global session totals
                    // (You could also save the detailed declarations in a new table if desired)
                    $totalDeclaredBase += $dec['declared_amount'];
                }
            } else {
                // Backward compatibility or no methods to declare
                $totalDeclaredBase = $request->actual_amount ?? $session->expected_amount;
            }

            $session->actual_amount = $totalDeclaredBase;
            $session->difference = $totalDeclaredBase - $session->expected_amount;
            $session->declarations_data = $request->has('declarations') ? json_encode($request->declarations) : null;
            
            if ($request->action === 'declare') {
                // Solo declarar, el turno sigue abierto
                $session->save();
                DB::commit();
                return response()->json(['success' => true, 'message' => 'Declaración guardada exitosamente. Turno sigue abierto.', 'difference' => $session->difference]);
            }

            $session->status = 'closed';
            $session->closed_at = now();
            $session->closing_notes = $request->closing_notes;
            $session->save();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Turno de caja cerrado exitosamente.', 'difference' => $session->difference]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
