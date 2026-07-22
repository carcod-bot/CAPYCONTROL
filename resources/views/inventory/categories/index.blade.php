@extends('layouts.app')
@section('title', 'Categorías')

@section('content')
<div class="card">
    <div class="flex justify-between items-center mb-4">
        <h3>Lista de Categorías</h3>
        <button class="btn btn-primary" onclick="openModal('createCategoryModal')">
            <i class="fa-solid fa-plus"></i> Nueva Categoría
        </button>
    </div>

    <!-- Modal for Create -->
    <div id="createCategoryModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Crear Categoría</h3>
                <button class="modal-close" onclick="closeModal('createCategoryModal')"><i class="fa-solid fa-times"></i></button>
            </div>
            <form id="createCategoryForm" action="{{ route('categories.store') }}" method="POST" onsubmit="event.preventDefault(); submitAjaxForm(this, this.action, () => { closeModal('createCategoryModal'); loadData(currentPage); })">
                @csrf
                <div class="form-group">
                    <label class="form-label">Departamento Base *</label>
                    <select name="department_id" class="form-control select2" style="width:100%;" required>
                        <option value="">Seleccione un departamento...</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Nombre de Categoría *</label>
                    <input type="text" name="name" class="form-control" required placeholder="Ej. Celulares">
                </div>
                <div class="form-group">
                    <label class="form-label">Descripción</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('createCategoryModal')">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <table class="table" id="categoriesTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Departamento</th>
                <th>Descripción</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="categories-tbody">
            <tr><td colspan="5" class="text-center text-muted">Cargando categorías...</td></tr>
        </tbody>
    </table>
    <div id="categories-pagination" class="mt-4 flex justify-between items-center text-sm" style="display: flex; justify-content: space-between; align-items: center;"></div>
</div>

<!-- Modal for Edit -->
<div id="editCategoryModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Editar Categoría</h3>
            <button class="modal-close" onclick="closeModal('editCategoryModal')"><i class="fa-solid fa-times"></i></button>
        </div>
        <form id="editCategoryForm" method="POST" onsubmit="event.preventDefault(); submitAjaxForm(this, this.action, () => { closeModal('editCategoryModal'); loadData(currentPage); })">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="form-label">Departamento Base *</label>
                <select name="department_id" id="edit_department_id" class="form-control select2" style="width:100%;" required>
                    <option value="">Seleccione un departamento...</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Nombre de Categoría *</label>
                <input type="text" name="name" id="edit_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Descripción</label>
                <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editCategoryModal')">Cancelar</button>
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
        const tbody = document.getElementById('categories-tbody');
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Cargando categorías...</td></tr>';
        
        try {
            const response = await fetch(`{{ route('categories.index') }}?page=${page}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();
            renderTable(data.data);
            renderPagination(data);
        } catch (error) {
            console.error('Error fetching categories:', error);
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error al cargar datos.</td></tr>';
        }
    }

    function renderTable(categories) {
        const tbody = document.getElementById('categories-tbody');
        tbody.innerHTML = '';
        
        if (categories.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No se encontraron categorías.</td></tr>';
            return;
        }

        categories.forEach(cat => {
            const desc = cat.description || '';
            const deptName = cat.department ? cat.department.name : 'Independiente';
            const deptId = cat.department ? cat.department.id : '';
            
            const safeName = cat.name.replace(/'/g, "\\'");
            const safeDesc = desc.replace(/'/g, "\\'");
            
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${cat.id}</td>
                <td style="font-weight:600;">${cat.name}</td>
                <td>
                    <span class="badge" style="background:var(--primary-light); color:var(--primary);">
                        ${deptName}
                    </span>
                </td>
                <td class="text-muted">${desc}</td>
                <td>
                    <button class="btn btn-secondary" onclick="editCategory(${cat.id}, '${safeName}', '${deptId}', '${safeDesc}')" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;"><i class="fa-solid fa-edit"></i></button>
                    <button class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; margin-left: 0.25rem;" onclick="deleteAjax('{{ url('categories') }}/${cat.id}', () => loadData(currentPage))"><i class="fa-solid fa-trash"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function renderPagination(data) {
        const paginationContainer = document.getElementById('categories-pagination');
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

    function editCategory(id, name, department_id, desc) {
        let form = document.getElementById('editCategoryForm');
        form.action = '{{ url('categories') }}/' + id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_description').value = desc;
        $('#edit_department_id').val(department_id).trigger('change');
        openModal('editCategoryModal');
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadData(1);
    });

    $(document).ready(function() {
        $('#createCategoryModal .select2').select2({
            dropdownParent: $('#createCategoryModal'),
            width: 'resolve'
        });
        $('#editCategoryModal .select2').select2({
            dropdownParent: $('#editCategoryModal'),
            width: 'resolve'
        });
    });
</script>
@endpush
