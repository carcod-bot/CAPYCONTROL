<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($b) use ($q) {
                $b->where('name', 'LIKE', "%{$q}%")
                  ->orWhere('document_id', 'LIKE', "%{$q}%")
                  ->orWhere('phone', 'LIKE', "%{$q}%")
                  ->orWhere('email', 'LIKE', "%{$q}%");
            });
        }

        $customers = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'document_id' => 'required|string|max:255|unique:customers,document_id',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_status' => 'required|in:active,suspended'
        ]);

        $data['credit_limit'] = $data['credit_limit'] ?? 0;

        Customer::create($data);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Cliente creado exitosamente.']);
        }
        return back()->with('success', 'Cliente creado exitosamente.');
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'document_id' => 'required|string|max:255|unique:customers,document_id,' . $customer->id,
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_status' => 'required|in:active,suspended'
        ]);

        $data['credit_limit'] = $data['credit_limit'] ?? 0;

        $customer->update($data);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Cliente actualizado exitosamente.']);
        }
        return back()->with('success', 'Cliente actualizado exitosamente.');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->current_balance > 0) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No se puede eliminar un cliente que tiene deudas pendientes.'], 403);
            }
            return back()->with('error', 'No se puede eliminar un cliente que tiene deudas pendientes.');
        }

        $customer->delete();
        
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Cliente eliminado.']);
        }
        return back()->with('success', 'Cliente eliminado.');
    }
}
