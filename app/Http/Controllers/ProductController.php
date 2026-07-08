<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Department;
use App\Models\Category;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['department', 'category', 'brand', 'provider']);

        if ($request->filled('search_code')) {
            $search = $request->search_code;
            $query->where(function($q) use ($search) {
                $q->where('private_code', 'like', "%{$search}%")
                  ->orWhere('ean_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->filled('price_min')) {
            $query->where('price_usd', '>=', $request->price_min);
        }

        if ($request->filled('price_max')) {
            $query->where('price_usd', '<=', $request->price_max);
        }

        $products = $query->orderBy('name')->get();
        
        // Modal variables
        $departments = Department::where('active', true)->orderBy('name')->get();
        $categories = Category::where('active', true)->orderBy('name')->get();
        $brands = \App\Models\Brand::orderBy('name')->get();
        $providers = \App\Models\Provider::orderBy('name')->get();
        $nextCode = Product::generatePrivateCode();
        $codeMode = Setting::get('private_code_mode', 'incremental');

        return view('inventory.products.index', compact('products', 'departments', 'categories', 'brands', 'providers', 'nextCode', 'codeMode'));
    }

    public function create()
    {
        $departments = Department::where('active', true)->orderBy('name')->get();
        $categories = Category::where('active', true)->orderBy('name')->get();
        $nextCode = Product::generatePrivateCode();
        $codeMode = Setting::get('private_code_mode', 'incremental');

        return view('inventory.products.create', compact('departments', 'categories', 'nextCode', 'codeMode'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'private_code' => 'required|string|max:100|unique:products',
            'ean_code' => 'nullable|string|max:100',
            'size_type' => 'nullable|string|max:50',
            'price_usd' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'provider_id' => 'nullable|exists:providers,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $data = $request->except('image');

        if (!empty($data['category_id'])) {
            $category = Category::find($data['category_id']);
            if ($category) {
                $data['department_id'] = $category->department_id;
            }
        }

        // Generic logic
        if (empty($data['brand_id'])) {
            $genericBrand = \App\Models\Brand::where('name', 'Genérico')->first();
            if ($genericBrand) {
                $data['brand_id'] = $genericBrand->id;
            }
        }

        if (empty($data['provider_id'])) {
            $genericProv = \App\Models\Provider::where('name', 'Genérico')->first();
            if ($genericProv) {
                $data['provider_id'] = $genericProv->id;
            }
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        Product::create($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Producto creado exitosamente']);
        }
        return redirect()->route('products.index')->with('success', 'Producto creado exitosamente');
    }

    public function edit(Product $product)
    {
        $departments = Department::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $brands = \App\Models\Brand::orderBy('name')->get();
        $providers = \App\Models\Provider::orderBy('name')->get();
        
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'product' => $product
            ]);
        }
        
        return view('inventory.products.edit', compact('product', 'departments', 'categories', 'brands', 'providers'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'private_code' => 'required|string|max:100|unique:products,private_code,' . $product->id,
            'ean_code' => 'nullable|string|max:100',
            'size_type' => 'nullable|string|max:50',
            'price_usd' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'provider_id' => 'nullable|exists:providers,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $data = $request->except('image');

        if (!empty($data['category_id'])) {
            $category = Category::find($data['category_id']);
            if ($category) {
                $data['department_id'] = $category->department_id;
            }
        }

        // Generic logic
        if (empty($data['brand_id'])) {
            $genericBrand = \App\Models\Brand::where('name', 'Genérico')->first();
            if ($genericBrand) {
                $data['brand_id'] = $genericBrand->id;
            }
        }

        if (empty($data['provider_id'])) {
            $genericProv = \App\Models\Provider::where('name', 'Genérico')->first();
            if ($genericProv) {
                $data['provider_id'] = $genericProv->id;
            }
        }

        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Producto actualizado exitosamente']);
        }
        return redirect()->route('products.index')->with('success', 'Producto actualizado exitosamente');
    }

    public function destroy(Request $request, Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Producto eliminado exitosamente']);
        }
        return redirect()->route('products.index')->with('success', 'Producto eliminado exitosamente');
    }
}
