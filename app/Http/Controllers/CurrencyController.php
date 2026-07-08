<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function index()
    {
        return view('finances.currencies.index');
    }

    public function fetchAll()
    {
        $currencies = Currency::with('paymentMethods')->orderBy('code')->get();
        return response()->json($currencies);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|unique:currencies,code',
            'description' => 'required|string',
            'symbol' => 'nullable|string',
            'max_decimals' => 'required|integer',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'exchange_rate' => 'required|numeric',
            'iso_code' => 'nullable|string',
            'observation' => 'nullable|string',
            'used_in_pos' => 'boolean',
        ]);

        if (isset($data['is_default']) && $data['is_default']) {
            Currency::query()->update(['is_default' => false]);
        }

        $currency = Currency::create($data);
        return response()->json(['success' => true, 'currency' => $currency]);
    }

    public function update(Request $request, Currency $currency)
    {
        $data = $request->validate([
            'code' => 'required|string|unique:currencies,code,' . $currency->id,
            'description' => 'required|string',
            'symbol' => 'nullable|string',
            'max_decimals' => 'required|integer',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'exchange_rate' => 'required|numeric',
            'iso_code' => 'nullable|string',
            'observation' => 'nullable|string',
            'used_in_pos' => 'boolean',
        ]);

        if (isset($data['is_default']) && $data['is_default']) {
            Currency::query()->update(['is_default' => false]);
        }

        $currency->update($data);
        return response()->json(['success' => true, 'currency' => $currency]);
    }

    public function destroy(Currency $currency)
    {
        $currency->delete();
        return response()->json(['success' => true]);
    }
}
