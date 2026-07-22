@extends('layouts.app')
@section('title', 'Operaciones Autorizadas (Eventos POS)')

@section('content')
<div class="page-header">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="page-title"><i class="fa-solid fa-shield-halved" style="color:var(--primary); margin-right:10px;"></i> Operaciones Autorizadas</h1>
            <p class="text-muted mt-2">Historial de eventos de seguridad y autorizaciones en el POS.</p>
        </div>
    </div>
</div>

<div class="content-wrapper">
    <div class="card" style="padding: 1.5rem;">
        
        <!-- Search and Filter Form -->
        <form method="GET" action="{{ route('pos-control.events') }}" class="mb-4 flex flex-wrap" style="gap: 1rem; align-items: flex-start;">
            <div style="flex: 1; min-width: 250px;">
                <input type="text" name="q" class="form-control" placeholder="Buscar por supervisor, detalles o cajero..." value="{{ request('q') }}">
            </div>
            <div style="width: 200px;">
                <select name="cash_register_id" class="form-control">
                    <option value="">Todas las cajas</option>
                    @foreach($cashRegisters as $caja)
                        <option value="{{ $caja->id }}" {{ request('cash_register_id') == $caja->id ? 'selected' : '' }}>{{ $caja->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="width: 200px;">
                <select name="event_type" class="form-control">
                    <option value="">Todos los eventos</option>
                    <option value="open_drawer" {{ request('event_type') == 'open_drawer' ? 'selected' : '' }}>Abrir Gaveta</option>
                    <option value="report_x" {{ request('event_type') == 'report_x' ? 'selected' : '' }}>Reporte X</option>
                    <option value="report_z" {{ request('event_type') == 'report_z' ? 'selected' : '' }}>Reporte Z</option>
                    <option value="void_sale" {{ request('event_type') == 'void_sale' ? 'selected' : '' }}>Anulación</option>
                </select>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1.5rem;"><i class="fa-solid fa-filter"></i> Filtrar</button>
                <a href="{{ route('pos-control.events') }}" class="btn btn-secondary" style="padding: 0.5rem 1.5rem; display: flex; align-items: center;">Limpiar</a>
            </div>
        </form>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Fecha y Hora</th>
                        <th>Caja</th>
                        <th>Cajero</th>
                        <th>Supervisor Autorizante</th>
                        <th>Tipo de Evento</th>
                        <th>Detalles</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $event)
                        <tr>
                            <td>
                                <div style="font-weight: 500;">{{ $event->created_at->format('d/m/Y') }}</div>
                                <div class="text-muted" style="font-size: 0.85rem;">{{ $event->created_at->format('h:i A') }}</div>
                            </td>
                            <td>
                                {{ $event->session && $event->session->cashRegister ? $event->session->cashRegister->name : 'N/A' }}
                                @if($event->session)
                                    <div class="text-muted" style="font-size: 0.8rem;">Sesión #{{ $event->session->id }}</div>
                                @endif
                            </td>
                            <td>
                                {{ $event->user ? $event->user->name : 'N/A' }}
                                <div class="text-muted" style="font-size: 0.8rem;">{{ $event->user ? $event->user->username : '' }}</div>
                            </td>
                            <td>
                                @if($event->supervisor_username)
                                    <span class="badge" style="background-color: var(--warning-light); color: var(--warning);">
                                        <i class="fa-solid fa-user-check"></i> {{ $event->supervisor_username }}
                                    </span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($event->event_type == 'open_drawer')
                                    <span class="badge" style="background-color: var(--primary-light); color: var(--primary);">Abrir Gaveta</span>
                                @elseif($event->event_type == 'report_x')
                                    <span class="badge" style="background-color: #fef3c7; color: #92400e;">Reporte X</span>
                                @elseif($event->event_type == 'report_z')
                                    <span class="badge" style="background-color: #ffedd5; color: #c2410c;">Reporte Z</span>
                                @elseif($event->event_type == 'void_sale')
                                    <span class="badge" style="background-color: var(--danger-light); color: var(--danger);">Anulación</span>
                                @else
                                    <span class="badge">{{ $event->event_type }}</span>
                                @endif
                            </td>
                            <td>
                                @if(is_array($event->details))
                                    <ul style="margin: 0; padding-left: 1rem; font-size: 0.85rem; color: var(--text-muted);">
                                        @foreach($event->details as $key => $val)
                                            <li><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ is_array($val) ? json_encode($val) : $val }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    {{ $event->details }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted" style="padding: 2rem;">
                                No se encontraron eventos que coincidan con los filtros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $events->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>
@endsection
