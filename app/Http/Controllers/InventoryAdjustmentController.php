<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\InventoryAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryAdjustmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $isStockView = false;
        $stockProducts = null;
        $adjustments = null;

        if ($request->type === 'stock') {
            $isStockView = true;
            $query = Product::with(['category', 'brand']);
            
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('private_code', 'like', "%{$search}%")
                      ->orWhere('ean_code', 'like', "%{$search}%");
                });
            }

            if ($request->filled('batch')) {
                $batchSearch = $request->batch;
                $query->whereHas('batches', function($q) use ($batchSearch) {
                    $q->where('batch_number', 'like', "%{$batchSearch}%");
                });
            }

            $stockProducts = $query->orderBy('name')->paginate(20)->withQueryString();
        } else {
            $query = InventoryAdjustment::with(['product', 'user', 'batches'])->orderBy('created_at', 'desc');

            if ($request->filled('type')) {
                if ($request->type === 'finished_batches') {
                    $query->whereHas('batches', function($q) {
                        $q->where('current_quantity', '<=', 0);
                    })->whereIn('type', ['in', 'set']);
                } else {
                    $query->where('type', $request->type);
                }
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('product', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('private_code', 'like', "%{$search}%")
                      ->orWhere('ean_code', 'like', "%{$search}%");
                });
            }

            if ($request->filled('batch')) {
                $batchSearch = $request->batch;
                $query->whereHas('batches', function($q) use ($batchSearch) {
                    $q->where('batch_number', 'like', "%{$batchSearch}%");
                });
            }

            $adjustments = $query->paginate(20)->withQueryString();
        }

        $brands = \App\Models\Brand::where('active', true)->orderBy('name')->get();
        $providers = \App\Models\Provider::where('active', true)->orderBy('name')->get();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'isStockView' => $isStockView,
                'data' => $isStockView ? $stockProducts : $adjustments
            ]);
        }

        return view('inventory.adjustments.index', compact('adjustments', 'brands', 'providers', 'isStockView', 'stockProducts'));
    }

    private function generateBatchNumber($increment = true)
    {
        $mode = \App\Models\Setting::get('batch_generation_mode', 'auto_date');
        $prefix = \App\Models\Setting::get('default_batch_prefix', '');

        if ($mode === 'sequential') {
            $next = \App\Models\Setting::get('batch_next_number', '1');
            if ($increment) {
                \App\Models\Setting::set('batch_next_number', $next + 1);
            }
            return $prefix . $next;
        }

        $autoPrefix = \App\Models\Setting::get('default_batch_prefix', 'LOTE-');
        return $autoPrefix . date('Ymd-His') . '-' . strtoupper(substr(uniqid(), -4));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:in,out,set',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric|min:0.001',
            'products.*.batch_number' => 'nullable|string|max:100',
            'products.*.expiry_date' => 'nullable|date',
            'products.*.brand_id' => 'nullable|exists:brands,id',
            'products.*.provider_id' => 'nullable|exists:providers,id',
        ]);

        try {
            DB::beginTransaction();

            $createdAdjustments = [];

            foreach ($request->products as $prodInput) {
                $product = Product::lockForUpdate()->findOrFail($prodInput['product_id']);
                $previousStock = $product->stock;
                $newStock = $previousStock;
                $qty = $prodInput['quantity'];

                $affectedBatches = [];

                if ($request->type === 'in') {
                    $newStock = $previousStock + $qty;
                    
                    $batchNumber = !empty($prodInput['batch_number']) ? $prodInput['batch_number'] : \App\Models\Setting::get('default_batch_prefix', 'LOTE-') . date('Ymd-His') . '-' . strtoupper(substr(uniqid(), -4));
                    
                    // Create a batch
                    $batch = $product->batches()->create([
                        'batch_number' => $batchNumber,
                        'provider_id' => $prodInput['provider_id'] ?? $product->provider_id,
                        'brand_id' => $prodInput['brand_id'] ?? $product->brand_id,
                        'expiry_date' => $prodInput['expiry_date'] ?? null,
                        'initial_quantity' => $qty,
                        'current_quantity' => $qty,
                    ]);
                    $affectedBatches[$batch->id] = ['quantity' => $qty];

                } elseif ($request->type === 'out') {
                    if ($previousStock < $qty) {
                        throw new \Exception("El stock actual del producto '{$product->name}' es menor que la cantidad a retirar.");
                    }
                    $newStock = $previousStock - $qty;
                    
                    // FIFO deduction
                    $remainingToDeduct = $qty;
                    $activeBatches = $product->getActiveBatches();
                    foreach ($activeBatches as $batch) {
                        if ($remainingToDeduct <= 0) break;
                        
                        if ($batch->current_quantity >= $remainingToDeduct) {
                            $batch->current_quantity -= $remainingToDeduct;
                            $batch->save();
                            $affectedBatches[$batch->id] = ['quantity' => $remainingToDeduct];
                            $remainingToDeduct = 0;
                        } else {
                            $deducted = $batch->current_quantity;
                            $remainingToDeduct -= $deducted;
                            $batch->current_quantity = 0;
                            $batch->save();
                            $affectedBatches[$batch->id] = ['quantity' => $deducted];
                        }
                    }

                } elseif ($request->type === 'set') {
                    $newStock = $qty; 
                    $difference = $newStock - $previousStock;
                    
                    if ($difference > 0) {
                        // Create adjustment batch for surplus
                        $batch = $product->batches()->create([
                            'batch_number' => 'AJUSTE-' . date('Ymd-His'),
                            'provider_id' => $product->provider_id,
                            'brand_id' => $product->brand_id,
                            'initial_quantity' => $difference,
                            'current_quantity' => $difference,
                        ]);
                        $affectedBatches[$batch->id] = ['quantity' => $difference];
                    } elseif ($difference < 0) {
                        // FIFO deduction for deficit
                        $remainingToDeduct = abs($difference);
                        $activeBatches = $product->getActiveBatches();
                        foreach ($activeBatches as $batch) {
                            if ($remainingToDeduct <= 0) break;
                            
                            if ($batch->current_quantity >= $remainingToDeduct) {
                                $batch->current_quantity -= $remainingToDeduct;
                                $batch->save();
                                $affectedBatches[$batch->id] = ['quantity' => $remainingToDeduct];
                                $remainingToDeduct = 0;
                            } else {
                                $deducted = $batch->current_quantity;
                                $remainingToDeduct -= $deducted;
                                $batch->current_quantity = 0;
                                $batch->save();
                                $affectedBatches[$batch->id] = ['quantity' => $deducted];
                            }
                        }
                    }
                    $qty = abs($difference); 
                }

                // Create adjustment record
                $adjustment = InventoryAdjustment::create([
                    'product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'type' => $request->type,
                    'quantity' => $request->type === 'set' ? $qty : $prodInput['quantity'],
                    'previous_stock' => $previousStock,
                    'new_stock' => $newStock,
                    'reason' => $request->reason,
                    'notes' => $request->notes,
                ]);

                if (!empty($affectedBatches)) {
                    $adjustment->batches()->sync($affectedBatches);
                }

                // Update product stock
                $product->stock = $newStock;
                $product->save();
                
                $createdAdjustments[] = $adjustment;
            }

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ajustes registrados exitosamente.',
                ]);
            }

            return redirect()->back()->with('success', 'Ajustes de inventario registrados correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al procesar los ajustes: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Error al registrar los ajustes: ' . $e->getMessage());
        }
    }

    /**
     * Search products for adjustment modal (AJAX)
     */
    public function searchProducts(Request $request)
    {
        $term = $request->term;
        if (!$term) {
            return response()->json([]);
        }

        $products = Product::where('active', true)
            ->where(function($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('private_code', 'like', "%{$term}%")
                  ->orWhere('ean_code', 'like', "%{$term}%");
            })
            ->select('id', 'name', 'private_code', 'stock')
            ->take(10)
            ->get();

        return response()->json($products);
    }

    public function getBatchLifecycle($id)
    {
        $adjustment = InventoryAdjustment::with('batches')->findOrFail($id);
        
        $batchesInfo = [];
        foreach ($adjustment->batches as $batch) {
            $allAdjustments = $batch->inventoryAdjustments()->get();

            $sold = 0;
            $damaged = 0;
            $totalIn = 0;
            $totalSetPos = 0;
            $recounted = false;

            foreach ($allAdjustments as $adj) {
                if ($adj->type === 'out') {
                    if (stripos($adj->reason, 'Venta') !== false) {
                        $sold += $adj->pivot->quantity;
                    } else {
                        $damaged += $adj->pivot->quantity;
                    }
                } elseif ($adj->type === 'set') {
                    if ($adj->pivot->quantity > 0) {
                        $totalSetPos += $adj->pivot->quantity;
                    } else {
                        $recounted = true;
                    }
                } elseif ($adj->type === 'in') {
                    $totalIn += $adj->pivot->quantity;
                }
            }
            
            $batchesInfo[] = [
                'batch_number' => $batch->batch_number,
                'expiry_date' => $batch->expiry_date ? $batch->expiry_date->format('d/m/Y') : null,
                'initial' => $totalIn + $totalSetPos,
                'sold' => abs($sold),
                'damaged' => abs($damaged),
                'recounted' => $recounted,
                'current' => $batch->current_quantity
            ];
        }

        $saleData = null;
        if ($adjustment->type === 'out' && str_starts_with($adjustment->reason, 'Venta ')) {
            $ticket = str_replace('Venta ', '', $adjustment->reason);
            $sale = \App\Models\Sale::with(['user', 'items'])->where('ticket_number', $ticket)->first();
            if ($sale) {
                $saleData = [
                    'ticket' => $sale->ticket_number,
                    'date' => $sale->created_at->format('d/m/Y h:i A'),
                    'user' => $sale->user ? $sale->user->username : 'N/A',
                    'total' => $sale->total_amount,
                    'payment' => $sale->payment_method
                ];
            }
        }

        return response()->json([
            'batches' => $batchesInfo,
            'sale' => $saleData,
            'is_sale' => $saleData !== null
        ]);
    }

    public function editBatches($id)
    {
        $adjustment = InventoryAdjustment::with('batches')->findOrFail($id);
        
        $providers = \App\Models\Provider::orderBy('name')->get(['id', 'name']);
        $brands = \App\Models\Brand::orderBy('name')->get(['id', 'name']);
        $defaultPrefix = \App\Models\Setting::get('default_batch_prefix', 'LOTE-');

        return response()->json([
            'adjustment_id' => $adjustment->id,
            'type' => $adjustment->type,
            'batches' => $adjustment->batches,
            'providers' => $providers,
            'brands' => $brands,
            'default_batch' => $defaultPrefix . date('Ymd-His') . '-' . strtoupper(substr(uniqid(), -4))
        ]);
    }

    public function updateBatches(Request $request, $id)
    {
        $request->validate([
            'batches' => 'required|array',
            'batches.*.id' => 'required',
            'batches.*.batch_number' => 'required|string|max:100',
            'batches.*.expiry_date' => 'nullable|date',
            'batches.*.provider_id' => 'nullable|exists:providers,id',
            'batches.*.brand_id' => 'nullable|exists:brands,id',
        ]);

        $adjustment = InventoryAdjustment::findOrFail($id);
        $adjustmentBatchIds = $adjustment->batches()->pluck('product_batches.id')->toArray();

        foreach ($request->batches as $batchData) {
            if ($batchData['id'] === 'new') {
                $batch = $adjustment->product->batches()->create([
                    'batch_number' => $batchData['batch_number'],
                    'provider_id' => $batchData['provider_id'] ?: null,
                    'brand_id' => $batchData['brand_id'] ?: null,
                    'expiry_date' => $batchData['expiry_date'] ?: null,
                    'initial_quantity' => $adjustment->quantity,
                    'current_quantity' => $adjustment->quantity, // Asumiendo que todo el stock está ahí, ya que es un ajuste antiguo.
                ]);
                $adjustment->batches()->attach($batch->id, ['quantity' => $adjustment->quantity]);
            } elseif (in_array($batchData['id'], $adjustmentBatchIds)) {
                $batch = \App\Models\ProductBatch::find($batchData['id']);
                $batch->update([
                    'batch_number' => $batchData['batch_number'],
                    'expiry_date' => $batchData['expiry_date'],
                    'provider_id' => $batchData['provider_id'],
                    'brand_id' => $batchData['brand_id'],
                ]);
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Lotes actualizados exitosamente']);
        }
        return redirect()->back()->with('success', 'Lotes actualizados exitosamente');
    }
}
