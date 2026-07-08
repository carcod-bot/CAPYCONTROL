<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::orderBy('name')->get();
        return view('inventory.brands.index', compact('brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:brands',
            'description' => 'nullable|string'
        ]);

        $brand = Brand::create($request->all());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Marca creada exitosamente.', 'data' => $brand]);
        }
        return redirect()->route('brands.index')->with('success', 'Marca creada exitosamente.');
    }

    public function update(Request $request, Brand $brand)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,' . $brand->id,
            'description' => 'nullable|string'
        ]);

        $brand->update($request->all());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Marca actualizada exitosamente.', 'data' => $brand]);
        }
        return redirect()->route('brands.index')->with('success', 'Marca actualizada exitosamente.');
    }

    public function destroy(Request $request, Brand $brand)
    {
        if ($brand->name === 'Genérico') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No se puede eliminar la marca Genérica.'], 403);
            }
            return redirect()->route('brands.index')->with('error', 'No se puede eliminar la marca Genérica.');
        }

        $brand->delete();
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Marca eliminada.']);
        }
        return redirect()->route('brands.index')->with('success', 'Marca eliminada.');
    }
}
