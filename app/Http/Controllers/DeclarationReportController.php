<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CashSession;
use App\Models\CashRegister;
use App\Models\User;
use App\Models\PaymentMethod;
use App\Models\SalePayment;
use App\Models\CashMovement;
use Carbon\Carbon;

class DeclarationReportController extends Controller
{
    public function index(Request $request)
    {
        $query = CashSession::with(['user', 'cashRegister'])->orderBy('id', 'desc');

        if ($request->filled('cash_register_id')) {
            $query->where('cash_register_id', $request->cash_register_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('opened_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('opened_at', '<=', $request->date_to);
        }

        $sessions = $query->paginate(20);

        $registers = CashRegister::all();
        $users = User::all();

        return view('finances.declarations.index', compact('sessions', 'registers', 'users'));
    }

    public function show($id)
    {
        $session = CashSession::with(['user', 'cashRegister', 'movements.paymentMethod'])->findOrFail($id);
        
        $paymentMethods = PaymentMethod::with('currency')->get();
        $declarationsData = json_decode($session->declarations_data, true) ?: [];
        
        // Map declarations by payment method ID for easy access
        $declaredMap = [];
        foreach ($declarationsData as $dec) {
            $declaredMap[$dec['payment_method_id']] = $dec;
        }

        $methodsSummary = [];
        $openingAndWithdrawalsApplied = false;

        foreach ($paymentMethods as $method) {
            // 1. Sales
            $salesLocal = SalePayment::whereHas('sale', function ($query) use ($session) {
                $query->where('cash_session_id', $session->id);
            })->where('payment_method_id', $method->id)->sum('amount_local');

            // 2. Withdrawals
            $withdrawalsLocal = CashMovement::where('cash_session_id', $session->id)
                ->where('type', 'withdrawal')
                ->where('payment_method_id', $method->id)
                ->sum('amount');

            // 3. Deposits
            $depositsLocal = CashMovement::where('cash_session_id', $session->id)
                ->where('type', 'deposit')
                ->where('payment_method_id', $method->id)
                ->sum('amount');

            $expectedLocal = $salesLocal + $depositsLocal - $withdrawalsLocal;

            // 4. Apply Opening Amount to the primary Cash method (Efectivo VES)
            $isCash = (str_contains(strtolower($method->description), 'efectivo') || str_contains(strtolower($method->description), 'cash'));
            
            if (!$openingAndWithdrawalsApplied && $isCash) {
                $expectedLocal += $session->opening_amount;

                // Legacy withdrawals without method
                $legacyWithdrawals = CashMovement::where('cash_session_id', $session->id)
                    ->where('type', 'withdrawal')
                    ->whereNull('payment_method_id')
                    ->sum('amount');
                $expectedLocal -= $legacyWithdrawals;
                $openingAndWithdrawalsApplied = true;
            }

            $declaredAmount = isset($declaredMap[$method->id]) ? $declaredMap[$method->id]['declared_amount_local'] : 0;
            $differenceLocal = $declaredAmount - $expectedLocal;

            // Only add to summary if there is some movement or it was declared
            if ($salesLocal > 0 || $withdrawalsLocal > 0 || $depositsLocal > 0 || isset($declaredMap[$method->id]) || ($isCash && $session->opening_amount > 0)) {
                $methodsSummary[] = [
                    'method' => $method,
                    'is_auto_declare' => $method->auto_declare,
                    'sales' => $salesLocal,
                    'deposits' => $depositsLocal,
                    'withdrawals' => $withdrawalsLocal,
                    'expected' => $expectedLocal,
                    'declared' => $declaredAmount,
                    'difference' => $differenceLocal
                ];
            }
        }

        return view('finances.declarations.show', compact('session', 'methodsSummary'));
    }
}
