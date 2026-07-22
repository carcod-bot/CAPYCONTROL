@extends('layouts.app')

@section('title', 'Cuentas por Cobrar')

@push('styles')
<style>
    .credits-layout {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    
    .toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--surface);
        padding: 1rem 1.5rem;
        border-radius: 12px;
        box-shadow: var(--shadow-sm);
    }
    
    .search-bar {
        display: flex;
        gap: 0.5rem;
        flex: 1;
        max-width: 400px;
    }
    
    .table-container {
        background: var(--surface);
        border-radius: 16px;
        box-shadow: var(--shadow-md);
        overflow: hidden;
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
        background: var(--background);
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }
    
    .table tbody tr:hover {
        background: var(--background);
    }
    
    .btn-view {
        background: rgba(59, 130, 246, 0.15);
        color: #3b82f6;
        padding: 0.4rem 1rem;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
    }
    .btn-view:hover {
        background: #3b82f6;
        color: white;
    }
</style>
@endpush

@section('content')
<div class="credits-layout">
    <div class="toolbar">
        <form class="search-bar" action="{{ route('credits.index') }}" method="GET">
            <input type="text" name="q" class="form-control" placeholder="Buscar cliente deudor..." value="{{ request('q') }}">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i> Buscar</button>
            @if(request('q'))
                <a href="{{ route('credits.index') }}" class="btn btn-secondary" title="Limpiar"><i class="fa-solid fa-times"></i></a>
            @endif
        </form>
    </div>
    
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Documento</th>
                    <th>Teléfono</th>
                    <th>Límite de Crédito</th>
                    <th>Deuda Pendiente</th>
                    <th width="120px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($debtors as $d)
                <tr>
                    <td style="font-weight: 600; color: var(--primary);">{{ $d->name }}</td>
                    <td>{{ $d->document_id }}</td>
                    <td>{{ $d->phone ?: '-' }}</td>
                    <td>${{ number_format($d->credit_limit, 2) }}</td>
                    <td style="color: var(--danger); font-weight: 700;">
                        ${{ number_format($d->current_balance, 2) }}
                    </td>
                    <td>
                        <a href="{{ route('credits.show', $d) }}" class="btn-view">
                            <i class="fa-solid fa-eye"></i> Detalle
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                        <i class="fa-solid fa-hand-holding-dollar fa-3x" style="margin-bottom: 1rem; opacity: 0.5;"></i>
                        <br>No hay clientes con deudas pendientes actualmente.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div style="margin-top: 1rem;">
        {{ $debtors->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
