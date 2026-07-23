@extends('layouts.app')

@section('title', 'Niveles de Crédito')

@section('content')
<div class="d-flex justify-content-end align-items-center mb-4">
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
                <tbody id="levels-tbody">
                    <tr><td colspan="7" class="text-center text-muted">Cargando niveles...</td></tr>
                </tbody>
            </table>
        </div>
        <div id="levels-pagination" class="mt-4 flex justify-between items-center text-sm" style="display: flex; justify-content: space-between; align-items: center; padding: 0 1.5rem 1.5rem;"></div>
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
                    <select class="form-control select2" id="down_payment_type" name="down_payment_type" required>
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
                    <select class="form-control select2" id="payment_frequency" name="payment_frequency" required>
                        <option value="weekly">Semanal</option>
                        <option value="biweekly">Quincenal</option>
                        <option value="monthly">Mensual</option>
                    </select>
                </div>
            </div>

            <div style="width: 100%; display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1.5rem;">
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
            $('#down_payment_type').val(level.down_payment_type).trigger('change');
            document.getElementById('down_payment_value').value = level.down_payment_value;
            document.getElementById('installments_count').value = level.installments_count;
            $('#payment_frequency').val(level.payment_frequency).trigger('change');
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

    // Close on outside click
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.action-dropdown')) {
            document.querySelectorAll('.action-dropdown.open').forEach(d => {
                d.classList.remove('open');
            });
        }
    });

    $(document).ready(function() {
        $('#levelModal .select2').select2({
            dropdownParent: $('#levelModal'),
            width: '100%',
            minimumResultsForSearch: -1
        });
        loadData(1);
    });

    let currentPage = 1;

    async function loadData(page = 1) {
        currentPage = page;
        const tbody = document.getElementById('levels-tbody');
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Cargando niveles...</td></tr>';
        
        try {
            const response = await fetch(`{{ route('credit-levels.index') }}?page=${page}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();
            renderTable(data.data);
            renderPagination(data);
        } catch (error) {
            console.error('Error:', error);
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error al cargar datos.</td></tr>';
        }
    }

    function renderTable(levels) {
        const tbody = document.getElementById('levels-tbody');
        tbody.innerHTML = '';
        
        if (levels.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No hay niveles de crédito registrados.</td></tr>';
            return;
        }

        levels.forEach(level => {
            const downPayment = level.down_payment_type === 'percentage' 
                ? parseFloat(level.down_payment_value).toFixed(2) + '%' 
                : '$' + parseFloat(level.down_payment_value).toFixed(2);
                
            const freq = level.payment_frequency === 'weekly' ? 'Semanal' 
                     : level.payment_frequency === 'biweekly' ? 'Quincenal' 
                     : 'Mensual';

            const tr = document.createElement('tr');
            
            tr.innerHTML = `
                <td>${level.name}</td>
                <td>${level.required_purchases} compras</td>
                <td>${parseFloat(level.limit_increase_percentage).toFixed(0)}%</td>
                <td>${downPayment}</td>
                <td>${level.installments_count}</td>
                <td>${freq}</td>
                <td class="text-end">
                    <div class="action-dropdown">
                        <button class="action-dropdown-toggle dropdown-toggle-btn">
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                        </button>
                        <div class="action-dropdown-menu">
                            <button class="action-dropdown-item btn-edit text-warning">
                                <i class="fa-solid fa-pen" style="width: 20px;"></i> Editar
                            </button>
                            <button class="action-dropdown-item btn-delete text-danger">
                                <i class="fa-solid fa-trash" style="width: 20px;"></i> Eliminar
                            </button>
                        </div>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);

            // Bind events dynamically
            const dropdown = tr.querySelector('.action-dropdown');
            const btn = tr.querySelector('.dropdown-toggle-btn');

            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                
                // Close others
                document.querySelectorAll('.action-dropdown.open').forEach(d => {
                    if (d !== dropdown) d.classList.remove('open');
                });
                
                // Toggle current
                dropdown.classList.toggle('open');
            });

            tr.querySelector('.btn-edit').addEventListener('click', function() {
                dropdown.classList.remove('open'); 
                openLevelModal(level);
            });

            tr.querySelector('.btn-delete').addEventListener('click', function() {
                dropdown.classList.remove('open');
                deleteLevel(level.id);
            });
        });
    }

    function renderPagination(data) {
        const paginationContainer = document.getElementById('levels-pagination');
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
                });
                closeLevelModal();
                loadData(currentPage);
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
                        });
                        loadData(currentPage);
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
