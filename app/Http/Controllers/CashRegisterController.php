<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\CashSession;
use App\Models\CashMovement;
use Illuminate\Http\Request;

class CashRegisterController extends Controller
{
    /**
     * Display the POS monitoring dashboard — only shows registers with an OPEN session
     */
    public function index()
    {
        // Only registers that currently have an open session (active monitoring)
        $openSessions = CashSession::where('status', 'open')
            ->with(['cashRegister', 'user', 'movements'])
            ->orderBy('opened_at', 'desc')
            ->get();

        $openRegisterIds = $openSessions->pluck('cash_register_id');

        // Stats: based on all active registers
        $allRegisters = CashRegister::where('active', true)->get();
        $totalRegisters = $allRegisters->count();
        $openRegisters = $openRegisterIds->count();
        $closedRegisters = $totalRegisters - $openRegisters;

        $totalSalesToday = CashSession::whereDate('opened_at', today())->sum('total_sales');
        $totalWithdrawalsToday = CashSession::whereDate('opened_at', today())->sum('total_withdrawals');

        return view('pos-control.index', compact(
            'openSessions', 'totalRegisters', 'openRegisters', 'closedRegisters',
            'totalSalesToday', 'totalWithdrawalsToday'
        ));
    }

    /**
     * Display the Registers Management page — shows ALL registers (for CRUD)
     */
    public function registers()
    {
        $registers = CashRegister::with(['activeSession.user'])
            ->orderBy('number')
            ->get();

        return view('pos-control.registers', compact('registers'));
    }

    /**
     * Store a new cash register
     */
    public function store(Request $request)
    {
        $request->validate([
            'number'     => 'required|string|max:10|unique:cash_registers',
            'name'       => 'nullable|string|max:255',
            'location'   => 'nullable|string|max:255',
            'hostname'   => 'nullable|string|max:255',
            'ip_address' => 'nullable|ip',
        ]);

        $register = CashRegister::create($request->only('number', 'name', 'location', 'hostname', 'ip_address'));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Caja registrada exitosamente', 'data' => $register]);
        }
        return redirect()->route('pos-control.registers')->with('success', 'Caja registrada exitosamente');
    }

    /**
     * Update a cash register
     */
    public function update(Request $request, CashRegister $cashRegister)
    {
        $request->validate([
            'number'     => 'required|string|max:10|unique:cash_registers,number,' . $cashRegister->id,
            'name'       => 'nullable|string|max:255',
            'location'   => 'nullable|string|max:255',
            'hostname'   => 'nullable|string|max:255',
            'ip_address' => 'nullable|ip',
        ]);

        $cashRegister->update($request->only('number', 'name', 'location', 'hostname', 'ip_address'));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Caja actualizada exitosamente', 'data' => $cashRegister]);
        }
        return redirect()->route('pos-control.registers')->with('success', 'Caja actualizada exitosamente');
    }

    /**
     * Delete a cash register
     */
    public function destroy(Request $request, CashRegister $cashRegister)
    {
        $cashRegister->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Caja eliminada exitosamente']);
        }
        return redirect()->route('pos-control.registers')->with('success', 'Caja eliminada exitosamente');
    }

    /**
     * Get all sessions for a specific register (AJAX)
     */
    public function sessions(CashRegister $cashRegister)
    {
        $sessions = $cashRegister->sessions()
            ->with('user')
            ->orderBy('opened_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json($sessions);
    }
}
