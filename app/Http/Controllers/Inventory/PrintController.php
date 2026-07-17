<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class PrintController extends Controller
{
    /**
     * Display the print queue interface.
     */
    public function index()
    {
        return view('inventory.prints.index');
    }

    /**
     * Search products via AJAX for the print queue.
     */
    public function search(Request $request)
    {
        $query = $request->input('q');

        if (!$query) {
            return response()->json([]);
        }

        $products = Product::where('active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('ean_code', 'like', "%{$query}%")
                  ->orWhere('private_code', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'price_usd', 'ean_code', 'private_code']);

        return response()->json($products);
    }

    /**
     * Generate the printable view.
     */
    public function generate(Request $request)
    {
        $type = $request->input('type'); // 'labels' or 'talkers'
        $codeType = $request->input('code_type', 'ean'); // 'ean' or 'private'
        $columns = (int) $request->input('columns', 2);
        $size = $request->input('size', 'small');
        $items = json_decode($request->input('items', '[]'), true);

        if (empty($items)) {
            return back()->with('error', 'No hay productos para imprimir.');
        }

        // Fetch product details
        $productIds = array_column($items, 'id');
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $printData = [];
        foreach ($items as $item) {
            $product = $products->get($item['id']);
            if ($product) {
                $qty = (int) $item['qty'];
                for ($i = 0; $i < $qty; $i++) {
                    $printData[] = [
                        'name' => $product->name,
                        'price' => number_format($product->price_usd, 2, ',', '.'),
                        'barcode' => $codeType === 'ean' && $product->ean_code ? $product->ean_code : $product->private_code,
                    ];
                }
            }
        }

        if ($type === 'labels') {
            return view('inventory.prints.labels', compact('printData', 'columns'));
        }

        return view('inventory.prints.talkers', compact('printData', 'size'));
    }
}
