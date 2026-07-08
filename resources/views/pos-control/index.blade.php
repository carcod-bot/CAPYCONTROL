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
        <h2 class="pos-section-title"><i class="fa-solid fa-desktop"></i> Puntos de Venta</h2>
    </div>
    <div class="pos-action-bar-right">
        <button class="btn btn-primary" onclick="openModal('addRegisterModal')">
            <i class="fa-solid fa-plus"></i> Nueva Caja
        </button>
        <button class="btn btn-secondary" onclick="window.location.reload()">
            <i class="fa-solid fa-arrows-rotate"></i> Actualizar
        </button>
    </div>
</div>

<!-- Registers Table -->
<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-container" style="border: none; margin: 0; box-shadow: none;">
        <table class="table" id="registersTable">
            <thead>
                <tr>
                    <th>Nº Caja</th>
                    <th>Cajero</th>
                    <th>Estado</th>
                    <th>Nº Ventas</th>
                    <th>Nº Retiros</th>
                    <th>Turno</th>
                    <th>Nº Devol.</th>
                    <th>Fecha Inicio</th>
                    <th>Hora Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Hora Fin</th>
                    <th>Fact. Pend.</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($registers as $register)
                    @php
                        $session = $register->activeSession;
                        $isOpen = $session !== null;
                    @endphp
                    <tr class="register-row {{ $isOpen ? 'row-open' : 'row-closed' }}" data-register-id="{{ $register->id }}">
                        <td><span class="register-number">{{ $register->number }}</span></td>
                        <td class="font-bold">{{ $isOpen ? strtoupper($session->user->username) : '—' }}</td>
                        <td>
                            @if($isOpen)
                                <span class="badge badge-success">Abierta</span>
                            @else
                                <span class="badge badge-closed">Cerrada</span>
                            @endif
                        </td>
                        <td class="text-center">{{ $isOpen ? $session->total_sales : '—' }}</td>
                        <td class="text-center">{{ $isOpen ? $session->total_withdrawals : '—' }}</td>
                        <td class="text-center">{{ $isOpen ? $session->turn_number : '—' }}</td>
                        <td class="text-center">{{ $isOpen ? $session->total_returns : '—' }}</td>
                        <td>{{ $isOpen ? $session->opened_at->format('d/m/Y') : '—' }}</td>
                        <td>{{ $isOpen ? $session->opened_at->format('h:i:s a') : '—' }}</td>
                        <td>{{ ($isOpen && $session->closed_at) ? $session->closed_at->format('d/m/Y') : '' }}</td>
                        <td>{{ ($isOpen && $session->closed_at) ? $session->closed_at->format('h:i:s a') : '' }}</td>
                        <td class="text-center">{{ $isOpen ? $session->pending_invoices : '—' }}</td>
                        <td>
                            <div class="table-actions">
                                @if($isOpen)
                                    <button class="btn-icon btn-icon-warning" title="Retiro Parcial" onclick="openWithdrawModal({{ $session->id }}, '{{ $register->number }}')">
                                        <i class="fa-solid fa-money-bill-transfer"></i>
                                    </button>
                                    <button class="btn-icon btn-icon-danger" title="Cierre de Caja" onclick="openCloseSessionModal({{ $session->id }}, '{{ $register->number }}', '{{ $session->user->username }}')">
                                        <i class="fa-solid fa-lock"></i>
                                    </button>
                                @else
                                    <button class="btn-icon btn-icon-success" title="Abrir Sesión" onclick="openNewSessionModal({{ $register->id }}, '{{ $register->number }}')">
                                        <i class="fa-solid fa-lock-open"></i>
                                    </button>
                                @endif
                                <button class="btn-icon btn-icon-info" title="Ver Historial" onclick="viewSessionHistory({{ $register->id }}, '{{ $register->number }}')">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                </button>
                                <button class="btn-icon btn-icon-secondary" title="Editar Caja" onclick="openEditRegisterModal({{ $register->id }}, '{{ $register->number }}', '{{ $register->name }}', '{{ $register->location }}')">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" class="text-center text-muted" style="padding: 3rem;">
                            <i class="fa-solid fa-cash-register" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
                            No hay cajas registradas. Crea una nueva caja para comenzar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Today's Sessions Summary -->
