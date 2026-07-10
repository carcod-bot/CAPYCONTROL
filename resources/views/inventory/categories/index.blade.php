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
            <form id="createCategoryForm" action="{{ route('categories.store') }}" method="POST" onsubmit="event.preventDefault(); submitAjaxForm(this, this.action, () => window.location.reload())">
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
        <tbody>
            @foreach($categories as $category)
            <tr id="row-{{ $category->id }}">
                <td>{{ $category->id }}</td>
                <td style="font-weight:600;">{{ $category->name }}</td>
                <td>
                    <span class="badge" style="background:var(--primary-light); color:var(--primary);">
                        {{ $category->department->name ?? 'Independiente' }}
                    </span>
                </td>
                <td class="text-muted">{{ $category->description }}</td>
                <td>
                    <button class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;" onclick="deleteAjax('{{ route('categories.destroy', $category) }}', () => document.getElementById('row-{{ $category->id }}').remove())">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $categories->links('pagination::bootstrap-4') }}</div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            dropdownParent: $('#createCategoryModal'),
            width: 'resolve'
        });
    });
</script>
@endpush
