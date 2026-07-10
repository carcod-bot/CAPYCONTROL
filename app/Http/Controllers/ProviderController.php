<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    public function index()
    {
        $providers = Provider::orderBy('name')->paginate(20)->withQueryString();
        return view('inventory.providers.index', compact('providers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:providers',
            'description' => 'nullable|string'
        ]);

        $provider = Provider::create($request->all());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Proveedor creado exitosamente.', 'data' => $provider]);
        }
        return redirect()->route('providers.index')->with('success', 'Proveedor creado exitosamente.');
    }

    public function update(Request $request, Provider $provider)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:providers,name,' . $provider->id,
            'description' => 'nullable|string'
        ]);

        $provider->update($request->all());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Proveedor actualizado exitosamente.', 'data' => $provider]);
        }
        return redirect()->route('providers.index')->with('success', 'Proveedor actualizado exitosamente.');
    }

    public function destroy(Request $request, Provider $provider)
    {
        if ($provider->name === 'Genérico') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No se puede eliminar el proveedor Genérico.'], 403);
            }
            return redirect()->route('providers.index')->with('error', 'No se puede eliminar el proveedor Genérico.');
        }

        $provider->delete();
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Proveedor eliminado.']);
        }
        return redirect()->route('providers.index')->with('success', 'Proveedor eliminado.');
    }
}
