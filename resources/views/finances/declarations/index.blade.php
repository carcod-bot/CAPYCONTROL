@extends('layouts.app')

@section('title', 'Declaraciones de Caja')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="card-title"><i class="fa-solid fa-list-check"></i> Reporte de Declaraciones</h2>
    </div>
    
    <div class="card-body">
        <form method="GET" action="{{ route('declarations.index') }}" class="mb-4" style="background: var(--surface); padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border);">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Caja</label>
                    <select name="cash_register_id" class="form-select">
                        <option value="">Todas</option>
                        @foreach($registers as $register)
                            <option value="{{ $register->id }}" {{ request('cash_register_id') == $register->id ? 'selected' : '' }}>
                                {{ $register->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Usuario</label>
                    <select name="user_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->username }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Abierto</option>
                        <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Cerrado</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Desde</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                
                <div class="form-group" style="display: flex; align-items: flex-end;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fa-solid fa-filter"></i> Filtrar
                    </button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th># Turno</th>
                        <th>Caja</th>
                        <th>Usuario</th>
                        <th>Estado</th>
                        <th>Apertura</th>
                        <th>Cierre</th>
                        <th>Esperado (Base)</th>
                        <th>Declarado (Base)</th>
                        <th>Diferencia (Base)</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $session)
                    <tr>
                        <td>{{ $session->turn_number }}</td>
                        <td>{{ $session->cashRegister->name ?? 'N/A' }}</td>
                        <td>{{ $session->user->username ?? 'N/A' }}</td>
                        <td>
                            @if($session->status === 'open')
                                <span class="badge" style="background-color: var(--warning); color: #fff;">Abierto</span>
                            @else
                                <span class="badge" style="background-color: var(--success); color: #fff;">Cerrado</span>
                            @endif
                        </td>
                        <td>{{ $session->opened_at ? $session->opened_at->format('d/m/Y H:i') : 'N/A' }}</td>
                        <td>{{ $session->closed_at ? $session->closed_at->format('d/m/Y H:i') : 'N/A' }}</td>
                        <td style="font-family: monospace;">{{ number_format($session->expected_amount, 2) }}</td>
                        <td style="font-family: monospace;">
                            {{ $session->actual_amount !== null ? number_format($session->actual_amount, 2) : 'No Declaró' }}
                        </td>
                        <td style="font-family: monospace; font-weight: bold; color: {{ $session->difference < 0 ? 'var(--danger)' : ($session->difference > 0 ? 'var(--success)' : 'inherit') }}">
                            @if($session->difference !== null)
                                {{ $session->difference > 0 ? '+' : '' }}{{ number_format($session->difference, 2) }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('declarations.show', $session->id) }}" class="btn btn-primary btn-sm" title="Ver Detalle">
                                <i class="fa-solid fa-eye"></i> Ver
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center" style="padding: 2rem;">
                            <i class="fa-solid fa-folder-open fa-3x" style="color: var(--text-muted); margin-bottom: 1rem;"></i>
                            <p>No se encontraron registros que coincidan con los filtros.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 1.5rem;">
            {{ $sessions->appends(request()->query())->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid var(--border);
        background-color: var(--surface);
        border-radius: 4px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
        color: var(--text-main);
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
    .dark-mode .select2-container--default .select2-selection--single {
        background-color: var(--surface);
        border-color: var(--border);
    }
    .dark-mode .select2-dropdown {
        background-color: var(--surface);
        border-color: var(--border);
    }
    .dark-mode .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: var(--primary);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>
<script>
$(document).ready(function() {
    $('.form-select').select2({
        width: '100%'
    });

    flatpickr(".form-control[type='date']", {
        locale: "es",
        dateFormat: "Y-m-d",
        allowInput: true
    });
});
</script>
@endpush
