@extends('layouts.app')

@section('title', 'Niveles de Crédito')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Niveles de Crédito</h2>
    <button class="btn btn-primary" onclick="openLevelModal()">
        <i class="fa-solid fa-plus"></i> Nuevo Nivel
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Compras Req.</th>
                        <th>Aumento Límite</th>
                        <th>Inicial</th>
                        <th>Cuotas</th>
                        <th>Frecuencia</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($levels as $level)
                    <tr>
                        <td>{{ $level->name }}</td>
                        <td>{{ $level->required_purchases }} compras</td>
                        <td>{{ number_format($level->limit_increase_percentage, 0) }}%</td>
                        <td>
                            @if($level->down_payment_type === 'percentage')
                                {{ number_format($level->down_payment_value, 2) }}%
                            @else
                                ${{ number_format($level->down_payment_value, 2) }}
                            @endif
                        </td>
                        <td>{{ $level->installments_count }}</td>
                        <td>
                            @if($level->payment_frequency === 'weekly')
                                Semanal
                            @elseif($level->payment_frequency === 'biweekly')
                                Quincenal
                            @else
                                Mensual
                            @endif
                        </td>
                        <td class="text-end">
                            <button class="btn-icon text-warning" onclick='openLevelModal(@json($level))' title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button class="btn-icon text-danger" onclick="deleteLevel({{ $level->id }})" title="Eliminar">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No hay niveles de crédito registrados.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Nivel -->
<div class="modal-overlay" id="levelModal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 id="modalTitle" style="font-weight: 700; color: var(--primary);"><i class="fa-solid fa-layer-group"></i> Nuevo Nivel</h3>
            <button type="button" class="modal-close" onclick="closeLevelModal()"><i class="fa-solid fa-times"></i></button>
        </div>
        <form id="levelForm" onsubmit="saveLevel(event)">
            @csrf
            <input type="hidden" id="level_id" name="id">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Nombre del Nivel</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Compras Requeridas</label>
                    <input type="number" class="form-control" id="required_purchases" name="required_purchases" min="0" value="0" required>
                    <small class="text-muted">Cantidad de compras para alcanzar el nivel.</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Tipo de Inicial</label>
                    <select class="form-control" id="down_payment_type" name="down_payment_type" required>
                        <option value="percentage">Porcentaje (%)</option>
                        <option value="fixed">Monto Fijo ($)</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Valor de Inicial</label>
                    <input type="number" step="0.01" class="form-control" id="down_payment_value" name="down_payment_value" min="0" value="0" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Aumento de Límite (%)</label>
                    <input type="number" step="0.01" class="form-control" id="limit_increase_percentage" name="limit_increase_percentage" min="0" value="0" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Cantidad de Cuotas</label>
                    <input type="number" class="form-control" id="installments_count" name="installments_count" min="1" value="1" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Frecuencia de Pago</label>
                    <select class="form-control" id="payment_frequency" name="payment_frequency" required>
                        <option value="weekly">Semanal</option>
                        <option value="biweekly">Quincenal</option>
                        <option value="monthly">Mensual</option>
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="button" class="btn btn-secondary" onclick="closeLevelModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Guardar Nivel</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openLevelModal(level = null) {
        document.getElementById('levelForm').reset();
        
        if(level) {
            document.getElementById('modalTitle').innerText = 'Editar Nivel';
            document.getElementById('level_id').value = level.id;
            document.getElementById('name').value = level.name;
            document.getElementById('required_purchases').value = level.required_purchases;
            document.getElementById('down_payment_type').value = level.down_payment_type;
            document.getElementById('down_payment_value').value = level.down_payment_value;
            document.getElementById('installments_count').value = level.installments_count;
            document.getElementById('payment_frequency').value = level.payment_frequency;
            document.getElementById('limit_increase_percentage').value = level.limit_increase_percentage;
        } else {
            document.getElementById('modalTitle').innerText = 'Nuevo Nivel';
            document.getElementById('level_id').value = '';
        }
        
        openModal('levelModal');
    }

    function closeLevelModal() {
        closeModal('levelModal');
    }

    async function saveLevel(e) {
        e.preventDefault();
        
        let formData = new FormData(document.getElementById('levelForm'));
        let id = document.getElementById('level_id').value;
        let url = id ? `{{ url('finances/credit-levels') }}/${id}` : `{{ url('finances/credit-levels') }}`;
        
        if (id) {
            formData.append('_method', 'PUT');
        }

        try {
            let res = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            let data = await res.json();
            
            if(data.success) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: data.message,
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                }).then(() => window.location.reload());
            } else {
                Swal.fire('Error', data.message || 'Ocurrió un error', 'error');
            }
        } catch (error) {
            console.error(error);
            Swal.fire('Error', 'No se pudo guardar el nivel', 'error');
        }
    }

    function deleteLevel(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "No podrás revertir esto.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    let res = await fetch(`{{ url('finances/credit-levels') }}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    let data = await res.json();
                    
                    if(data.success) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: data.message,
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        }).then(() => window.location.reload());
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                } catch (error) {
                    Swal.fire('Error', 'No se pudo eliminar el nivel', 'error');
                }
            }
        });
    }
</script>
@endpush
