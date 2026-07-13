@extends('layouts.app')
@section('title', 'Monitoreo de Puntos de Venta')

@section('content')
<!-- Stats Cards -->
<div class="pos-stats-grid">
    <div class="pos-stat-card">
        <div class="pos-stat-icon bg-primary-soft">
            <i class="fa-solid fa-cash-register"></i>
        </div>
        <div class="pos-stat-info">
            <span class="pos-stat-value">{{ $totalRegisters }}</span>
            <span class="pos-stat-label">Cajas Totales</span>
        </div>
    </div>
    <div class="pos-stat-card">
        <div class="pos-stat-icon bg-success-soft">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div class="pos-stat-info">
            <span class="pos-stat-value text-success">{{ $openRegisters }}</span>
            <span class="pos-stat-label">Cajas Abiertas</span>
        </div>
    </div>
    <div class="pos-stat-card">
        <div class="pos-stat-icon bg-danger-soft">
            <i class="fa-solid fa-circle-xmark"></i>
        </div>
        <div class="pos-stat-info">
            <span class="pos-stat-value text-danger">{{ $closedRegisters }}</span>
            <span class="pos-stat-label">Cajas Cerradas</span>
        </div>
    </div>
    <div class="pos-stat-card">
        <div class="pos-stat-icon bg-info-soft">
            <i class="fa-solid fa-receipt"></i>
        </div>
        <div class="pos-stat-info">
            <span class="pos-stat-value">{{ $totalSalesToday }}</span>
            <span class="pos-stat-label">Ventas Hoy</span>
        </div>
    </div>
</div>

<!-- Action Bar -->
<div class="pos-action-bar">
    <div class="pos-action-bar-left">
        <h2 class="pos-section-title"><i class="fa-solid fa-desktop"></i> Monitoreo — Cajas Activas</h2>
    </div>
    <div class="pos-action-bar-right">
        <a href="{{ route('pos-control.registers') }}" class="btn btn-primary">
            <i class="fa-solid fa-server"></i> Gestión de Cajas
        </a>
        <button class="btn btn-secondary" onclick="window.location.reload()">
            <i class="fa-solid fa-arrows-rotate"></i> Actualizar
        </button>
    </div>
</div>

<!-- Active Sessions Monitoring Table -->
<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-container" style="border: none; margin: 0; box-shadow: none;">
        <table class="table" id="registersTable">
            <thead>
                <tr>
                    <th>Nº Caja</th>
                    <th>Hostname / IP</th>
                    <th>Cajero</th>
                    <th>Estado</th>
                    <th>Nº Ventas</th>
                    <th>Nº Retiros</th>
                    <th>Turno</th>
                    <th>Nº Devol.</th>
                    <th>Fecha Inicio</th>
                    <th>Hora Inicio</th>
                    <th>Fact. Pend.</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($openSessions as $session)
                    @php $register = $session->cashRegister; @endphp
                    <tr class="register-row row-open" data-register-id="{{ $register->id }}">
                        <td><span class="register-number">{{ $register->number }}</span></td>
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                @if($register->hostname)
                                    <span style="font-weight:600; font-size:0.85rem;">
                                        <i class="fa-solid fa-computer" style="color:var(--primary);"></i>
                                        {{ $register->hostname }}
                                    </span>
                                @endif
                                @if($register->ip_address)
                                    <span style="font-size:0.78rem; color:var(--text-muted); font-family:monospace;">
                                        <i class="fa-solid fa-network-wired"></i>
                                        {{ $register->ip_address }}
                                    </span>
                                @endif
                                @if(!$register->hostname && !$register->ip_address)
                                    <span class="text-muted">—</span>
                                @endif
                            </div>
                        </td>
                        <td class="font-bold">{{ strtoupper($session->user->username) }}</td>
                        <td>
                            <span class="badge badge-success">Abierta</span>
                        </td>
                        <td class="text-center">{{ $session->total_sales }}</td>
                        <td class="text-center">{{ $session->total_withdrawals }}</td>
                        <td class="text-center">{{ $session->turn_number }}</td>
                        <td class="text-center">{{ $session->total_returns }}</td>
                        <td>{{ $session->opened_at->format('d/m/Y') }}</td>
                        <td>{{ $session->opened_at->format('h:i:s a') }}</td>
                        <td class="text-center">{{ $session->pending_invoices }}</td>
                        <td>
                            <div class="table-actions">
                                <button class="btn-icon btn-icon-warning" title="Retiro Parcial" onclick="openWithdrawModal({{ $session->id }}, '{{ $register->number }}')">
                                    <i class="fa-solid fa-money-bill-transfer"></i>
                                </button>
                                <button class="btn-icon btn-icon-danger" title="Cierre de Caja" onclick="openCloseSessionModal({{ $session->id }}, '{{ $register->number }}', '{{ $session->user->username }}')">
                                    <i class="fa-solid fa-lock"></i>
                                </button>
                                <button class="btn-icon btn-icon-info" title="Ver Historial" onclick="viewSessionHistory({{ $register->id }}, '{{ $register->number }}')">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                </button>
                                <a href="{{ route('pos-control.registers') }}" class="btn-icon btn-icon-secondary" title="Editar Caja">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center text-muted" style="padding: 3rem;">
                            <i class="fa-solid fa-circle-check" style="font-size: 2.5rem; margin-bottom: 0.75rem; display: block; color: var(--success);"></i>
                            <strong style="display:block; font-size:1.1rem; margin-bottom:0.5rem;">No hay cajas con sesión activa en este momento</strong>
                            <span style="font-size:0.9rem;">Cuando un cajero abra turno desde CapyControl, aparecerá aquí automáticamente.</span>
                            <br><br>
                            <a href="{{ route('pos-control.registers') }}" class="btn btn-primary" style="margin-top:0.5rem;">
                                <i class="fa-solid fa-server"></i> Ir a Gestión de Cajas
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ========== MODALS ========== -->

