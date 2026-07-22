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
        padding: 1rem 1.5rem;
        text-align: left;
        border-bottom: 1px solid var(--border);
    }
    
    .table th {
        background: var(--surface);
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        font-size: 0.85rem;
    }
    
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
<div class="mb-3">
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
                        <th>Monto Total</th>
                        <th>Monto Pagado</th>
                        <th>Restante</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customer->creditAccounts as $account)
                    <tr>
                        <td>{{ $account->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @if($account->sale)
                                <span title="Venta #{{ $account->sale->id }}">{{ $account->sale->ticket_number }}</span>
                            @else
                                N/A
                            @endif
                        </td>
                        <td style="font-weight: 600;">${{ number_format($account->amount, 2) }}</td>
                        <td style="color: #10b981;">${{ number_format($account->paid_amount, 2) }}</td>
                        <td style="color: var(--danger); font-weight: 700;">
                            ${{ number_format($account->amount - $account->paid_amount, 2) }}
                        </td>
                        <td>
                            @if($account->status === 'paid')
                                <span class="status-badge status-paid">Pagado</span>
                            @elseif($account->status === 'partial')
                                <span class="status-badge status-partial">Abono Parcial</span>
                            @else
                                <span class="status-badge status-pending">Pendiente</span>
                            @endif
                        </td>
                    </tr>
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
