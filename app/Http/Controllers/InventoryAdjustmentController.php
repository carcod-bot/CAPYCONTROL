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
        $query = InventoryAdjustment::with(['product', 'user', 'batches'])->orderBy('created_at', 'desc');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
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

        $brands = \App\Models\Brand::where('active', true)->orderBy('name')->get();
        $providers = \App\Models\Provider::where('active', true)->orderBy('name')->get();

        return view('inventory.adjustments.index', compact('adjustments', 'brands', 'providers'));
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
                    
                    $batchNumber = !empty($prodInput['batch_number']) ? $prodInput['batch_number'] : 'LOTE-' . date('Ymd-His') . '-' . strtoupper(substr(uniqid(), -4));
                    
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
        
        $lifecycle = [];

        foreach ($adjustment->batches as $batch) {
            // Get all adjustments that have affected this batch
            $allAdjustments = $batch->inventoryAdjustments()->get();

            $sold = 0;
            $damaged = 0;
            $recounted = false;

            foreach ($allAdjustments as $adj) {
                if ($adj->type === 'out') {
                    // Check if the reason contains 'Venta' (case insensitive)
                    if (stripos($adj->reason, 'Venta') !== false) {
                        $sold += $adj->pivot->quantity;
                    } else {
                        $damaged += $adj->pivot->quantity;
                    }
                } elseif ($adj->type === 'set') {
                    $recounted = true;
                }
            }

            $lifecycle[] = [
                'batch_number' => $batch->batch_number,
                'expiry_date' => $batch->expiry_date ? \Carbon\Carbon::parse($batch->expiry_date)->format('d/m/Y') : null,
                'initial' => floatval($batch->initial_quantity),
                'current' => floatval($batch->current_quantity),
                'sold' => floatval($sold),
                'damaged' => floatval($damaged),
                'recounted' => $recounted,
            ];
        }

        return response()->json($lifecycle);
    }
}