<!-- Close Session Modal -->
<div class="modal-overlay" id="closeSessionModal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-lock"></i> Cierre de Caja</h3>
            <button class="modal-close" onclick="closeModal('closeSessionModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="closeSessionForm" onsubmit="event.preventDefault(); submitCloseSession();">
            <input type="hidden" id="closeSessionId">
            <div class="pos-close-info">
                <div class="pos-close-info-row">
                    <span>Caja:</span>
                    <strong id="closeSessionRegisterLabel"></strong>
                </div>
                <div class="pos-close-info-row">
                    <span>Cajero:</span>
                    <strong id="closeSessionUserLabel"></strong>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Monto Real en Caja</label>
                <input type="number" id="closeSessionActualAmount" name="actual_amount" class="form-control" step="0.01" min="0" placeholder="Ingrese el monto contado...">
            </div>
            <div class="form-group">
                <label class="form-label">Notas del Cierre</label>
                <textarea id="closeSessionNotes" name="closing_notes" class="form-control" rows="3" placeholder="Observaciones del cierre..."></textarea>
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('closeSessionModal')">Cancelar</button>
                <button type="submit" class="btn btn-danger"><i class="fa-solid fa-lock"></i> Cerrar Sesión</button>
            </div>
        </form>
    </div>
</div>

