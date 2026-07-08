<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'currency_id' => 'required|exists:currencies,id',
            'code' => 'required|string',
            'description' => 'required|string',
            'value' => 'nullable|numeric',
            'max_change_amount' => 'nullable|numeric',
            'min_purchase_amount' => 'nullable|numeric',
            'is_real_denomination' => 'boolean',
            'allows_change' => 'boolean',
            'used_in_pos' => 'boolean',
            'electronic_verification' => 'boolean',
            'cash_advance' => 'boolean',
            'admin_serial' => 'boolean',
            'auto_declare' => 'boolean',
            'auto_deposit' => 'boolean',
            'used_in_admin_billing' => 'boolean',
        ]);

        $pm = PaymentMethod::create($data);
        return response()->json(['success' => true, 'payment_method' => $pm]);
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $data = $request->validate([
            'currency_id' => 'required|exists:currencies,id',
            'code' => 'required|string',
            'description' => 'required|string',
            'value' => 'nullable|numeric',
            'max_change_amount' => 'nullable|numeric',
            'min_purchase_amount' => 'nullable|numeric',
            'is_real_denomination' => 'boolean',
            'allows_change' => 'boolean',
            'used_in_pos' => 'boolean',
            'electronic_verification' => 'boolean',
            'cash_advance' => 'boolean',
            'admin_serial' => 'boolean',
            'auto_declare' => 'boolean',
            'auto_deposit' => 'boolean',
            'used_in_admin_billing' => 'boolean',
        ]);

        $paymentMethod->update($data);
        return response()->json(['success' => true, 'payment_method' => $paymentMethod]);
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        $paymentMethod->delete();
        return response()->json(['success' => true]);
    }
}
