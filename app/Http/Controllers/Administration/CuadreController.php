<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CashSession;
use App\Models\CashRegister;
use App\Models\PaymentMethod;
use App\Models\SalePayment;
use App\Models\CashMovement;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CuadreController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));
        
        // Find sessions active on that date
        $sessions = CashSession::with(['cashRegister', 'user'])
            ->whereDate('opened_at', '<=', $date)
            ->where(function($q) use ($date) {
                $q->whereNull('closed_at')->orWhereDate('closed_at', '>=', $date);
            })
            ->orderBy('opened_at', 'desc')
            ->get();

        $paymentMethods = PaymentMethod::where('used_in_pos', true)->get();
            
        return view('administration.cuadre.index', compact('sessions', 'date', 'paymentMethods'));
    }

    public function declarationFields(CashSession $session)
    {
        $hasSalesOrMovements = $session->sales()->where('status', '!=', 'voided')->exists() 
            || $session->movements()->exists();

        if (!$hasSalesOrMovements) {
            return '<div class="alert alert-info" style="margin-bottom: 0;">No hubo ventas ni movimientos en esta sesión. No requiere declaración, la caja cuadrará automáticamente con el monto de apertura.</div>
            <input type="hidden" name="no_declaration_needed" value="1">';
        }

        $usedMethodIds = SalePayment::whereHas('sale', function($q) use ($session) {
                $q->where('cash_session_id', $session->id)->where('status', '!=', 'voided');
            })->pluck('payment_method_id')->toArray();
            
        $usedMovementIds = CashMovement::where('cash_session_id', $session->id)
            ->pluck('payment_method_id')->toArray();
            
        $usedMethodIds = array_unique(array_merge($usedMethodIds, $usedMovementIds));

        $paymentMethods = PaymentMethod::where('used_in_pos', true)->get();

        $html = '';
        foreach ($paymentMethods as $pm) {
            // Include if used, OR if it's the base method (if they opened with money)
            if (in_array($pm->id, $usedMethodIds) || ($pm->is_base && $session->opening_amount > 0)) {
                $html .= '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px dashed var(--border);">';
                $html .= '<div><strong>' . htmlspecialchars($pm->description) . '</strong></div>';
                
                if ($pm->auto_declare) {
                    $html .= '<div><span class="badge" style="background: var(--primary-light); color: var(--primary);">AUTO-CALCULADO</span></div>';
                } else {
                    $html .= '<div style="width: 150px;">
                        <input type="number" name="declarations[' . $pm->id . ']" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                    </div>';
                }
                $html .= '</div>';
            }
        }

        if (empty($html)) {
             return '<div class="alert alert-info" style="margin-bottom: 0;">No hay métodos de pago aplicables para declarar.</div>
            <input type="hidden" name="no_declaration_needed" value="1">';
        }

        return $html;
    }

    public function forceClose(Request $request, CashSession $session)
    {
        if ($session->status !== 'open') {
            return redirect()->back()->with('error', 'La sesión ya está cerrada.');
        }

        if (!$request->has('no_declaration_needed')) {
            $request->validate([
                'declarations' => 'required|array',
                'declarations.*' => 'numeric|min:0'
            ]);
        }

        try {
            DB::beginTransaction();

            $declarations = $request->input('declarations', []);
            $declarationsData = [];
            $totalActualAmount = 0;
            
            // Base expected amount is already tracked by the session
            $expectedBase = $session->expected_amount;

            if ($request->has('no_declaration_needed')) {
                // If no declaration needed, we assume actual == expected perfectly
                $totalActualAmount = $expectedBase;
            } else {
                // Recalculate per method difference only for what was passed
                $paymentMethods = PaymentMethod::where('used_in_pos', true)->get();
                foreach ($paymentMethods as $pm) {
                    $declared = isset($declarations[$pm->id]) ? (float)$declarations[$pm->id] : 0;
                    
                    if ($pm->auto_declare) {
                        // Auto-declare takes expected as actual
                        $salesAmount = SalePayment::whereHas('sale', function($q) use ($session) {
                                $q->where('cash_session_id', $session->id)->where('status', '!=', 'voided');
                            })
                            ->where('payment_method_id', $pm->id)
                            ->sum('amount');
                            
                        $withdrawals = CashMovement::where('cash_session_id', $session->id)
                            ->where('type', 'withdrawal')
                            ->where('payment_method_id', $pm->id)
                            ->sum('amount');
                            
                        $expected = $salesAmount - $withdrawals;
                        $declared = $expected; // Auto-declare matches perfectly
                    }
                    
                    if ($pm->is_base) {
                        // Only add to total if it's base currency (we assume conversions are handled elsewhere or not used)
                        $totalActualAmount += $declared;
                    }

                    if (isset($declarations[$pm->id]) || $pm->auto_declare) {
                        $declarationsData[$pm->id] = $declared;
                    }
                }
            }

            $difference = $totalActualAmount - $expectedBase;

            $session->update([
                'status' => 'closed',
                'closed_at' => now(),
                'actual_amount' => $totalActualAmount,
                'difference' => $difference,
                'declarations_data' => $declarationsData,
                'closing_notes' => 'Cierre forzado desde Administración. ' . $request->input('notes')
            ]);

            DB::commit();
            return redirect()->route('admin.cuadre.index')->with('success', 'La caja fue cuadrada y cerrada forzosamente con éxito.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al forzar cierre: ' . $e->getMessage());
        }
    }
}
