<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\CashSession;
use App\Models\CashMovement;
use Illuminate\Http\Request;

class CashRegisterController extends Controller
{
    /**
     * Display the POS monitoring dashboard
     */
    public function index()
    {
        $registers = CashRegister::where('active', true)
            ->with(['activeSession.user', 'activeSession.movements'])
            ->orderBy('number')
            ->get();

        // Stats
        $totalRegisters = $registers->count();
        $openRegisters = $registers->filter(fn($r) => $r->activeSession)->count();
        $closedRegisters = $totalRegisters - $openRegisters;

        $activeSessions = CashSession::where('status', 'open')
            ->with(['cashRegister', 'user'])
            ->orderBy('opened_at', 'desc')
            ->get();

        $totalSalesToday = CashSession::whereDate('opened_at', today())->sum('total_sales');
        $totalWithdrawalsToday = CashSession::whereDate('opened_at', today())->sum('total_withdrawals');

        return view('pos-control.index', compact(
            'registers', 'totalRegisters', 'openRegisters', 'closedRegisters',
            'activeSessions', 'totalSalesToday', 'totalWithdrawalsToday'
        ));
    }

    /**
     * Store a new cash register
     */
    public function store(Request $request)
    {
        $request->validate([
            'number' => 'required|string|max:10|unique:cash_registers',
            'name' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        $register = CashRegister::create($request->only('number', 'name', 'location'));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Caja registrada exitosamente', 'data' => $register]);
        }
        return redirect()->route('pos-control.index')->with('success', 'Caja registrada exitosamente');
    }

    /**
     * Update a cash register
     */
    public function update(Request $request, CashRegister $cashRegister)
    {
        $request->validate([
            'number' => 'required|string|max:10|unique:cash_registers,number,' . $cashRegister->id,
            'name' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        $cashRegister->update($request->only('number', 'name', 'location'));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Caja actualizada exitosamente', 'data' => $cashRegister]);
        }
        return redirect()->route('pos-control.index')->with('success', 'Caja actualizada exitosamente');
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
        return redirect()->route('pos-control.index')->with('success', 'Caja eliminada exitosamente');
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
