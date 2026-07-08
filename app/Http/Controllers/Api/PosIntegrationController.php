<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CashSession;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\InventoryAdjustment;
use Illuminate\Support\Facades\DB;

class PosIntegrationController extends Controller
{
    /**
     * Check if the user has an active cash session
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
            return response()->json([
                'success' => false,
                'message' => 'No tienes un turno de caja abierto. Por favor, abre uno en CapyControl.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'session' => [
                'id' => $session->id,
                'register' => $session->cashRegister->name,
                'turn_number' => $session->turn_number,
            ]
        ]);
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
            'tendered_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
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

            // 2. Create Sale
            $changeAmount = max(0, $request->tendered_amount - $request->total_amount);

            $sale = Sale::create([
                'cash_session_id' => $session->id,
                'user_id' => $userId,
                'payment_method' => $request->payment_method,
                'total_amount' => $request->total_amount,
                'tendered_amount' => $request->tendered_amount,
                'change_amount' => $changeAmount,
                'ticket_number' => Sale::generateTicketNumber(),
            ]);

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
                $product->stock = $newStock;
                $product->save();

                // Record Inventory Adjustment for the sale
                InventoryAdjustment::create([
                    'product_id' => $product->id,
                    'user_id' => $userId,
                    'type' => 'out',
                    'quantity' => $qty,
                    'previous_stock' => $previousStock,
                    'new_stock' => $newStock,
                    'reason' => "Venta {$sale->ticket_number}",
                ]);
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
}