@if($todaySessions->count() > 0)
<div style="margin-top: 2rem;">
    <h2 class="pos-section-title"><i class="fa-solid fa-clock"></i> Sesiones del Día</h2>
    <div class="card" style="padding: 0; overflow: hidden; margin-top: 1rem;">
        <div class="table-container" style="border: none; margin: 0; box-shadow: none;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Caja</th>
                        <th>Cajero</th>
                        <th>Turno</th>
                        <th>Estado</th>
                        <th>Apertura</th>
                        <th>Cierre</th>
                        <th>Ventas</th>
                        <th>Retiros</th>
                        <th>Monto Inicial</th>
                        <th>Monto Esperado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($todaySessions as $s)
                    <tr>
                        <td><span class="register-number">{{ $s->cashRegister->number }}</span></td>
                        <td class="font-bold">{{ strtoupper($s->user->username) }}</td>
                        <td class="text-center">{{ $s->turn_number }}</td>
                        <td>
                            @if($s->isOpen())
                                <span class="badge badge-success">Abierta</span>
                            @else
                                <span class="badge badge-closed">Cerrada</span>
                            @endif
                        </td>
                        <td>{{ $s->opened_at->format('h:i:s a') }}</td>
                        <td>{{ $s->closed_at ? $s->closed_at->format('h:i:s a') : '—' }}</td>
                        <td class="text-center">{{ $s->total_sales }}</td>
                        <td class="text-center">{{ $s->total_withdrawals }}</td>
                        <td class="text-right">{{ number_format($s->opening_amount, 2) }}</td>
                        <td class="text-right font-bold">{{ number_format($s->expected_amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- ========== MODALS ========== -->

<!-- Add Register Modal -->
<div class="modal-overlay" id="addRegisterModal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-plus"></i> Nueva Caja</h3>
            <button class="modal-close" onclick="closeModal('addRegisterModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="addRegisterForm" onsubmit="event.preventDefault(); submitAddRegister();">
            <div class="form-group">
                <label class="form-label">Nº Caja <span class="text-danger">*</span></label>
                <input type="text" name="number" class="form-control" placeholder="Ej: 001" required>
            </div>
            <div class="form-group">
                <label class="form-label">Nombre</label>
                <input type="text" name="name" class="form-control" placeholder="Ej: Caja Principal">
            </div>
            <div class="form-group">
                <label class="form-label">Ubicación</label>
                <input type="text" name="location" class="form-control" placeholder="Ej: Entrada">
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addRegisterModal')">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Register Modal -->
<div class="modal-overlay" id="editRegisterModal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-pen"></i> Editar Caja</h3>
            <button class="modal-close" onclick="closeModal('editRegisterModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="editRegisterForm" onsubmit="event.preventDefault(); submitEditRegister();">
            <input type="hidden" id="editRegisterId">
            <div class="form-group">
                <label class="form-label">Nº Caja <span class="text-danger">*</span></label>
                <input type="text" id="editRegisterNumber" name="number" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Nombre</label>
                <input type="text" id="editRegisterName" name="name" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Ubicación</label>
                <input type="text" id="editRegisterLocation" name="location" class="form-control">
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editRegisterModal')">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="deleteRegister()" style="margin-right: auto;"><i class="fa-solid fa-trash"></i> Eliminar</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Open Session Modal -->
<div class="modal-overlay" id="openSessionModal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-lock-open"></i> Abrir Sesión</h3>
            <button class="modal-close" onclick="closeModal('openSessionModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="openSessionForm" onsubmit="event.preventDefault(); submitOpenSession();">
            <input type="hidden" id="openSessionRegisterId">
            <div class="form-group">
                <label class="form-label">Caja</label>
                <input type="text" id="openSessionRegisterLabel" class="form-control" readonly style="background: var(--background); font-weight: 700;">
            </div>
            <div class="form-group">
                <label class="form-label">Cajero <span class="text-danger">*</span></label>
                <select id="openSessionUserId" name="user_id" class="form-control" required>
                    <option value="">Seleccionar cajero...</option>
                    @php $users = \App\Models\User::orderBy('username')->get(); @endphp
                    @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->username }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Fondo Inicial <span class="text-danger">*</span></label>
                <input type="number" id="openSessionAmount" name="opening_amount" class="form-control" step="0.01" min="0" value="0.00" required>
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('openSessionModal')">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-play"></i> Abrir Sesión</button>
            </div>
        </form>
    </div>
</div>

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
    // === ADD REGISTER ===
    function submitAddRegister() {
        const form = document.getElementById('addRegisterForm');
        submitAjaxForm(form, '{{ route("pos-control.registers.store") }}', () => {
            closeModal('addRegisterModal');
            window.location.reload();
        });
    }

    // === EDIT REGISTER ===
    function openEditRegisterModal(id, number, name, location) {
        document.getElementById('editRegisterId').value = id;
        document.getElementById('editRegisterNumber').value = number;
        document.getElementById('editRegisterName').value = name || '';
        document.getElementById('editRegisterLocation').value = location || '';
        openModal('editRegisterModal');
    }

    function submitEditRegister() {
        const id = document.getElementById('editRegisterId').value;
        const form = document.getElementById('editRegisterForm');
        const formData = new FormData(form);
        formData.append('_method', 'PUT');

        submitAjaxForm(form, `/capycontrol/public/pos-control/registers/${id}`, () => {
            closeModal('editRegisterModal');
            window.location.reload();
        });
    }

    function deleteRegister() {
        const id = document.getElementById('editRegisterId').value;
        deleteAjax(`/capycontrol/public/pos-control/registers/${id}`, () => {
            closeModal('editRegisterModal');
            window.location.reload();
        });
    }

    // === OPEN SESSION ===
    function openNewSessionModal(registerId, registerNumber) {
        document.getElementById('openSessionRegisterId').value = registerId;
        document.getElementById('openSessionRegisterLabel').value = 'Caja ' + registerNumber;
        document.getElementById('openSessionAmount').value = '0.00';
        openModal('openSessionModal');
    }

    function submitOpenSession() {
        showGlobalLoader();
        const data = {
            cash_register_id: document.getElementById('openSessionRegisterId').value,
            user_id: document.getElementById('openSessionUserId').value,
            opening_amount: document.getElementById('openSessionAmount').value,
        };

        fetch('{{ route("pos-control.sessions.open") }}', {
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
                closeModal('openSessionModal');
                window.location.reload();
            } else {
                alert(data.message || 'Error al abrir sesión');
            }
        })
        .catch(() => alert('Error de conexión'))
        .finally(() => hideGlobalLoader());
    }

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
                alert(data.message || 'Error al cerrar sesión');
            }
        })
        .catch(() => alert('Error de conexión'))
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
