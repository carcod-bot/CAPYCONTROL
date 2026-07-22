@extends('layouts.app')
@section('title', 'Departamentos')

@section('content')
<div class="card">
    <div class="flex justify-between items-center mb-4">
        <h3>Lista de Departamentos</h3>
        <button class="btn btn-primary" onclick="openModal('createDepartmentModal')">
            <i class="fa-solid fa-plus"></i> Nuevo Departamento
        </button>
    </div>

    <!-- Modal for Create -->
    <div id="createDepartmentModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Crear Departamento</h3>
                <button class="modal-close" onclick="closeModal('createDepartmentModal')"><i class="fa-solid fa-times"></i></button>
            </div>
            <form id="createDepartmentForm" action="{{ route('departments.store') }}" method="POST" onsubmit="event.preventDefault(); submitAjaxForm(this, this.action, () => { closeModal('createDepartmentModal'); loadData(currentPage); })">
                @csrf
                <div class="form-group">
                    <label class="form-label">Nombre del Departamento</label>
                    <input type="text" name="name" class="form-control" required placeholder="Ej. Lácteos">
                </div>
                <div class="form-group">
                    <label class="form-label">Descripción</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('createDepartmentModal')">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <table class="table" id="departmentsTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Fecha Creación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="departments-tbody">
            <tr><td colspan="5" class="text-center text-muted">Cargando departamentos...</td></tr>
        </tbody>
    </table>
    <div id="departments-pagination" class="mt-4 flex justify-between items-center text-sm" style="display: flex; justify-content: space-between; align-items: center;"></div>
</div>

<!-- Modal for Edit -->
<div id="editDepartmentModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Editar Departamento</h3>
            <button class="modal-close" onclick="closeModal('editDepartmentModal')"><i class="fa-solid fa-times"></i></button>
        </div>
        <form id="editDepartmentForm" method="POST" onsubmit="event.preventDefault(); submitAjaxForm(this, this.action, () => { closeModal('editDepartmentModal'); loadData(currentPage); })">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="form-label">Nombre del Departamento</label>
                <input type="text" name="name" id="edit_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Descripción</label>
                <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editDepartmentModal')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentPage = 1;

    async function loadData(page = 1) {
        currentPage = page;
        const tbody = document.getElementById('departments-tbody');
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Cargando departamentos...</td></tr>';
        
        try {
            const response = await fetch(`{{ route('departments.index') }}?page=${page}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();
            renderTable(data.data);
            renderPagination(data);
        } catch (error) {
            console.error('Error fetching departments:', error);
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error al cargar datos.</td></tr>';
        }
    }

    function renderTable(departments) {
        const tbody = document.getElementById('departments-tbody');
        tbody.innerHTML = '';
        
        if (departments.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No se encontraron departamentos.</td></tr>';
            return;
        }

        departments.forEach(dept => {
            const desc = dept.description || '';
            const safeName = dept.name.replace(/'/g, "\\'");
            const safeDesc = desc.replace(/'/g, "\\'");
            
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${dept.id}</td>
                <td style="font-weight: 600;">${dept.name}</td>
                <td>${desc}</td>
                <td>${new Date(dept.created_at).toLocaleDateString()}</td>
                <td>
                    <button class="btn btn-secondary" onclick="editDepartment(${dept.id}, '${safeName}', '${safeDesc}')" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;"><i class="fa-solid fa-edit"></i></button>
                    <button class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; margin-left: 0.25rem;" onclick="deleteAjax('{{ url('departments') }}/${dept.id}', () => loadData(currentPage))"><i class="fa-solid fa-trash"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function renderPagination(data) {
        const paginationContainer = document.getElementById('departments-pagination');
        if (data.last_page <= 1) {
            paginationContainer.innerHTML = '';
            return;
        }

        let buttonsHTML = `<div class="pagination-info text-muted">Mostrando ${data.from || 0} a ${data.to || 0} de ${data.total} resultados</div>`;
        buttonsHTML += `<div style="display:flex; gap: 0.25rem;">`;
        
        if (data.current_page > 1) {
            buttonsHTML += `<button class="btn btn-secondary" style="padding: 0.25rem 0.5rem;" onclick="loadData(${data.current_page - 1})">&laquo; Ant</button>`;
        }
        
        if (data.current_page < data.last_page) {
            buttonsHTML += `<button class="btn btn-secondary" style="padding: 0.25rem 0.5rem;" onclick="loadData(${data.current_page + 1})">Sig &raquo;</button>`;
        }
        
        buttonsHTML += `</div>`;
        paginationContainer.innerHTML = buttonsHTML;
    }

    function editDepartment(id, name, desc) {
        let form = document.getElementById('editDepartmentForm');
        form.action = '{{ url('departments') }}/' + id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_description').value = desc;
        openModal('editDepartmentModal');
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadData(1);
    });
</script>
