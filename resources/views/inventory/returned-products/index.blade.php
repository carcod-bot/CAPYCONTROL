@extends('layouts.app')
@section('title', 'Productos Devueltos')

@section('content')
<div class="pos-action-bar">
    <div class="pos-action-bar-left">
        <h2 class="pos-section-title"><i class="fa-solid fa-arrow-rotate-left"></i> Productos Devueltos</h2>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4" style="padding: 1.5rem;">
    <form id="filtersForm" method="GET" action="{{ route('returned-products.index') }}" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 250px;">
            <label class="form-label">Buscar Producto o Ticket</label>
            <input type="text" name="search" class="form-control" placeholder="Nombre, código o N° Ticket..." value="{{ request('search') }}">
        </div>
        <div style="flex: 1; min-width: 200px;">
            <label class="form-label">Estado</label>
            <select name="status" class="form-control" style="width: 100%;">
                <option value="">Todos</option>
                <option value="pending_review" {{ request('status') == 'pending_review' ? 'selected' : '' }}>Pendiente de Revisión</option>
                <option value="restocked" {{ request('status') == 'restocked' ? 'selected' : '' }}>Reingresado a Stock</option>
                <option value="discarded" {{ request('status') == 'discarded' ? 'selected' : '' }}>Desechado</option>
            </select>
        </div>
        <div>
            <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-filter"></i> Filtrar</button>
            <a href="{{ route('returned-products.index') }}" class="btn btn-secondary">Limpiar</a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Ticket</th>
                    <th>Producto</th>
                    <th class="text-center">Cant.</th>
                    <th class="text-right">Monto</th>
                    <th>Motivo</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($returnedProducts as $rp)
                <tr>
                    <td>{{ $rp->created_at->format('d/m/Y H:i') }}</td>
                    <td class="font-bold">{{ $rp->sale ? $rp->sale->ticket_number : 'N/A' }}</td>
                    <td>
                        <strong>{{ $rp->product->name }}</strong><br>
                        <small class="text-muted">{{ $rp->product->private_code }}</small>
                    </td>
                    <td class="text-center">{{ number_format($rp->quantity_returned, 3) }}</td>
                    <td class="text-right">${{ number_format($rp->amount, 2) }}</td>
                    <td style="max-width: 250px; white-space: normal;">{{ $rp->reason }}</td>
                    <td class="text-center">
                        @if($rp->status == 'pending_review')
                            <span style="display: inline-block; background: var(--warning); color: #212529; padding: 4px 8px; border-radius: 6px; font-size: 0.85em; font-weight: 600; min-width: 80px; text-align: center;">Pendiente</span>
                        @elseif($rp->status == 'restocked')
                            <span style="display: inline-block; background: var(--success); color: #fff; padding: 4px 8px; border-radius: 6px; font-size: 0.85em; font-weight: 500; min-width: 80px; text-align: center;">Reingresado</span>
                        @elseif($rp->status == 'discarded')
                            <span style="display: inline-block; background: var(--danger); color: #fff; padding: 4px 8px; border-radius: 6px; font-size: 0.85em; font-weight: 500; min-width: 80px; text-align: center;">Desechado</span>
                        @else
                            <span style="display: inline-block; background: var(--text-muted); color: #fff; padding: 4px 8px; border-radius: 6px; font-size: 0.85em; font-weight: 500; min-width: 80px; text-align: center;">{{ $rp->status }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($rp->status == 'pending_review')
                        <div class="action-dropdown" id="dropdown-{{ $rp->id }}">
                            <button class="action-dropdown-toggle" onclick="toggleActionDropdown('dropdown-{{ $rp->id }}', event)">
                                Acciones <i class="fa-solid fa-chevron-down"></i>
                            </button>
                            <div class="action-dropdown-menu">
                                <button class="action-dropdown-item" style="color: var(--success);" onclick="processReturn({{ $rp->id }}, 'restocked')">
                                    <i class="fa-solid fa-check"></i> Reingresar a Stock
                                </button>
                                <button class="action-dropdown-item" style="color: var(--danger);" onclick="processReturn({{ $rp->id }}, 'discarded')">
                                    <i class="fa-solid fa-trash"></i> Desechar
                                </button>
                            </div>
                        </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 2rem;">
                        <i class="fa-solid fa-arrow-rotate-left fa-3x" style="color: var(--text-muted); margin-bottom: 1rem;"></i>
                        <p>No se encontraron productos devueltos.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        @if($returnedProducts->hasPages())
        <div style="padding: 1rem; border-top: 1px solid var(--border); display: flex; justify-content: center;">
            {{ $returnedProducts->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleActionDropdown(id, event) {
        event.stopPropagation();
        const el = document.getElementById(id);
        const wasOpen = el.classList.contains('open');
        
        document.querySelectorAll('.action-dropdown').forEach(d => d.classList.remove('open'));
        
        if (!wasOpen) el.classList.add('open');
    }

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.action-dropdown')) {
            document.querySelectorAll('.action-dropdown').forEach(d => d.classList.remove('open'));
        }
    });

    function processReturn(id, action) {
        let actionText = action === 'restocked' ? 'Reingresar el producto al stock' : 'Desechar el producto devuelto';
        let confirmBtnColor = action === 'restocked' ? '#28a745' : '#dc3545';
        let actionLabel = action === 'restocked' ? 'Reingresar' : 'Desechar';

        Swal.fire({
            title: '¿Estás seguro?',
            text: actionText,
            icon: 'warning',
            input: 'textarea',
            inputLabel: 'Nota adicional (Opcional)',
            inputPlaceholder: 'Escribe una nota para auditoría si lo deseas...',
            showCancelButton: true,
            confirmButtonColor: confirmBtnColor,
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, ' + actionLabel,
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const notes = result.value || '';
                
                fetch(`{{ url('inventory/returned-products') }}/${id}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        status: action,
                        notes: notes
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: data.message,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error', 'Ha ocurrido un error en la comunicación con el servidor.', 'error');
                });
            }
        });
    }
</script>
@endpush
