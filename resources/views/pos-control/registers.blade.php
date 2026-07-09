@extends('layouts.app')
@section('title', 'Gestión de Cajas')

@section('content')

<!-- Page Header -->
<div class="pos-action-bar">
    <div class="pos-action-bar-left">
        <h2 class="pos-section-title">
            <i class="fa-solid fa-server"></i> Gestión de Cajas
        </h2>
        <span style="margin-left: 1rem; font-size: 0.875rem; color: var(--text-muted);">
            Registra y administra los terminales POS de tu negocio
        </span>
    </div>
    <div class="pos-action-bar-right">
        <a href="{{ route('pos-control.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-desktop"></i> Monitoreo
        </a>
        <button class="btn btn-primary" onclick="openModal('addRegisterModal')">
            <i class="fa-solid fa-plus"></i> Nueva Caja
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
                    <th>Nombre</th>
                    <th>Ubicación</th>
                    <th>Hostname</th>
                    <th>IP del PC</th>
                    <th>Estado Sesión</th>
                    <th>Cajero Activo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($registers as $register)
                    @php $isOpen = $register->activeSession !== null; @endphp
                    <tr class="register-row {{ $isOpen ? 'row-open' : 'row-closed' }}" data-register-id="{{ $register->id }}">
                        <td><span class="register-number">{{ $register->number }}</span></td>
                        <td class="font-bold">{{ $register->name ?: '—' }}</td>
                        <td>{{ $register->location ?: '—' }}</td>
                        <td>
                            @if($register->hostname)
                                <span style="display:flex; align-items:center; gap:6px;">
                                    <i class="fa-solid fa-computer" style="color:var(--primary);"></i>
                                    <span style="font-weight:600;">{{ $register->hostname }}</span>
                                </span>
                            @else
                                <span class="text-muted" style="font-size:0.85rem;">Sin configurar</span>
                            @endif
                        </td>
                        <td>
                            @if($register->ip_address)
                                <span style="font-family:monospace; font-size:0.9rem; background:var(--background); padding:2px 8px; border-radius:4px; border:1px solid var(--border);">
                                    {{ $register->ip_address }}
                                </span>
                            @else
                                <span class="text-muted" style="font-size:0.85rem;">Sin configurar</span>
                            @endif
                        </td>
                        <td>
                            @if($isOpen)
                                <span class="badge badge-success">Abierta</span>
                            @else
                                <span class="badge badge-closed">Cerrada</span>
                            @endif
                        </td>
                        <td class="font-bold">
                            {{ $isOpen ? strtoupper($register->activeSession->user->username) : '—' }}
                        </td>
                        <td>
                            <div class="table-actions">
                                @if(!$isOpen)
                                    <button class="btn-icon btn-icon-success" title="Abrir Sesión"
                                        onclick="openNewSessionModal({{ $register->id }}, '{{ $register->number }}')">
                                        <i class="fa-solid fa-lock-open"></i>
                                    </button>
                                @endif
                                <button class="btn-icon btn-icon-info" title="Ver Historial"
                                    onclick="viewSessionHistory({{ $register->id }}, '{{ $register->number }}')">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                </button>
                                <button class="btn-icon btn-icon-secondary" title="Editar Caja"
                                    onclick="openEditRegisterModal(
                                        {{ $register->id }},
                                        '{{ $register->number }}',
                                        '{{ addslashes($register->name) }}',
                                        '{{ addslashes($register->location) }}',
                                        '{{ addslashes($register->hostname) }}',
                                        '{{ $register->ip_address }}'
                                    )">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted" style="padding: 3rem;">
                            <i class="fa-solid fa-cash-register" style="font-size: 2.5rem; margin-bottom: 0.75rem; display: block;"></i>
                            <strong style="display:block; font-size:1.1rem; margin-bottom:0.5rem;">No hay cajas registradas</strong>
                            Crea tu primera caja para comenzar a operar con CapyPOS.
                            <br><br>
                            <button class="btn btn-primary" onclick="openModal('addRegisterModal')" style="margin-top:0.5rem;">
                                <i class="fa-solid fa-plus"></i> Crear Primera Caja
                            </button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Info Card: How IP Control works -->
<div class="card" style="margin-top: 1.5rem; border-left: 4px solid var(--primary);">
    <div style="display:flex; align-items:flex-start; gap:1rem; padding: 0.25rem 0;">
        <i class="fa-solid fa-shield-halved" style="font-size:1.5rem; color:var(--primary); flex-shrink:0; margin-top:2px;"></i>
        <div>
            <strong style="display:block; margin-bottom:0.35rem;">Control de Acceso por IP</strong>
            <p style="color:var(--text-muted); font-size:0.875rem; margin:0; line-height:1.6;">
                Al registrar la <strong>IP del PC</strong> de cada caja, solo ese equipo podrá acceder a <strong>CapyPOS</strong> y operar con su sesión.
                Si un PC con una IP diferente intenta entrar, CapyPOS mostrará un mensaje de acceso denegado.
                Deja el campo IP vacío si no deseas restringir el acceso por IP para esa caja.
            </p>
        </div>
    </div>
