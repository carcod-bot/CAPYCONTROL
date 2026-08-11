<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\ReturnedProduct;
use App\Models\InventoryAdjustment;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReturnedProductController extends Controller
{
    public function index(Request $request)
    {
        $query = ReturnedProduct::with(['product', 'sale'])->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('private_code', 'like', "%{$search}%")
                  ->orWhere('ean_code', 'like', "%{$search}%");
            })->orWhereHas('sale', function($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%");
            });
        }

        $returnedProducts = $query->paginate(20)->withQueryString();

        return view('inventory.returned-products.index', compact('returnedProducts'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:restocked,discarded',
            'notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $returnedProduct = ReturnedProduct::lockForUpdate()->findOrFail($id);

            if ($returnedProduct->status !== 'pending_review') {
                throw new \Exception('Este producto devuelto ya ha sido procesado.');
            }

            if ($request->status === 'restocked') {
                $product = $returnedProduct->product;
                
                // Bloqueamos el producto para actualizar su stock
                $product = \App\Models\Product::lockForUpdate()->findOrFail($product->id);
                $previousStock = $product->stock;
                $qty = $returnedProduct->quantity_returned;
                $newStock = $previousStock + $qty;

                // Generamos un lote para el reingreso
                $batchPrefix = Setting::get('default_batch_prefix', 'LOTE-');
                $batchNumber = $batchPrefix . 'DEV-' . date('Ymd-His') . '-' . strtoupper(substr(uniqid(), -4));

                $batch = $product->batches()->create([
                    'batch_number' => $batchNumber,
                    'provider_id' => $product->provider_id,
                    'brand_id' => $product->brand_id,
                    'initial_quantity' => $qty,
                    'current_quantity' => $qty,
                ]);

                // Registramos el ajuste de inventario
                $reason = 'Reingreso a Stock por Devolución (Ticket: ' . ($returnedProduct->sale->ticket_number ?? 'N/A') . ')';
                if ($request->filled('notes')) {
                    $reason .= ' - Nota: ' . $request->notes;
                }

                $adjustment = InventoryAdjustment::create([
                    'product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'type' => 'in',
                    'quantity' => $qty,
                    'previous_stock' => $previousStock,
                    'new_stock' => $newStock,
                    'reason' => $reason,
                    'notes' => 'Generado automáticamente desde gestión de devoluciones.',
                ]);

                $adjustment->batches()->sync([$batch->id => ['quantity' => $qty]]);

                $product->stock = $newStock;
                $product->save();

                $returnedProduct->status = 'restocked';
            } else {
                $returnedProduct->status = 'discarded';
            }

            if ($request->filled('notes')) {
                $returnedProduct->reason = $returnedProduct->reason . "\n\nResolución: " . $request->notes;
            }

            $returnedProduct->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'El estado de la devolución se actualizó correctamente.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al procesar la devolución: ' . $e->getMessage()
            ], 500);
        }
    }
}
