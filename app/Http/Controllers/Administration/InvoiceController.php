<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\CashRegister;
use App\Models\User;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['cashSession.cashRegister', 'cashSession.user', 'customer']);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('cash_register_id')) {
            $query->whereHas('cashSession', function($q) use ($request) {
                $q->where('cash_register_id', $request->cash_register_id);
            });
        }

        if ($request->filled('user_id')) {
            $query->whereHas('cashSession', function($q) use ($request) {
                $q->where('user_id', $request->user_id);
            });
        }

        if ($request->filled('ticket_number')) {
            $query->where('ticket_number', 'like', '%' . $request->ticket_number . '%');
        }

        if ($request->filled('customer_document')) {
            $query->whereHas('customer', function($q) use ($request) {
                $q->where('document_id', 'like', '%' . $request->customer_document . '%');
            });
        }

        if ($request->filled('customer_name')) {
            $query->whereHas('customer', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->customer_name . '%');
            });
        }

        if ($request->filled('product_name')) {
            $term = $request->product_name;
            $query->whereHas('items', function($q) use ($term) {
                $q->where('product_name', 'like', '%' . $term . '%')
                  ->orWhere('product_code', 'like', '%' . $term . '%')
                  ->orWhereHas('product', function($pq) use ($term) {
                      $pq->where('ean_code', 'like', '%' . $term . '%')
                         ->orWhere('private_code', 'like', '%' . $term . '%');
                  });
            });
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(20);
        $registers = CashRegister::all();
        $users = User::all();

        return view('administration.invoices.index', compact('invoices', 'registers', 'users'));
    }

    public function show(Sale $invoice)
    {
        $invoice->load(['customer', 'cashSession.cashRegister', 'cashSession.user', 'items', 'paymentMethod']);
        return view('administration.invoices.show', compact('invoice'));
    }
}
