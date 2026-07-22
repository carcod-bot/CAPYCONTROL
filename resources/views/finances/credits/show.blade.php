@extends('layouts.app')

@section('title', 'Estado de Cuenta: ' . $customer->name)

@push('styles')
<style>
    .credit-layout {
        display: flex;
        gap: 2rem;
        align-items: flex-start;
    }
    
    .customer-summary {
        width: 350px;
        background: var(--surface);
        border-radius: 16px;
        box-shadow: var(--shadow-md);
        padding: 1.5rem;
        flex-shrink: 0;
    }
    
    .summary-title {
        font-weight: 800;
        color: var(--primary);
        font-size: 1.1rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .summary-stat {
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid var(--border);
    }
    .summary-stat:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    
    .stat-label {
        color: var(--text-muted);
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
    }
    .stat-value {
        font-size: 1.8rem;
        font-weight: 800;
        color: var(--text-main);
    }
    .stat-value.danger { color: var(--danger); }
    .stat-value.success { color: #10b981; }
    
    .content-area {
        flex: 1;
        background: var(--surface);
        border-radius: 16px;
        box-shadow: var(--shadow-md);
        overflow: hidden;
    }
    
    .content-header {
        padding: 1.5rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--background);
    }
    .content-header h3 {
        margin: 0;
        font-weight: 700;
        font-size: 1.1rem;
    }
    
    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }
    
    .table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .table th, .table td {
        padding: 1.25rem 1rem;
        text-align: left;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }
    
    .table th:first-child, .table td:first-child { padding-left: 1.5rem; }
    .table th:last-child, .table td:last-child { padding-right: 1.5rem; }
    
    .table th {
        background: var(--surface);
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }
    
    .text-end { text-align: right !important; }
    .text-center { text-align: center !important; }
    
    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    .status-pending { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
    .status-partial { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
    .status-paid { background: rgba(16, 185, 129, 0.15); color: #10b981; }
    
</style>
@endpush

@section('content')
<div style="margin-bottom: 1.5rem;">
    <a href="{{ route('credits.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Volver al listado
    </a>
</div>

<div class="credit-layout">
    <!-- Customer Summary -->
    <div class="customer-summary">
        <div class="summary-title">
            <i class="fa-solid fa-user"></i> Datos del Cliente
        </div>
        
        <div class="mb-4">
            <div style="font-weight: 700; font-size: 1.1rem;">{{ $customer->name }}</div>
            <div style="color: var(--text-muted); font-size: 0.9rem;">CI/RIF: {{ $customer->document_id }}</div>
            <div style="color: var(--text-muted); font-size: 0.9rem;">Tel: {{ $customer->phone ?: 'N/A' }}</div>
        </div>
        
        <div class="summary-stat">
            <div class="stat-label">Límite de Crédito</div>
            <div class="stat-value">${{ number_format($customer->credit_limit, 2) }}</div>
        </div>
        
        <div class="summary-stat">
            <div class="stat-label">Deuda Actual Pendiente</div>
            <div class="stat-value danger">${{ number_format($customer->current_balance, 2) }}</div>
        </div>
        
        <div class="summary-stat">
            <div class="stat-label">Crédito Disponible</div>
            <div class="stat-value success">${{ number_format(max(0, $customer->credit_limit - $customer->current_balance), 2) }}</div>
        </div>
    </div>
    
    <!-- Credits Table -->
    <div class="content-area">
        <div class="content-header">
            <h3><i class="fa-solid fa-list"></i> Historial de Cargos</h3>
        </div>
        
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Factura Origen</th>
                        <th class="text-end">Monto Total</th>
                        <th class="text-end">Monto Pagado</th>
                        <th class="text-end">Restante</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customer->creditAccounts as $account)
                    <tr>
                        <td style="white-space: nowrap;">
                            @if($account->installments->count() > 0)
                            <button class="btn btn-sm btn-secondary me-2" onclick="toggleInstallments({{ $account->id }})" title="Ver Cuotas">
                                <i class="fa-solid fa-chevron-down" id="icon-inst-{{ $account->id }}"></i>
                            </button>
                            @endif
                            {{ $account->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td>
                            @if($account->sale)
                                <span style="background: rgba(255,255,255,0.05); padding: 0.25rem 0.5rem; border-radius: 4px; font-family: monospace;" title="Venta #{{ $account->sale->id }}">{{ $account->sale->ticket_number }}</span>
                            @else
                                <span style="color: var(--text-muted);">N/A</span>
                            @endif
                        </td>
                        <td class="text-end" style="font-weight: 600;">${{ number_format($account->amount, 2) }}</td>
                        <td class="text-end" style="color: #10b981; font-weight: 500;">${{ number_format($account->paid_amount, 2) }}</td>
                        <td class="text-end" style="color: var(--danger); font-weight: 700;">
                            ${{ number_format($account->amount - $account->paid_amount, 2) }}
                        </td>
                        <td class="text-center">
                            @if($account->status === 'paid')
                                <span class="status-badge status-paid">Pagado</span>
                            @elseif($account->status === 'partial')
                                <span class="status-badge status-partial">Abono Parcial</span>
                            @else
                                <span class="status-badge status-pending">Pendiente</span>
                            @endif
                        </td>
                    </tr>
                    @if($account->installments->count() > 0)
                    <tr id="installments-{{ $account->id }}" style="display: none; background: rgba(0,0,0,0.1);">
                        <td colspan="6" style="padding: 1.5rem;">
                            <h5 style="margin-bottom: 1rem; font-size: 0.95rem; color: var(--text-main);"><i class="fa-solid fa-calendar-days"></i> Cronograma de Pagos</h5>
                            <table class="table table-sm" style="background: transparent; margin: 0;">
                                <thead>
                                    <tr>
                                        <th style="font-size: 0.75rem; background: transparent;">Cuota</th>
                                        <th style="font-size: 0.75rem; background: transparent;">Vencimiento</th>
                                        <th class="text-end" style="font-size: 0.75rem; background: transparent;">Monto</th>
                                        <th class="text-end" style="font-size: 0.75rem; background: transparent;">Pagado</th>
                                        <th class="text-center" style="font-size: 0.75rem; background: transparent;">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($account->installments as $inst)
                                    <tr>
                                        <td>#{{ $inst->installment_number }}</td>
                                        <td>{{ $inst->due_date->format('d/m/Y') }}
                                            @if($inst->status !== 'paid' && $inst->due_date->isPast())
                                                <span class="badge bg-danger ms-2">Vencida</span>
                                            @endif
                                        </td>
                                        <td class="text-end">${{ number_format($inst->amount, 2) }}</td>
                                        <td class="text-end">${{ number_format($inst->paid_amount, 2) }}</td>
                                        <td class="text-center">
                                            @if($inst->status === 'paid')
                                                <span class="status-badge status-paid" style="padding: 0.15rem 0.5rem; font-size: 0.75rem;">Pagado</span>
                                            @elseif($inst->status === 'partial')
                                                <span class="status-badge status-partial" style="padding: 0.15rem 0.5rem; font-size: 0.75rem;">Abono</span>
                                            @else
                                                <span class="status-badge status-pending" style="padding: 0.15rem 0.5rem; font-size: 0.75rem;">Pendiente</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    @endif
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                            <i class="fa-solid fa-file-invoice fa-3x" style="margin-bottom: 1rem; opacity: 0.5;"></i>
                            <br>El cliente no tiene cargos a crédito registrados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleInstallments(id) {
        let el = document.getElementById('installments-' + id);
        let icon = document.getElementById('icon-inst-' + id);
        if (el.style.display === 'none') {
            el.style.display = 'table-row';
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-up');
        } else {
            el.style.display = 'none';
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
        }
    }
</script>
@endpush
