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
        $query = InventoryAdjustment::with(['product', 'user'])->orderBy('created_at', 'desc');

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

        $adjustments = $query->paginate(20)->withQueryString();

        return view('inventory.adjustments.index', compact('adjustments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:in,out,set',
            'quantity' => 'required|numeric|min:0.001',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $product = Product::lockForUpdate()->findOrFail($request->product_id);
            $previousStock = $product->stock;
            $newStock = $previousStock;
            $qty = $request->quantity;

            if ($request->type === 'in') {
                $newStock = $previousStock + $qty;
            } elseif ($request->type === 'out') {
                $newStock = $previousStock - $qty;
            } elseif ($request->type === 'set') {
                $newStock = $qty; // Physical count sets the absolute value
                $qty = abs($newStock - $previousStock); // Store the difference for the record if needed
            }

            // Create adjustment record
            $adjustment = InventoryAdjustment::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'type' => $request->type,
                'quantity' => $request->type === 'set' ? $qty : $request->quantity,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'reason' => $request->reason,
                'notes' => $request->notes,
            ]);

            // Update product stock
            $product->stock = $newStock;
            $product->save();

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ajuste registrado exitosamente.',
                    'data' => $adjustment->load(['product', 'user'])
                ]);
            }

            return redirect()->back()->with('success', 'Ajuste de inventario registrado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al procesar el ajuste: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Error al registrar el ajuste.');
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
}
