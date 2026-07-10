@extends('layouts.app')
@section('title', 'Marcas')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h1 class="page-title">Gestión de Marcas</h1>
</div>

<div class="card max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-4">
        <h2 style="font-size: 1.2rem; font-weight: 700;">Lista de Marcas</h2>
        <button class="btn btn-primary" onclick="openModal('createBrandModal')"><i class="fa-solid fa-plus"></i> Nueva Marca</button>
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
            <tbody>
                @foreach($brands as $brand)
                <tr id="row-{{ $brand->id }}">
                    <td style="font-weight: 600;">{{ $brand->name }}</td>
                    <td class="text-muted">{{ $brand->description ?: 'Sin descripción' }}</td>
                    <td id="td-status-{{ $brand->id }}">
                        <span class="badge" style="background: {{ $brand->active ? 'var(--primary-light)' : '#fee2e2' }}; color: {{ $brand->active ? 'var(--primary)' : '#991b1b' }};">
                            {{ $brand->active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-secondary" onclick="editBrand({{ $brand->id }}, '{{ $brand->name }}', '{{ $brand->description }}', {{ $brand->active }})" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;"><i class="fa-solid fa-edit"></i></button>
                        @if($brand->name !== 'Genérico')
                        <button class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;" onclick="deleteAjax('{{ route('brands.destroy', $brand) }}', () => document.getElementById('row-{{ $brand->id }}').remove())">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-4">{{ $brands->links('pagination::bootstrap-4') }}</div>
    </div>
</div>

<!-- Modal Crear -->
<div class="modal-overlay" id="createBrandModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Nueva Marca</h3>
            <button class="modal-close" onclick="closeModal('createBrandModal')"><i class="fa-solid fa-times"></i></button>
        </div>
        <form id="createBrandForm" action="{{ route('brands.store') }}" method="POST" onsubmit="event.preventDefault(); submitAjaxForm(this, this.action, () => window.location.reload())">
            @csrf
            <div class="form-group">
                <label class="form-label">Nombre de la Marca *</label>
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
            <button type="submit" class="btn btn-primary w-full"><i class="fa-solid fa-save"></i> Guardar Marca</button>
        </form>
    </div>
</div>

<!-- Modal Editar -->
<div class="modal-overlay" id="editBrandModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Editar Marca</h3>
            <button class="modal-close" onclick="closeModal('editBrandModal')"><i class="fa-solid fa-times"></i></button>
        </div>
        <form id="editBrandForm" method="POST" onsubmit="event.preventDefault(); submitAjaxForm(this, this.action, () => window.location.reload())">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="form-label">Nombre de la Marca *</label>
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
            <button type="submit" class="btn btn-primary w-full"><i class="fa-solid fa-save"></i> Actualizar Marca</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function editBrand(id, name, desc, active) {
        let form = document.getElementById('editBrandForm');
        form.action = '/products/brands/' + id; // It's actually resource /brands
        form.action = '{{ url('brands') }}/' + id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_description').value = desc;
        document.getElementById('edit_active').value = active;
        openModal('editBrandModal');
    }
</script>
@endpush
