<?php

namespace App\Http\Controllers\Finances;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CreditAccount;
use Illuminate\Http\Request;

class CreditController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::where('current_balance', '>', 0);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($b) use ($q) {
                $b->where('name', 'LIKE', "%{$q}%")
                  ->orWhere('document_id', 'LIKE', "%{$q}%");
            });
        }

        $debtors = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('finances.credits.index', compact('debtors'));
    }

    public function show(Customer $customer)
    {
        $customer->load(['creditAccounts' => function($q) {
            $q->orderBy('created_at', 'desc');
        }, 'creditAccounts.sale']);

        return view('finances.credits.show', compact('customer'));
    }
}
