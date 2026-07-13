@extends('layouts.app')

@section('title', 'Cuadre General')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="card-title"><i class="fa-solid fa-scale-balanced"></i> Cuadre General de Cajas</h2>
    </div>
    
    <div class="card-body">
        <form method="GET" action="{{ route('admin.cuadre.index') }}" class="mb-4" style="background: var(--surface); padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border);">
            <div style="display: flex; gap: 1rem; align-items: flex-end;">
                <div class="form-group" style="flex: 1; margin-bottom: 0;">
                    <label class="form-label">Fecha del Cuadre</label>
                    <input type="date" name="date" class="form-control" value="{{ $date }}">
                </div>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-search"></i> Buscar
                    </button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Caja</th>
                        <th>Cajero</th>
                        <th>Estado</th>
                        <th>Apertura</th>
                        <th>Monto Base Inicial</th>
                        <th>Diferencia (Base)</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $session)
                    <tr class="register-row {{ $session->status === 'open' ? 'row-open' : 'row-closed' }}">
                        <td class="font-bold">{{ $session->cashRegister->name ?? 'N/A' }}</td>
                        <td>{{ $session->user->username ?? 'N/A' }}</td>
                        <td>
                            @if($session->status === 'open')
                                <span class="badge badge-success">Abierta</span>
                            @else
                                <span class="badge badge-closed">Cerrada</span>
                            @endif
                        </td>
                        <td>{{ $session->opened_at ? $session->opened_at->format('d/m/Y H:i') : 'N/A' }}</td>
                        <td style="font-family: monospace;">{{ number_format($session->opening_amount, 2) }}</td>
                        <td style="font-family: monospace; font-weight: bold; color: {{ $session->difference < 0 ? 'var(--danger)' : ($session->difference > 0 ? 'var(--success)' : 'inherit') }}">
                            @if($session->status === 'closed')
                                {{ $session->difference > 0 ? '+' : '' }}{{ number_format($session->difference, 2) }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($session->status === 'open')
                                <button class="btn btn-primary btn-sm" onclick="openForceCloseModal({{ $session->id }}, '{{ $session->cashRegister->name }}', '{{ $session->user->username }}')">
                                    <i class="fa-solid fa-lock"></i> Forzar Cierre
                                </button>
                            @else
                                <a href="{{ route('declarations.show', $session->id) }}" class="btn btn-secondary btn-sm" title="Ver Detalle">
                                    <i class="fa-solid fa-eye"></i> Detalle
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 2rem;">
                            <i class="fa-solid fa-calendar-xmark fa-3x" style="color: var(--text-muted); margin-bottom: 1rem;"></i>
                            <p>No hay sesiones registradas para la fecha {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Force Close Modal -->
<div id="forceCloseModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-lock"></i> Cierre Forzado de Caja</h3>
            <button class="modal-close" onclick="closeModal('forceCloseModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="forceCloseForm" method="POST" action="">
            @csrf
            <div class="pos-close-info mb-3">
                <div class="pos-close-info-row">
                    <span>Caja:</span>
                    <strong id="forceCloseRegisterLabel"></strong>
                </div>
                <div class="pos-close-info-row">
                    <span>Cajero:</span>
                    <strong id="forceCloseUserLabel"></strong>
                </div>
            </div>
            
            <p class="text-muted mb-3" style="font-size: 0.9rem;">
                Ingrese el dinero físico contabilizado en caja. El sistema calculará la diferencia automáticamente.
            </p>

            <div style="background: var(--background); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid var(--border);" id="dynamicDeclarationFields">
                <div class="text-center" style="padding: 1rem;">
                    <i class="fa-solid fa-spinner fa-spin"></i> Cargando métodos a declarar...
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" style="font-weight: 600; color: var(--text-main); margin-bottom: 0.5rem; display: block;">Notas / Observaciones</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Motivo del cierre forzado..."></textarea>
            </div>
            
            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('forceCloseModal')">Cancelar</button>
                <button type="submit" class="btn btn-danger" id="btnProcessClose">
                    <i class="fa-solid fa-triangle-exclamation"></i> Procesar Cierre
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        flatpickr("input[type='date']", {
            locale: "es",
            dateFormat: "Y-m-d",
            allowInput: true
        });
    });

    function openForceCloseModal(sessionId, registerName, username) {
        document.getElementById('forceCloseRegisterLabel').textContent = registerName;
        document.getElementById('forceCloseUserLabel').textContent = username;
        
        const form = document.getElementById('forceCloseForm');
        form.action = `{{ url('admin/cuadre') }}/${sessionId}/force-close`;
        
        document.getElementById('dynamicDeclarationFields').innerHTML = '<div class="text-center" style="padding: 1rem;"><i class="fa-solid fa-spinner fa-spin"></i> Cargando métodos a declarar...</div>';
        document.getElementById('forceCloseModal').classList.add('open');
        document.getElementById('btnProcessClose').disabled = true;

        fetch(`{{ url('admin/cuadre') }}/${sessionId}/declaration-fields`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('dynamicDeclarationFields').innerHTML = html;
                document.getElementById('btnProcessClose').disabled = false;
            })
            .catch(error => {
                document.getElementById('dynamicDeclarationFields').innerHTML = '<div class="alert alert-danger">Error cargando métodos.</div>';
                console.error(error);
            });
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('open');
    }
</script>
@endpush
