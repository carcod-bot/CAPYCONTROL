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
            <form id="createDepartmentForm" action="{{ route('departments.store') }}" method="POST" onsubmit="event.preventDefault(); submitAjaxForm(this, this.action, () => window.location.reload())">
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
        <tbody>
            @forelse($departments as $department)
            <tr id="row-{{ $department->id }}">
                <td>{{ $department->id }}</td>
                <td>{{ $department->name }}</td>
                <td>{{ $department->description }}</td>
                <td>{{ $department->created_at->format('d/m/Y') }}</td>
                <td>
                    <button class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;" onclick="deleteAjax('{{ route('departments.destroy', $department) }}', () => document.getElementById('row-{{ $department->id }}').remove())">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center text-muted">No hay departamentos registrados.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-4">{{ $departments->links('pagination::bootstrap-4') }}</div>
</div>
@endsection
