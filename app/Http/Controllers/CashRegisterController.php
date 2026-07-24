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

    public function align(CashRegister $cashRegister)
    {
        $ip = $cashRegister->ip_address;

        if (!$ip) {
            return response()->json(['error' => 'La caja no tiene una IP configurada.'], 400);
        }

        try {
            // Se usa Http::timeout para no colgar el servidor si la caja está apagada
            $url = "http://{$ip}/capypos/public/api/sync-local";
            $response = \Illuminate\Support\Facades\Http::timeout(5)->get($url);

            if ($response->successful()) {
                return response()->json(['success' => true, 'message' => 'Caja alineada correctamente.']);
            }

            // Fallback for local testing (if IP fails, try localhost if it's the same machine)
            if (request()->ip() === '127.0.0.1' || request()->ip() === '::1') {
                 $url = "http://127.0.0.1/capypos/public/api/sync-local";
                 $response = \Illuminate\Support\Facades\Http::timeout(5)->get($url);
                 if ($response->successful()) {
                     return response()->json(['success' => true, 'message' => 'Caja alineada correctamente usando localhost.']);
                 }
            }

            return response()->json(['error' => 'La caja respondió con error: ' . $response->status()], 500);

        } catch (\Exception $e) {
            // Fallback for local testing
            if (request()->ip() === '127.0.0.1' || request()->ip() === '::1') {
                 try {
                     $url = "http://127.0.0.1/capypos/public/api/sync-local";
                     $response = \Illuminate\Support\Facades\Http::timeout(5)->get($url);
                     if ($response->successful()) {
                         return response()->json(['success' => true, 'message' => 'Caja alineada correctamente usando localhost.']);
                     }
                 } catch (\Exception $e2) {
                     // ignore
                 }
            }
            return response()->json(['error' => 'No se pudo conectar con la caja: ' . $e->getMessage()], 500);
        }
    }

    public function alignAll()
    {
        $registers = CashRegister::whereNotNull('ip_address')->get();

        if ($registers->isEmpty()) {
            return response()->json(['error' => 'No hay cajas con IP configurada.'], 400);
        }

        $responses = [];
        $promises = [];
        
        $pool = \Illuminate\Support\Facades\Http::pool(function (\Illuminate\Http\Client\Pool $pool) use ($registers, &$promises) {
            foreach ($registers as $register) {
                $url = "http://{$register->ip_address}/capypos/public/api/sync-local";
                $promises[$register->id] = $pool->as($register->id)->timeout(20)->get($url);
            }
        });

        $successCount = 0;
        $failCount = 0;

        foreach ($registers as $register) {
            $response = $pool[$register->id] ?? null;
            
            if ($response && $response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
                $successCount++;
            } else {
                // Try localhost fallback if applicable
                if (request()->ip() === '127.0.0.1' || request()->ip() === '::1') {
                    try {
                        $url = "http://127.0.0.1/capypos/public/api/sync-local";
                        $res = \Illuminate\Support\Facades\Http::timeout(20)->get($url);
                        if ($res->successful()) {
                            $successCount++;
                            continue;
                        }
                    } catch (\Exception $e) {
                        // fail
                    }
                }
                $failCount++;
            }
        }

        return response()->json([
            'success' => true, 
            'message' => "Proceso completado. $successCount cajas alineadas, $failCount fallaron."
        ]);
    }
}
