@extends('layouts.app')

@section('title', 'Gestión de Clientes')

@push('styles')
<style>
    .customers-layout {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    
    .toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--surface);
        padding: 1rem 1.5rem;
        border-radius: 12px;
        box-shadow: var(--shadow-sm);
    }
    
    .search-bar {
        display: flex;
        gap: 0.5rem;
        flex: 1;
        max-width: 400px;
    }
    
    .table-container {
        background: var(--surface);
        border-radius: 16px;
        box-shadow: var(--shadow-md);
        overflow: hidden;
    }
    
    .table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .table th, .table td {
        padding: 1rem 1.5rem;
        text-align: left;
        border-bottom: 1px solid var(--border);
    }
    
    .table th {
        background: var(--background);
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }
    
    .table tbody tr:hover {
        background: var(--background);
    }
    
    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    .status-active { background: rgba(16, 185, 129, 0.15); color: #10b981; }
    .status-suspended { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
    
    .actions-cell {
        display: flex;
        gap: 0.5rem;
    }
    
    .btn-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-edit { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
    .btn-edit:hover { background: #3b82f6; color: white; }
    .btn-delete { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
    .btn-delete:hover { background: #ef4444; color: white; }
</style>
@endpush

@section('content')
<div class="customers-layout">
    <div class="toolbar">
        <form class="search-bar" action="{{ route('customers.index') }}" method="GET">
            <input type="text" name="q" class="form-control" placeholder="Buscar cliente por nombre o documento..." value="{{ request('q') }}">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i> Buscar</button>
            @if(request('q'))
                <a href="{{ route('customers.index') }}" class="btn btn-secondary" title="Limpiar"><i class="fa-solid fa-times"></i></a>
            @endif
        </form>
        <div>
            @if(Auth::user()->hasPermission('finances.edit'))
            <button class="btn btn-primary" onclick="openCreateModal()">
                <i class="fa-solid fa-plus"></i> Nuevo Cliente
            </button>
            @endif
        </div>
    </div>
    
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Documento</th>
                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>Límite de Crédito</th>
                    <th>Deuda Actual</th>
                    <th>Estado Crédito</th>
                    @if(Auth::user()->hasPermission('finances.edit'))
                    <th width="100px">Acciones</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $c)
                <tr>
                    <td>{{ $c->document_id }}</td>
                    <td style="font-weight: 600; color: var(--primary);">{{ $c->name }}</td>
                    <td>{{ $c->phone ?: '-' }}</td>
                    <td>${{ number_format($c->credit_limit, 2) }}</td>
                    <td style="color: {{ $c->current_balance > 0 ? 'var(--danger)' : 'inherit' }}; font-weight: {{ $c->current_balance > 0 ? '700' : 'normal' }}">
                        ${{ number_format($c->current_balance, 2) }}
                    </td>
                    <td>
                        <span class="status-badge {{ $c->credit_status === 'active' ? 'status-active' : 'status-suspended' }}">
                            {{ $c->credit_status === 'active' ? 'Activo' : 'Suspendido' }}
                        </span>
                    </td>
                    @if(Auth::user()->hasPermission('finances.edit'))
                    <td class="actions-cell">
                        <button class="btn-icon btn-edit" onclick='openEditModal(@json($c))' title="Editar"><i class="fa-solid fa-pen"></i></button>
                        <form action="{{ route('customers.destroy', $c) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este cliente?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-icon btn-delete" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                        <i class="fa-solid fa-users-slash fa-3x" style="margin-bottom: 1rem; opacity: 0.5;"></i>
                        <br>No se encontraron clientes.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div style="margin-top: 1rem;">
        {{ $customers->links('pagination::bootstrap-5') }}
    </div>
</div>

<!-- Modal Cliente -->
<div class="modal fade" id="customerModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content" style="background: var(--surface); color: var(--text-main);">
      <form id="customerForm" method="POST">
          @csrf
          <input type="hidden" name="_method" id="formMethod" value="POST">
          <div class="modal-header" style="border-bottom: 1px solid var(--border);">
            <h5 class="modal-title" id="modalTitle">Nuevo Cliente</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: var(--invert-close-icon);"></button>
          </div>
          <div class="modal-body">
              <div class="mb-3">
                  <label class="form-label">Documento / RIF</label>
                  <input type="text" name="document_id" id="document_id" class="form-control" required>
              </div>
              <div class="mb-3">
                  <label class="form-label">Nombre / Razón Social</label>
                  <input type="text" name="name" id="name" class="form-control" required>
              </div>
              <div class="row">
                  <div class="col-md-6 mb-3">
                      <label class="form-label">Teléfono</label>
                      <input type="text" name="phone" id="phone" class="form-control">
                  </div>
                  <div class="col-md-6 mb-3">
                      <label class="form-label">Correo Electrónico</label>
                      <input type="email" name="email" id="email" class="form-control">
                  </div>
              </div>
              <div class="mb-3">
                  <label class="form-label">Dirección</label>
                  <input type="text" name="address" id="address" class="form-control">
              </div>
              <hr style="border-color: var(--border);">
              <h6 style="color: var(--primary); font-weight: 700; margin-bottom: 1rem;">Configuración de Crédito</h6>
              <div class="row">
                  <div class="col-md-6 mb-3">
                      <label class="form-label">Límite de Crédito ($)</label>
                      <input type="number" step="0.01" name="credit_limit" id="credit_limit" class="form-control" value="0.00">
                  </div>
                  <div class="col-md-6 mb-3">
                      <label class="form-label">Estado de Crédito</label>
                      <select name="credit_status" id="credit_status" class="form-control">
                          <option value="active">Activo</option>
                          <option value="suspended">Suspendido</option>
                      </select>
                  </div>
              </div>
          </div>
          <div class="modal-footer" style="border-top: 1px solid var(--border);">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Guardar</button>
          </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
    const customerModal = new bootstrap.Modal(document.getElementById('customerModal'));
    const form = document.getElementById('customerForm');
    
    function openCreateModal() {
        document.getElementById('modalTitle').innerText = 'Nuevo Cliente';
        document.getElementById('formMethod').value = 'POST';
        form.action = "{{ route('customers.store') }}";
        form.reset();
        customerModal.show();
    }
    
    function openEditModal(customer) {
        document.getElementById('modalTitle').innerText = 'Editar Cliente';
        document.getElementById('formMethod').value = 'PUT';
        form.action = `/customers/${customer.id}`;
        
        document.getElementById('document_id').value = customer.document_id || '';
        document.getElementById('name').value = customer.name || '';
        document.getElementById('phone').value = customer.phone || '';
        document.getElementById('email').value = customer.email || '';
        document.getElementById('address').value = customer.address || '';
        document.getElementById('credit_limit').value = parseFloat(customer.credit_limit || 0).toFixed(2);
        document.getElementById('credit_status').value = customer.credit_status || 'active';
        
        customerModal.show();
    }
</script>
@endpush
