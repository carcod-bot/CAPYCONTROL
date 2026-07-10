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
            <tbody>
                @foreach($providers as $provider)
                <tr id="row-{{ $provider->id }}">
                    <td style="font-weight: 600;">{{ $provider->name }}</td>
                    <td class="text-muted">{{ $provider->description ?: 'Sin descripción' }}</td>
                    <td>
                        <span class="badge" style="background: {{ $provider->active ? 'var(--primary-light)' : '#fee2e2' }}; color: {{ $provider->active ? 'var(--primary)' : '#991b1b' }};">
                            {{ $provider->active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-secondary" onclick="editProv({{ $provider->id }}, '{{ $provider->name }}', '{{ $provider->description }}', {{ $provider->active }})" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;"><i class="fa-solid fa-edit"></i></button>
                        @if($provider->name !== 'Genérico')
                        <button class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;" onclick="deleteAjax('{{ route('providers.destroy', $provider) }}', () => document.getElementById('row-{{ $provider->id }}').remove())">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-4">{{ $providers->links('pagination::bootstrap-4') }}</div>
    </div>
</div>

<!-- Modal Crear -->
<div class="modal-overlay" id="createProvModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Nuevo Proveedor</h3>
            <button class="modal-close" onclick="closeModal('createProvModal')"><i class="fa-solid fa-times"></i></button>
        </div>
        <form id="createProvForm" action="{{ route('providers.store') }}" method="POST" onsubmit="event.preventDefault(); submitAjaxForm(this, this.action, () => window.location.reload())">
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
        <form id="editProvForm" method="POST" onsubmit="event.preventDefault(); submitAjaxForm(this, this.action, () => window.location.reload())">
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
    function editProv(id, name, desc, active) {
        let form = document.getElementById('editProvForm');
        form.action = '{{ url('providers') }}/' + id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_description').value = desc;
        document.getElementById('edit_active').value = active;
        openModal('editProvModal');
    }
</script>
@endpush
