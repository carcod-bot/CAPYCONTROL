@extends('layouts.app')
@section('title', 'Usuarios y Roles')

@push('styles')
<style>
    .tabs-container { margin-bottom: 2rem; }
    .tabs-header { display: flex; border-bottom: 2px solid var(--border); gap: 2rem; }
    .tab-btn { background: none; border: none; padding: 1rem 0; font-size: 1rem; font-weight: 700; color: var(--text-muted); cursor: pointer; position: relative; transition: var(--transition); }
    .tab-btn:hover { color: var(--text-main); }
    .tab-btn.active { color: var(--primary); }
    .tab-btn.active::after { content: ''; position: absolute; bottom: -2px; left: 0; width: 100%; height: 2px; background: var(--primary); border-radius: 2px 2px 0 0; }
    .tab-pane { display: none; padding-top: 1.5rem; }
    .tab-pane.active { display: block; }
    
    .permissions-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 2rem; }
    .permission-group { display: flex; flex-direction: column; gap: 0.75rem; }
    .permission-group-items { display: grid; grid-template-columns: 1fr 1fr; gap: 0.25rem 1rem; }
    .permission-group-title { font-size: 0.95rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; }
    .permission-group-title i { color: var(--primary); }
    
    .permission-item { display: flex; align-items: flex-start; gap: 10px; padding: 6px 10px; border-radius: 8px; transition: all 0.2s ease; cursor: pointer; margin-left: -10px; }
    .permission-item:hover { background-color: rgba(79, 70, 229, 0.05); }
    
    .toggle-switch { position: relative; display: inline-block; width: 34px; height: 18px; flex-shrink: 0; margin-top: 2px; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .3s ease; border-radius: 34px; }
    .toggle-slider:before { position: absolute; content: ""; height: 14px; width: 14px; left: 2px; bottom: 2px; background-color: white; transition: .3s ease; border-radius: 50%; box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
    .toggle-switch input:checked + .toggle-slider { background-color: var(--primary); }
    .toggle-switch input:checked + .toggle-slider:before { transform: translateX(16px); }
    
    .permission-label-text { font-size: 0.85rem; font-weight: 500; color: var(--text-main); user-select: none; line-height: 1.25; padding-top: 3px; }
    
    .badge-role { padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 700; background: #f1f5f9; color: #64748b; }
    .badge-role.system { background: #dcfce7; color: #16a34a; }
    body.dark-mode .badge-role { background: rgba(100,116,139,0.15); color: #94a3b8; }
    body.dark-mode .badge-role.system { background: rgba(22,163,74,0.15); color: #4ade80; }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="page-title"><i class="fa-solid fa-users" style="color:var(--primary); margin-right:10px;"></i> Usuarios y Roles</h1>
            <p class="text-muted mt-2">Gestiona el acceso, permisos y roles del sistema.</p>
        </div>
    </div>
</div>

<div class="content-wrapper">
    <div class="tabs-container">
        <div class="tabs-header">
            <button class="tab-btn active" onclick="switchTab('usuarios')"><i class="fa-solid fa-user"></i> Usuarios</button>
            <button class="tab-btn" onclick="switchTab('roles')"><i class="fa-solid fa-shield-halved"></i> Roles y Permisos</button>
        </div>

        <!-- TAB: USUARIOS -->
        <div id="tab-usuarios" class="tab-pane active">
            <div class="flex justify-between items-center mb-4">
                <h2 style="font-size: 1.25rem; font-weight: 700;">Lista de Usuarios</h2>
                <button class="btn btn-primary" onclick="openUserModal()">
                    <i class="fa-solid fa-plus"></i> Nuevo Usuario
                </button>
            </div>

            <div class="card" style="padding: 1.5rem;">
                <div class="table-container" style="margin-top: 0;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Rol Base</th>
                                <th>Permisos Extra</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $u)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-4">
                                        <div style="width:36px; height:36px; border-radius:10px; background:var(--primary-light); color:var(--primary); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:0.9rem;">
                                            {{ strtoupper(substr($u->username, 0, 2)) }}
                                        </div>
                                        <div>
                                            <strong style="font-size:1rem; color:var(--text-main);">{{ $u->username }}</strong>
                                            @if($u->isAdmin())
                                                <span class="badge-role system ml-2" style="margin-left: 8px;">ADMIN</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($u->roleModel)
                                        <span class="badge-role {{ $u->roleModel->is_system ? 'system' : '' }}">
                                            {{ $u->roleModel->name }}
                                        </span>
                                    @else
                                        <span class="text-muted" style="font-size: 0.85rem; font-style: italic;">Sin rol asignado</span>
                                    @endif
                                </td>
                                <td>
                                    @if(count($u->permissions ?? []) > 0)
                                        <span style="font-size:0.85rem; color:var(--primary); font-weight:700; background:var(--primary-light); padding:4px 8px; border-radius:8px;">+{{ count($u->permissions) }} extras</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="table-actions justify-center" style="justify-content: flex-end;">
                                        <button class="btn-icon btn-icon-secondary" onclick="editUser({{ $u->id }}, '{{ $u->username }}', {{ $u->role_id ?? 'null' }}, {{ json_encode($u->permissions ?? []) }})" title="Editar Usuario">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        @if($u->id !== Auth::id())
                                        <button class="btn-icon btn-icon-danger" onclick="deleteUser({{ $u->id }}, '{{ $u->username }}')" title="Eliminar Usuario">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB: ROLES -->
        <div id="tab-roles" class="tab-pane">
            <div class="flex justify-between items-center mb-4">
                <h2 style="font-size: 1.25rem; font-weight: 700;">Roles del Sistema</h2>
                <button class="btn btn-primary" onclick="openRoleModal()">
                    <i class="fa-solid fa-plus"></i> Nuevo Rol
                </button>
            </div>

            <div class="card" style="padding: 1.5rem;">
                <div class="table-container" style="margin-top: 0;">
                    <table class="table" id="roles-table">
                        <thead>
                            <tr>
                                <th>Rol</th>
                                <th>Descripción</th>
                                <th>Permisos Activos</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roles as $r)
                            <tr>
                                <td>
                                    <strong style="font-size:1rem; color:var(--text-main);">{{ $r->name }}</strong>
                                    @if($r->is_system)
                                        <span class="badge-role system ml-2" style="margin-left:8px;">SISTEMA</span>
                                    @endif
                                </td>
                                <td class="text-muted" style="font-size: 0.9rem;">{{ $r->description }}</td>
                                <td>
                                    <span style="font-size:0.85rem; font-weight:700; color:var(--primary); background:var(--primary-light); padding:4px 8px; border-radius:8px;">
                                        {{ $r->name === 'Administrador' ? count($allPermissions) : count($r->permissions ?? []) }} / {{ count($allPermissions) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="table-actions justify-center" style="justify-content: flex-end;">
                                        <button class="btn-icon btn-icon-secondary" onclick="editRole({{ $r->id }}, '{{ $r->name }}', '{{ $r->description }}', {{ json_encode($r->permissions ?? []) }}, {{ $r->is_system ? 'true' : 'false' }})" title="Editar Rol">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        @if(!$r->is_system)
                                        <button class="btn-icon btn-icon-danger" onclick="deleteRole({{ $r->id }}, '{{ $r->name }}')" title="Eliminar Rol">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL: USUARIO -->
<!-- ============================================== -->
<div id="userModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 950px;">
        <div class="modal-header">
            <h3 id="userModalTitle"><i class="fa-solid fa-user-plus" style="color:var(--primary); margin-right:8px;"></i> Nuevo Usuario</h3>
            <button type="button" class="modal-close" onclick="closeModal('userModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <form id="userForm">
                <input type="hidden" id="u_id">
                
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Nombre de Usuario *</label>
                        <input type="text" id="u_username" class="form-control" placeholder="Ej: admin_juan" required>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Rol Base</label>
                        <select id="u_role_id" class="form-control">
                            <option value="">-- Seleccionar Rol --</option>
                            @foreach($roles as $r)
                                <option value="{{ $r->id }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" id="u_pass_label">Contraseña *</label>
                        <input type="password" id="u_password" class="form-control" placeholder="••••••••">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Confirmar Contraseña</label>
                        <input type="password" id="u_password_confirmation" class="form-control" placeholder="••••••••">
                    </div>
                </div>

                <div style="background:var(--background); border:1px solid var(--border); border-radius:16px; padding:1.5rem;">
                    <h4 style="margin-bottom:0.5rem; font-size:1.1rem; font-weight:800; color:var(--text-main);"><i class="fa-solid fa-sliders" style="color:var(--primary); margin-right:8px;"></i> Permisos Adicionales</h4>
                    <p class="text-muted" style="font-size:0.85rem; margin-bottom:1.5rem;">
                        Marca permisos específicos para este usuario, independientemente de su Rol Base.
                    </p>
                    
                    @php
                        $grouped = collect($allPermissions)->groupBy(function($key) use ($permissionLabels) {
                            return $permissionLabels[$key]['group'];
                        });
                    @endphp

                    <div class="permissions-grid">
                        @foreach($grouped as $groupName => $keys)
                            <div class="permission-group">
                                <div class="permission-group-title">
                                    <i class="fa-solid fa-layer-group"></i> {{ $groupName }}
                                </div>
                                <div class="permission-group-items">
                                    @foreach($keys as $key)
                                        <label class="permission-item">
                                            <div class="toggle-switch">
                                                <input type="checkbox" name="u_perms[]" value="{{ $key }}">
                                                <span class="toggle-slider"></span>
                                            </div>
                                            <span class="permission-label-text">{{ $permissionLabels[$key]['label'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer" style="margin-top:2rem; display:flex; justify-content:flex-end; gap:1rem; border-top:1px solid var(--border); padding-top:1.5rem;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('userModal')">Cancelar</button>
            <button type="button" class="btn btn-primary" onclick="saveUser()"><i class="fa-solid fa-save"></i> Guardar Usuario</button>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL: ROL -->
<!-- ============================================== -->
<div id="roleModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 950px;">
        <div class="modal-header">
            <h3 id="roleModalTitle"><i class="fa-solid fa-shield-plus" style="color:var(--primary); margin-right:8px;"></i> Nuevo Rol</h3>
            <button type="button" class="modal-close" onclick="closeModal('roleModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <form id="roleForm">
                <input type="hidden" id="r_id">
                <input type="hidden" id="r_is_system">
                
                <div style="display:grid; grid-template-columns: 1fr 2fr; gap: 1.5rem; margin-bottom: 2rem;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Nombre del Rol *</label>
                        <input type="text" id="r_name" class="form-control" placeholder="Ej: Gerente" required>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Descripción</label>
                        <input type="text" id="r_description" class="form-control" placeholder="Breve explicación del rol...">
                    </div>
                </div>

                <div id="r_system_warning" class="alert alert-warning" style="display:none; background:#fff8e1; border:1px solid #ffe082; color:#b45309;">
                    <i class="fa-solid fa-triangle-exclamation"></i> Este es un rol del sistema. Sus permisos están predefinidos y no pueden modificarse.
                </div>

                <div id="r_permissions_container" style="background:var(--background); border:1px solid var(--border); border-radius:16px; padding:1.5rem;">
                    <h4 style="margin-bottom:1.5rem; font-size:1.1rem; font-weight:800; color:var(--text-main);"><i class="fa-solid fa-key" style="color:var(--primary); margin-right:8px;"></i> Permisos del Rol</h4>
                    <div class="permissions-grid">
                        @foreach($grouped as $groupName => $keys)
                            <div class="permission-group">
                                <div class="permission-group-title">
                                    <i class="fa-solid fa-layer-group"></i> {{ $groupName }}
                                </div>
                                <div class="permission-group-items">
                                    @foreach($keys as $key)
                                        <label class="permission-item">
                                            <div class="toggle-switch">
                                                <input type="checkbox" name="r_perms[]" value="{{ $key }}">
                                                <span class="toggle-slider"></span>
                                            </div>
                                            <span class="permission-label-text">{{ $permissionLabels[$key]['label'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer" style="margin-top:2rem; display:flex; justify-content:flex-end; gap:1rem; border-top:1px solid var(--border); padding-top:1.5rem;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('roleModal')">Cancelar</button>
            <button type="button" class="btn btn-primary" onclick="saveRole()"><i class="fa-solid fa-save"></i> Guardar Rol</button>
        </div>
    </div>
</div>

@push('scripts')
<!-- Incluir jQuery y Select2 si no están globalmente incluidos, aunque asumimos que App Layout ya los tiene -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar Select2 en los selects que lo requieran
        if ($.fn.select2) {
            $('#u_role_id').select2({
                width: '100%',
                placeholder: '-- Seleccionar Rol --',
                dropdownParent: $('#userModal')
            });
        }
    });

    function switchTab(tabId) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
        
        event.currentTarget.classList.add('active');
        document.getElementById('tab-' + tabId).classList.add('active');
    }

    // --- USERS LOGIC ---
    function openUserModal() {
        document.getElementById('userForm').reset();
        document.getElementById('u_id').value = '';
        document.getElementById('userModalTitle').innerHTML = '<i class="fa-solid fa-user-plus" style="color:var(--primary); margin-right:8px;"></i> Nuevo Usuario';
        document.getElementById('u_pass_label').innerText = 'Contraseña *';
        document.getElementById('u_password').required = true;
        
        if ($.fn.select2) {
            $('#u_role_id').val('').trigger('change');
        } else {
            document.getElementById('u_role_id').value = '';
        }

        document.getElementById('userModal').classList.add('open');
    }

    function editUser(id, username, role_id, perms) {
        document.getElementById('userForm').reset();
        document.getElementById('u_id').value = id;
        document.getElementById('u_username').value = username;
        
        if ($.fn.select2) {
            $('#u_role_id').val(role_id || '').trigger('change');
        } else {
            document.getElementById('u_role_id').value = role_id || '';
        }

        document.getElementById('userModalTitle').innerHTML = '<i class="fa-solid fa-user-pen" style="color:var(--primary); margin-right:8px;"></i> Editar Usuario';
        document.getElementById('u_pass_label').innerText = 'Contraseña (opcional)';
        document.getElementById('u_password').required = false;
        
        document.querySelectorAll('input[name="u_perms[]"]').forEach(cb => {
            cb.checked = perms.includes(cb.value);
        });
        
        document.getElementById('userModal').classList.add('open');
    }

    function saveUser() {
        const form = document.getElementById('userForm');
        
        // Validación HTML5 (Forzar required fields)
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const pass = document.getElementById('u_password').value;
        const conf = document.getElementById('u_password_confirmation').value;
        
        if (pass && pass !== conf) {
            Swal.fire('Atención', 'Las contraseñas no coinciden.', 'warning');
            return;
        }

        const id = document.getElementById('u_id').value;
        const method = id ? 'PUT' : 'POST';
        const url = id ? `{{ url('/configuraciones/usuarios') }}/${id}` : `{{ route('config.usuarios.store') }}`;
        
        const perms = Array.from(document.querySelectorAll('input[name="u_perms[]"]:checked')).map(cb => cb.value);

        const payload = {
            username: document.getElementById('u_username').value,
            role_id: document.getElementById('u_role_id').value || null,
            extra_permissions: perms,
        };

        if (pass || !id) {
            payload.password = pass;
            payload.password_confirmation = conf;
        }

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(payload)
        })
        .then(async res => {
            const text = await res.text();
            let data = null;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error("Respuesta no es JSON:", text);
                throw new Error("El servidor devolvió una respuesta no válida (HTML). Ver consola.");
            }
            
            if (!res.ok) {
                if (res.status === 422 && data && data.errors) {
                    let errs = Object.values(data.errors).map(e => e.join('\n')).join('\n');
                    throw new Error(errs);
                }
                throw new Error((data && data.message) || 'Error HTTP: ' + res.status);
            }
            return data;
        })
        .then(data => {
            if (data.success) {
                Swal.fire('¡Éxito!', data.message || 'Usuario guardado.', 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', data.message || 'Error al guardar', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Atención', err.message || 'Ocurrió un error inesperado.', 'warning');
        });
    }

    function deleteUser(id, name) {
        Swal.fire({
            title: `¿Eliminar al usuario ${name}?`,
            text: "Esta acción no se puede deshacer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ url('/configuraciones/usuarios') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Eliminado', data.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                }).catch(err => {
                    Swal.fire('Error', 'Ocurrió un error al eliminar.', 'error');
                });
            }
        });
    }

    // --- ROLES LOGIC ---
    function openRoleModal() {
        document.getElementById('roleForm').reset();
        document.getElementById('r_id').value = '';
        document.getElementById('r_is_system').value = 'false';
        document.getElementById('roleModalTitle').innerHTML = '<i class="fa-solid fa-shield-plus" style="color:var(--primary); margin-right:8px;"></i> Nuevo Rol';
        document.getElementById('r_name').readOnly = false;
        document.getElementById('r_system_warning').style.display = 'none';
        document.getElementById('r_permissions_container').style.opacity = '1';
        document.getElementById('r_permissions_container').style.pointerEvents = 'auto';
        document.getElementById('roleModal').classList.add('open');
    }

    function editRole(id, name, desc, perms, isSystem) {
        document.getElementById('roleForm').reset();
        document.getElementById('r_id').value = id;
        document.getElementById('r_name').value = name;
        document.getElementById('r_description').value = desc;
        document.getElementById('r_is_system').value = isSystem ? 'true' : 'false';
        document.getElementById('roleModalTitle').innerHTML = '<i class="fa-solid fa-shield-halved" style="color:var(--primary); margin-right:8px;"></i> Editar Rol';
        
        document.querySelectorAll('input[name="r_perms[]"]').forEach(cb => {
            if (isSystem && name === 'Administrador') {
                cb.checked = true;
            } else {
                cb.checked = perms.includes(cb.value);
            }
        });

        if (isSystem && name === 'Administrador') {
            document.getElementById('r_name').readOnly = true;
            document.getElementById('r_system_warning').style.display = 'block';
            document.getElementById('r_permissions_container').style.opacity = '0.5';
            document.getElementById('r_permissions_container').style.pointerEvents = 'none';
        } else {
            document.getElementById('r_name').readOnly = false;
            document.getElementById('r_system_warning').style.display = 'none';
            document.getElementById('r_permissions_container').style.opacity = '1';
            document.getElementById('r_permissions_container').style.pointerEvents = 'auto';
        }

        document.getElementById('roleModal').classList.add('open');
    }

    function saveRole() {
        const form = document.getElementById('roleForm');
        
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const id = document.getElementById('r_id').value;
        const method = id ? 'PUT' : 'POST';
        const url = id ? `{{ url('/api/roles') }}/${id}` : `{{ route('roles.store') }}`;
        
        const perms = Array.from(document.querySelectorAll('input[name="r_perms[]"]:checked')).map(cb => cb.value);

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                name: document.getElementById('r_name').value,
                description: document.getElementById('r_description').value,
                permissions: perms,
            })
        })
        .then(async res => {
            const text = await res.text();
            let data = null;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error("Respuesta no es JSON:", text);
                throw new Error("El servidor devolvió una respuesta no válida (HTML). Ver consola.");
            }
            
            if (!res.ok) {
                if (res.status === 422 && data && data.errors) {
                    let errs = Object.values(data.errors).map(e => e.join('\n')).join('\n');
                    throw new Error(errs);
                }
                throw new Error((data && data.message) || 'Error HTTP: ' + res.status);
            }
            return data;
        })
        .then(data => {
            if (data.success) {
                Swal.fire('¡Éxito!', data.message || 'Rol guardado.', 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', data.message || 'Error al guardar', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Atención', err.message || 'Ocurrió un error inesperado.', 'warning');
        });
    }

    function deleteRole(id, name) {
        Swal.fire({
            title: `¿Eliminar el rol ${name}?`,
            text: "Esta acción no se puede deshacer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ url('/api/roles') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(async res => {
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Error al eliminar');
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire('Eliminado', data.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                }).catch(err => {
                    Swal.fire('Error', err.message || 'Ocurrió un error al eliminar.', 'error');
                });
            }
        });
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('open');
    }
</script>
@endpush
@endsection
