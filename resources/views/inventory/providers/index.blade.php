@extends('layouts.app')
@section('title', 'Proveedores')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h1 class="page-title">Gestión de Proveedores</h1>
</div>

<div class="card max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-4">
        <h2 style="font-size: 1.2rem; font-weight: 700;">Lista de Proveedores</h2>
        <button class="btn btn-primary" onclick="openModal('createProvModal')"><i class="fa-solid fa-plus"></i> Nuevo Proveedor</button>
    </div>

    <div class="table-container" style="overflow-x: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="providers-tbody">
                <tr><td colspan="4" class="text-center text-muted">Cargando proveedores...</td></tr>
            </tbody>
        </table>
        <div id="providers-pagination" class="mt-4 flex justify-between items-center text-sm" style="display: flex; justify-content: space-between; align-items: center;"></div>
    </div>
</div>

<!-- Modal Crear -->
<div class="modal-overlay" id="createProvModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Nuevo Proveedor</h3>
            <button class="modal-close" onclick="closeModal('createProvModal')"><i class="fa-solid fa-times"></i></button>
        </div>
        <form id="createProvForm" action="{{ route('providers.store') }}" method="POST" onsubmit="event.preventDefault(); submitAjaxForm(this, this.action, () => { closeModal('createProvModal'); loadData(currentPage); })">
            @csrf
            <div class="form-group">
                <label class="form-label">Nombre del Proveedor *</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Descripción</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Estado</label>
                <select name="active" class="form-control">
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-full"><i class="fa-solid fa-save"></i> Guardar Proveedor</button>
        </form>
    </div>
</div>

<!-- Modal Editar -->
<div class="modal-overlay" id="editProvModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Editar Proveedor</h3>
            <button class="modal-close" onclick="closeModal('editProvModal')"><i class="fa-solid fa-times"></i></button>
        </div>
        <form id="editProvForm" method="POST" onsubmit="event.preventDefault(); submitAjaxForm(this, this.action, () => { closeModal('editProvModal'); loadData(currentPage); })">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="form-label">Nombre del Proveedor *</label>
                <input type="text" name="name" id="edit_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Descripción</label>
                <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Estado</label>
                <select name="active" id="edit_active" class="form-control">
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-full"><i class="fa-solid fa-save"></i> Actualizar Proveedor</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentPage = 1;

    async function loadData(page = 1) {
        currentPage = page;
        const tbody = document.getElementById('providers-tbody');
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Cargando proveedores...</td></tr>';
        
        try {
            const response = await fetch(`{{ route('providers.index') }}?page=${page}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();
            renderTable(data.data);
            renderPagination(data);
        } catch (error) {
            console.error('Error fetching providers:', error);
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error al cargar datos.</td></tr>';
        }
    }

    function renderTable(providers) {
        const tbody = document.getElementById('providers-tbody');
        tbody.innerHTML = '';
        
        if (providers.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No se encontraron proveedores.</td></tr>';
            return;
        }

        providers.forEach(provider => {
            const isActive = provider.active == 1 || provider.active === true;
            const statusBg = isActive ? 'var(--primary-light)' : '#fee2e2';
            const statusColor = isActive ? 'var(--primary)' : '#991b1b';
            const statusLabel = isActive ? 'Activo' : 'Inactivo';
            const desc = provider.description || 'Sin descripción';
            
            // Parse for safety
            const safeName = provider.name.replace(/'/g, "\\'");
            const safeDesc = desc.replace(/'/g, "\\'");
            
            let deleteBtn = '';
            if (provider.name !== 'Genérico') {
                deleteBtn = `<button class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; margin-left: 0.25rem;" onclick="deleteAjax('{{ url('providers') }}/${provider.id}', () => loadData(currentPage))"><i class="fa-solid fa-trash"></i></button>`;
            }

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="font-weight: 600;">${provider.name}</td>
                <td class="text-muted">${desc}</td>
                <td>
                    <span class="badge" style="background: ${statusBg}; color: ${statusColor};">
                        ${statusLabel}
                    </span>
                </td>
                <td>
                    <button class="btn btn-secondary" onclick="editProv(${provider.id}, '${safeName}', '${safeDesc}', ${provider.active})" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;"><i class="fa-solid fa-edit"></i></button>
                    ${deleteBtn}
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function renderPagination(data) {
        const paginationContainer = document.getElementById('providers-pagination');
        if (data.last_page <= 1) {
            paginationContainer.innerHTML = '';
            return;
        }

        let buttonsHTML = `<div class="pagination-info text-muted">Mostrando ${data.from || 0} a ${data.to || 0} de ${data.total} resultados</div>`;
        buttonsHTML += `<div style="display:flex; gap: 0.25rem;">`;
        
        // Prev
        if (data.current_page > 1) {
            buttonsHTML += `<button class="btn btn-secondary" style="padding: 0.25rem 0.5rem;" onclick="loadData(${data.current_page - 1})">&laquo; Ant</button>`;
        }
        
        // Next
        if (data.current_page < data.last_page) {
            buttonsHTML += `<button class="btn btn-secondary" style="padding: 0.25rem 0.5rem;" onclick="loadData(${data.current_page + 1})">Sig &raquo;</button>`;
        }
        
        buttonsHTML += `</div>`;
        paginationContainer.innerHTML = buttonsHTML;
    }

    function editProv(id, name, desc, active) {
        let form = document.getElementById('editProvForm');
        form.action = '{{ url('providers') }}/' + id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_description').value = desc === 'Sin descripción' ? '' : desc;
        document.getElementById('edit_active').value = active == 1 ? 1 : 0;
        openModal('editProvModal');
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadData(1);
    });
</script>
@endpush
