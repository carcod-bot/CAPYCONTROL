@extends('layouts.app')

@section('title', 'Detalle de Declaración')

@section('content')
<div class="mb-4">
    <a href="{{ route('declarations.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Volver al Listado
    </a>
</div>

<div class="row" style="display: flex; flex-wrap: wrap; gap: 1.5rem; margin-bottom: 1.5rem;">
    <!-- Detalles del Turno -->
    <div class="col" style="flex: 1; min-width: 300px;">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title"><i class="fa-solid fa-circle-info"></i> Información del Turno #{{ $session->turn_number }}</h3>
            </div>
            <div class="card-body">
                <p><strong>Caja:</strong> {{ $session->cashRegister->name ?? 'N/A' }}</p>
                <p><strong>Usuario:</strong> {{ $session->user->username ?? 'N/A' }}</p>
                <p><strong>Estado:</strong> 
                    @if($session->status === 'open')
                        <span class="badge" style="background-color: var(--warning); color: #fff;">Abierto</span>
                    @else
                        <span class="badge" style="background-color: var(--success); color: #fff;">Cerrado</span>
                    @endif
                </p>
                <p><strong>Apertura:</strong> {{ $session->opened_at ? $session->opened_at->format('d/m/Y h:i A') : 'N/A' }}</p>
                <p><strong>Cierre:</strong> {{ $session->closed_at ? $session->closed_at->format('d/m/Y h:i A') : 'N/A' }}</p>
                
                @if($session->closing_notes)
                    <div style="margin-top: 1rem; padding: 1rem; background: var(--background); border-left: 4px solid var(--primary); border-radius: 4px;">
                        <strong>Notas de Cierre:</strong><br>
                        {{ $session->closing_notes }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Resumen Global -->
    <div class="col" style="flex: 1; min-width: 300px;">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title"><i class="fa-solid fa-chart-pie"></i> Resumen Global (Moneda Base)</h3>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div style="padding: 1rem; background: rgba(0, 123, 255, 0.1); border-radius: 8px; text-align: center;">
                        <span style="display: block; font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Esperado Sistema</span>
                        <strong style="font-size: 1.5rem; font-family: monospace; color: var(--primary);">{{ number_format($session->expected_amount, 2) }}</strong>
                    </div>
                    
                    <div style="padding: 1rem; background: rgba(40, 167, 69, 0.1); border-radius: 8px; text-align: center;">
                        <span style="display: block; font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Declarado</span>
                        <strong style="font-size: 1.5rem; font-family: monospace; color: var(--success);">
                            {{ $session->actual_amount !== null ? number_format($session->actual_amount, 2) : 'N/A' }}
                        </strong>
                    </div>

                    <div style="padding: 1rem; background: {{ $session->difference < 0 ? 'rgba(220, 53, 69, 0.1)' : 'rgba(40, 167, 69, 0.1)' }}; border-radius: 8px; text-align: center; grid-column: span 2;">
                        <span style="display: block; font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Diferencia</span>
                        <strong style="font-size: 1.75rem; font-family: monospace; color: {{ $session->difference < 0 ? 'var(--danger)' : 'var(--success)' }};">
                            @if($session->difference !== null)
                                {{ $session->difference > 0 ? '+' : '' }}{{ number_format($session->difference, 2) }}
                            @else
                                No Declarado
                            @endif
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Comparativa por Método de Pago -->
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title"><i class="fa-solid fa-money-check-dollar"></i> Desglose por Método de Pago</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Método</th>
                        <th>Auto Declarado</th>
                        <th class="text-right">Ventas</th>
                        <th class="text-right">Depósitos</th>
                        <th class="text-right">Retiros</th>
                        <th class="text-right" style="background: rgba(0,0,0,0.03);">Esperado Sistema</th>
                        <th class="text-right" style="background: rgba(0,0,0,0.03);">Declarado Físico</th>
                        <th class="text-right" style="background: rgba(0,0,0,0.03);">Diferencia</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($methodsSummary as $summary)
                    <tr>
                        <td>
                            <strong>{{ $summary['method']->description }}</strong>
                            <br><small class="text-muted">{{ $summary['method']->currency->code }}</small>
                        </td>
                        <td>
                            @if($summary['is_auto_declare'])
                                <span class="badge" style="background: var(--info); color: #fff;">Sí</span>
                            @else
                                <span class="badge" style="background: var(--secondary); color: #fff;">No</span>
                            @endif
                        </td>
                        <td class="text-right" style="font-family: monospace;">{{ number_format($summary['sales'], 2) }}</td>
                        <td class="text-right" style="font-family: monospace; color: var(--success);">{{ number_format($summary['deposits'], 2) }}</td>
                        <td class="text-right" style="font-family: monospace; color: var(--danger);">{{ number_format($summary['withdrawals'], 2) }}</td>
                        <td class="text-right" style="font-family: monospace; font-weight: 500; background: rgba(0,0,0,0.02);">
                            {{ number_format($summary['expected'], 2) }}
                        </td>
                        <td class="text-right" style="font-family: monospace; font-weight: bold; background: rgba(0,0,0,0.02);">
                            @if($summary['is_auto_declare'])
                                <span class="text-muted" style="font-size: 0.85em;">(Auto)</span> {{ number_format($summary['expected'], 2) }}
                            @else
                                {{ number_format($summary['declared'], 2) }}
                            @endif
                        </td>
                        <td class="text-right" style="font-family: monospace; font-weight: bold; background: rgba(0,0,0,0.02); color: {{ $summary['difference'] < 0 ? 'var(--danger)' : ($summary['difference'] > 0 ? 'var(--success)' : 'inherit') }}">
                            @if($summary['is_auto_declare'])
                                0.00
                            @else
                                {{ $summary['difference'] > 0 ? '+' : '' }}{{ number_format($summary['difference'], 2) }}
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">No hubo movimientos registrados en esta sesión.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            <small class="text-muted"><i class="fa-solid fa-circle-info"></i> El "Esperado Sistema" en Efectivo VES incluye el monto de apertura de caja ({{ number_format($session->opening_amount, 2) }}).</small>
        </div>
    </div>
</div>

<!-- Movimientos (Arqueos, Retiros) -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fa-solid fa-money-bill-transfer"></i> Retiros y Depósitos (Arqueos Parciales)</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Fecha/Hora</th>
                        <th>Tipo</th>
                        <th>Motivo</th>
                        <th>Método</th>
                        <th class="text-right">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($session->movements as $movement)
                    <tr>
                        <td>{{ $movement->created_at->format('d/m/Y h:i:s A') }}</td>
                        <td>
                            @if($movement->type === 'withdrawal')
                                <span style="color: var(--danger);"><i class="fa-solid fa-arrow-down"></i> Retiro</span>
                            @else
                                <span style="color: var(--success);"><i class="fa-solid fa-arrow-up"></i> Depósito</span>
                            @endif
                        </td>
                        <td>{{ $movement->reason }}</td>
                        <td>{{ $movement->paymentMethod->description ?? 'N/A' }}</td>
                        <td class="text-right" style="font-family: monospace; font-weight: bold;">
                            {{ number_format($movement->amount, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">No se realizaron retiros ni depósitos manuales en esta sesión.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.text-right { text-align: right; }
</style>
@endsection