</div>

<!-- ========== MODALS ========== -->

<!-- Add Register Modal -->
<div class="modal-overlay" id="addRegisterModal">
    <div class="modal-content" style="max-width: 560px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-plus"></i> Nueva Caja</h3>
            <button class="modal-close" onclick="closeModal('addRegisterModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="addRegisterForm" onsubmit="event.preventDefault(); submitAddRegister();">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group" style="grid-column: 1;">
                    <label class="form-label">Nº Caja <span class="text-danger">*</span></label>
                    <input type="text" name="number" class="form-control" placeholder="Ej: 001" required maxlength="10">
                </div>
                <div class="form-group" style="grid-column: 2;">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="name" class="form-control" placeholder="Ej: Caja Principal">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Ubicación</label>
                <input type="text" name="location" class="form-control" placeholder="Ej: Entrada principal">
            </div>
            <hr style="border-color: var(--border); margin: 1rem 0;">
            <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:1rem;">
                <i class="fa-solid fa-shield-halved" style="color:var(--primary);"></i>
                <strong> PC Asignado</strong> — Solo este equipo podrá acceder a CapyPOS con esta caja.
            </p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">
                        <i class="fa-solid fa-computer"></i> Hostname del PC
                    </label>
                    <input type="text" name="hostname" class="form-control" placeholder="Ej: CAJA-01 o PC-VENTAS">
                </div>
                <div class="form-group">
                    <label class="form-label">
                        <i class="fa-solid fa-network-wired"></i> IP del PC <span class="text-muted" style="font-size:0.8rem;">(en la red local)</span>
                    </label>
                    <input type="text" name="ip_address" class="form-control" placeholder="Ej: 192.168.1.100" pattern="^(\d{1,3}\.){3}\d{1,3}$">
                </div>
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addRegisterModal')">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Guardar Caja</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Register Modal -->
<div class="modal-overlay" id="editRegisterModal">
    <div class="modal-content" style="max-width: 560px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-pen"></i> Editar Caja</h3>
            <button class="modal-close" onclick="closeModal('editRegisterModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="editRegisterForm" onsubmit="event.preventDefault(); submitEditRegister();">
            <input type="hidden" id="editRegisterId">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Nº Caja <span class="text-danger">*</span></label>
                    <input type="text" id="editRegisterNumber" name="number" class="form-control" required maxlength="10">
                </div>
                <div class="form-group">
                    <label class="form-label">Nombre</label>
                    <input type="text" id="editRegisterName" name="name" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Ubicación</label>
                <input type="text" id="editRegisterLocation" name="location" class="form-control">
            </div>
            <hr style="border-color: var(--border); margin: 1rem 0;">
            <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:1rem;">
                <i class="fa-solid fa-shield-halved" style="color:var(--primary);"></i>
                <strong> PC Asignado</strong> — Solo este equipo podrá acceder a CapyPOS con esta caja.
            </p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">
                        <i class="fa-solid fa-computer"></i> Hostname del PC
                    </label>
                    <input type="text" id="editRegisterHostname" name="hostname" class="form-control" placeholder="Ej: CAJA-01">
                </div>
                <div class="form-group">
                    <label class="form-label">
                        <i class="fa-solid fa-network-wired"></i> IP del PC
                    </label>
                    <input type="text" id="editRegisterIp" name="ip_address" class="form-control" placeholder="Ej: 192.168.1.100" pattern="^(\d{1,3}\.){3}\d{1,3}$">
                </div>
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editRegisterModal')">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="deleteRegister()" style="margin-right: auto;">
                    <i class="fa-solid fa-trash"></i> Eliminar
                </button>
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

<!-- Session History Modal -->
<div class="modal-overlay" id="historyModal">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-clock-rotate-left"></i> Historial — Caja <span id="historyRegisterLabel"></span></h3>
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
    function openEditRegisterModal(id, number, name, location, hostname, ip) {
        document.getElementById('editRegisterId').value = id;
        document.getElementById('editRegisterNumber').value = number;
        document.getElementById('editRegisterName').value = name || '';
        document.getElementById('editRegisterLocation').value = location || '';
        document.getElementById('editRegisterHostname').value = hostname || '';
        document.getElementById('editRegisterIp').value = ip || '';
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
