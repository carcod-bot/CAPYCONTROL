<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\CashSession;
use App\Models\CashMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashSessionController extends Controller
{
    /**
     * Show session detail
     */
    public function show(CashSession $cashSession)
    {
        $cashSession->load(['cashRegister', 'user', 'movements.user']);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'session' => $cashSession,
                'withdrawals_total' => $cashSession->totalWithdrawalsAmount(),
                'deposits_total' => $cashSession->totalDepositsAmount(),
            ]);
        }

        return view('pos-control.session-detail', compact('cashSession'));
    }

    /**
     * Open a new session on a cash register
     */
    public function open(Request $request)
    {
        $request->validate([
            'cash_register_id' => 'required|exists:cash_registers,id',
            'user_id' => 'required|exists:users,id',
            'opening_amount' => 'required|numeric|min:0',
        ]);

        $register = CashRegister::findOrFail($request->cash_register_id);

        // Check if register already has an open session
        if ($register->activeSession) {
            return response()->json([
                'success' => false,
                'message' => 'Esta caja ya tiene una sesión abierta. Ciérrela primero.'
            ], 422);
        }

        // Calculate next turn number
        $lastSession = $register->sessions()->latest('opened_at')->first();
        $turnNumber = $lastSession ? $lastSession->turn_number + 1 : 1;

        $session = CashSession::create([
            'cash_register_id' => $request->cash_register_id,
            'user_id' => $request->user_id,
            'status' => 'open',
            'turn_number' => $turnNumber,
            'opening_amount' => $request->opening_amount,
            'expected_amount' => $request->opening_amount,
            'opened_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sesión abierta exitosamente',
            'session' => $session->load(['cashRegister', 'user']),
        ]);
    }

    /**
     * Close a session
     */
    public function close(Request $request, CashSession $cashSession)
    {
        if (!$cashSession->isOpen()) {
            return response()->json([
                'success' => false,
                'message' => 'Esta sesión ya está cerrada.'
            ], 422);
        }

        $request->validate([
            'actual_amount' => 'nullable|numeric|min:0',
            'closing_notes' => 'nullable|string|max:1000',
        ]);

        $cashSession->closeSession(
            $request->actual_amount,
            $request->closing_notes
        );

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente',
            'session' => $cashSession->fresh(['cashRegister', 'user']),
        ]);
    }

    /**
     * Partial withdrawal from a session
     */
    public function withdraw(Request $request, CashSession $cashSession)
    {
        if (!$cashSession->isOpen()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede retirar de una sesión cerrada.'
            ], 422);
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        $movement = CashMovement::create([
            'cash_session_id' => $cashSession->id,
            'user_id' => Auth::id(),
            'type' => 'withdrawal',
            'amount' => $request->amount,
            'reason' => $request->reason ?? 'Retiro parcial',
            'notes' => $request->notes,
        ]);

        // Update session counters
        $cashSession->increment('total_withdrawals');
        $cashSession->decrement('expected_amount', $request->amount);

        return response()->json([
            'success' => true,
            'message' => 'Retiro realizado exitosamente',
            'movement' => $movement,
        ]);
    }

    /**
     * Deposit into a session
     */
    public function deposit(Request $request, CashSession $cashSession)
    {
        if (!$cashSession->isOpen()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede depositar en una sesión cerrada.'
            ], 422);
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        $movement = CashMovement::create([
            'cash_session_id' => $cashSession->id,
            'user_id' => Auth::id(),
            'type' => 'deposit',
            'amount' => $request->amount,
            'reason' => $request->reason ?? 'Depósito',
            'notes' => $request->notes,
        ]);

        // Update expected amount
        $cashSession->increment('expected_amount', $request->amount);

        return response()->json([
            'success' => true,
            'message' => 'Depósito realizado exitosamente',
            'movement' => $movement,
        ]);
    }
}
