<?php

namespace App\Http\Controllers;

use App\Models\CreditLevel;
use Illuminate\Http\Request;

class CreditLevelController extends Controller
{
    public function index()
    {
        $levels = CreditLevel::orderBy('required_purchases', 'asc')->get();
        return view('finances.credits.levels.index', compact('levels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'required_purchases' => 'required|integer|min:0',
            'down_payment_type' => 'required|in:percentage,fixed',
            'down_payment_value' => 'required|numeric|min:0',
            'installments_count' => 'required|integer|min:1',
            'payment_frequency' => 'required|in:weekly,biweekly,monthly',
            'limit_increase_percentage' => 'nullable|numeric|min:0',
        ]);

        CreditLevel::create($request->all());

        return response()->json(['success' => true, 'message' => 'Nivel de crédito creado exitosamente.']);
    }

    public function update(Request $request, CreditLevel $creditLevel)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'required_purchases' => 'required|integer|min:0',
            'down_payment_type' => 'required|in:percentage,fixed',
            'down_payment_value' => 'required|numeric|min:0',
            'installments_count' => 'required|integer|min:1',
            'payment_frequency' => 'required|in:weekly,biweekly,monthly',
            'limit_increase_percentage' => 'nullable|numeric|min:0',
        ]);

        $creditLevel->update($request->all());

        return response()->json(['success' => true, 'message' => 'Nivel de crédito actualizado exitosamente.']);
    }

    public function destroy(CreditLevel $creditLevel)
    {
        if ($creditLevel->customers()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'No se puede eliminar el nivel porque hay clientes asignados a él.'], 400);
        }

        $creditLevel->delete();

        return response()->json(['success' => true, 'message' => 'Nivel de crédito eliminado exitosamente.']);
    }
}