<!-- Withdraw Modal -->
<div class="modal-overlay" id="withdrawModal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-money-bill-transfer"></i> Retiro Parcial</h3>
            <button class="modal-close" onclick="closeModal('withdrawModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="withdrawForm" onsubmit="event.preventDefault(); submitWithdraw();">
            <input type="hidden" id="withdrawSessionId">
            <div class="form-group">
                <label class="form-label">Caja</label>
                <input type="text" id="withdrawRegisterLabel" class="form-control" readonly style="background: var(--background); font-weight: 700;">
            </div>
            <div class="form-group">
                <label class="form-label">Moneda (Método de Pago) <span class="text-danger">*</span></label>
                <select name="payment_method_id" class="form-control select2" required style="width: 100%;">
                    <option value="">Seleccione una moneda...</option>
                    @foreach(\App\Models\PaymentMethod::where('used_in_pos', true)->get() as $pm)
                        <option value="{{ $pm->id }}">{{ $pm->description }} ({{ $pm->currency ? $pm->currency->code : '' }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Monto a Retirar <span class="text-danger">*</span></label>
                <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required placeholder="0.00">
            </div>
            <div class="form-group">
                <label class="form-label">Motivo</label>
                <input type="text" name="reason" class="form-control" placeholder="Ej: Retiro parcial de efectivo" value="Retiro parcial">
            </div>
            <div class="form-group">
                <label class="form-label">Notas</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Notas adicionales..."></textarea>
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('withdrawModal')">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Confirmar Retiro</button>
            </div>
        </form>
    </div>
</div>

<!-- Session History Modal -->
<div class="modal-overlay" id="historyModal">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-clock-rotate-left"></i> Historial de Sesiones — Caja <span id="historyRegisterLabel"></span></h3>
            <button class="modal-close" onclick="closeModal('historyModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="table-container" style="margin: 0;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Turno</th>
                        <th>Cajero</th>
                        <th>Estado</th>
                        <th>Apertura</th>
                        <th>Cierre</th>
                        <th>Ventas</th>
                        <th>Retiros</th>
                    </tr>
                </thead>
                <tbody id="historyTableBody">
                    <tr><td colspan="7" class="text-center text-muted">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('.select2').select2({ width: '100%' });
    });

    // === CLOSE SESSION ===
    function openCloseSessionModal(sessionId, registerNumber, username) {
        document.getElementById('closeSessionId').value = sessionId;
        document.getElementById('closeSessionRegisterLabel').textContent = 'Caja ' + registerNumber;
        document.getElementById('closeSessionUserLabel').textContent = username;
        document.getElementById('closeSessionActualAmount').value = '';
        document.getElementById('closeSessionNotes').value = '';
        openModal('closeSessionModal');
    }

    function submitCloseSession() {
        showGlobalLoader();
        const sessionId = document.getElementById('closeSessionId').value;
        const data = {
            actual_amount: document.getElementById('closeSessionActualAmount').value || null,
            closing_notes: document.getElementById('closeSessionNotes').value || null,
        };

        fetch(`/capycontrol/public/pos-control/sessions/${sessionId}/close`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                closeModal('closeSessionModal');
                window.location.reload();
            } else {
                showToast(data.message || 'Error al cerrar sesión');
            }
        })
        .catch(() => showToast('Error de conexión'))
        .finally(() => hideGlobalLoader());
    }

    // === WITHDRAW ===
    function openWithdrawModal(sessionId, registerNumber) {
        document.getElementById('withdrawSessionId').value = sessionId;
        document.getElementById('withdrawRegisterLabel').value = 'Caja ' + registerNumber;
        openModal('withdrawModal');
    }

    function submitWithdraw() {
        const sessionId = document.getElementById('withdrawSessionId').value;
        const form = document.getElementById('withdrawForm');
        submitAjaxForm(form, `/capycontrol/public/pos-control/sessions/${sessionId}/withdraw`, () => {
            closeModal('withdrawModal');
            window.location.reload();
        });
    }

    // === SESSION HISTORY ===
    function viewSessionHistory(registerId, registerNumber) {
        document.getElementById('historyRegisterLabel').textContent = registerNumber;
        document.getElementById('historyTableBody').innerHTML = '<tr><td colspan="7" class="text-center text-muted">Cargando...</td></tr>';
        openModal('historyModal');

        fetch(`/capycontrol/public/pos-control/registers/${registerId}/sessions`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(sessions => {
            const tbody = document.getElementById('historyTableBody');
            if (sessions.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No hay sesiones registradas</td></tr>';
                return;
            }
            tbody.innerHTML = '';
            sessions.forEach(s => {
                const openedAt = new Date(s.opened_at);
                const closedAt = s.closed_at ? new Date(s.closed_at) : null;
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="text-center font-bold">${s.turn_number}</td>
                    <td class="font-bold">${s.user ? s.user.username.toUpperCase() : '—'}</td>
                    <td><span class="badge ${s.status === 'open' ? 'badge-success' : 'badge-closed'}">${s.status === 'open' ? 'Abierta' : 'Cerrada'}</span></td>
                    <td>${openedAt.toLocaleDateString('es-VE')} ${openedAt.toLocaleTimeString('es-VE')}</td>
                    <td>${closedAt ? closedAt.toLocaleDateString('es-VE') + ' ' + closedAt.toLocaleTimeString('es-VE') : '—'}</td>
                    <td class="text-center">${s.total_sales}</td>
                    <td class="text-center">${s.total_withdrawals}</td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(() => {
            document.getElementById('historyTableBody').innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error al cargar historial</td></tr>';
        });
    }
</script>
@endpush
