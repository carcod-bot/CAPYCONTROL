@extends('layouts.app')
@section('title', 'Ajustes y Conteo Físico')

@section('content')
<div class="pos-action-bar">
    <div class="pos-action-bar-left">
        <h2 class="pos-section-title"><i class="fa-solid fa-scale-balanced"></i> Ajustes de Inventario</h2>
    </div>
    <div class="pos-action-bar-right">
        <button class="btn btn-primary" onclick="openAdjustmentModal()">
            <i class="fa-solid fa-plus"></i> Nuevo Ajuste / Conteo
        </button>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4" style="padding: 1.5rem;">
    <form action="{{ route('inventory-adjustments.index') }}" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 250px;">
            <label class="form-label">Buscar Producto</label>
            <input type="text" name="search" class="form-control" placeholder="Nombre o código..." value="{{ request('search') }}">
        </div>
        <div style="flex: 1; min-width: 200px;">
            <label class="form-label">Número de Lote</label>
            <input type="text" name="batch" class="form-control" placeholder="Buscar lote..." value="{{ request('batch') }}">
        </div>
        <div style="flex: 1; min-width: 200px;">
            <label class="form-label">Tipo de Ajuste</label>
            <select name="type" class="form-control select2-filter" style="width: 100%;">
                <option value="">Todos</option>
                <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>Entrada (Suma)</option>
                <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>Salida (Resta)</option>
                <option value="set" {{ request('type') == 'set' ? 'selected' : '' }}>Conteo Físico (Remplaza)</option>
                <option value="finished_batches" {{ request('type') == 'finished_batches' ? 'selected' : '' }}>Lotes Terminados</option>
                <option value="stock" {{ request('type') == 'stock' ? 'selected' : '' }}>Stock (Agrupado por producto)</option>
            </select>
        </div>
        <div>
            <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-filter"></i> Filtrar</button>
            <a href="{{ route('inventory-adjustments.index') }}" class="btn btn-secondary">Limpiar</a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-container" style="border: none; margin: 0; box-shadow: none;">
        @if($isStockView)
            <table class="table">
                <thead>
                    <tr>
                        <th>Código Privado</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Marca</th>
                        <th class="text-center">Stock Actual</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stockProducts as $prod)
                    <tr>
                        <td class="font-bold" style="font-family: monospace;">{{ $prod->private_code }}</td>
                        <td class="font-bold">{{ $prod->name }}</td>
                        <td>{{ $prod->category ? $prod->category->name : 'N/A' }}</td>
                        <td>{{ $prod->brand ? $prod->brand->name : 'N/A' }}</td>
                        <td class="font-bold text-center {{ $prod->stock <= 0 ? 'text-danger' : 'text-success' }}">{{ number_format($prod->stock, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted" style="padding: 3rem;">
                            <i class="fa-solid fa-box-open" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
                            No se encontraron productos.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <!-- Paginación -->
            @if($stockProducts->hasPages())
                <div style="padding: 1rem; border-top: 1px solid var(--border);">
                    {{ $stockProducts->links('pagination::bootstrap-4') }}
                </div>
            @endif
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Tipo</th>
                        <th>Lote</th>
                        <th>Cant.</th>
                        <th>Stock Anterior</th>
                        <th>Nuevo Stock</th>
                        <th>Motivo</th>
                        <th>Usuario</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adjustments as $adj)
                    <tr style="cursor: pointer;" onclick="loadLifecycle({{ $adj->id }})" class="hover-bg">
                        <td>{{ $adj->created_at->format('d/m/Y h:i a') }}</td>
                        <td>
                            <div class="font-bold">{{ $adj->product->name }}</div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $adj->product->private_code }}</div>
                        </td>
                        <td>
                            @if($adj->type === 'in')
                                <span class="badge badge-success" style="display: inline-flex; align-items: center; gap: 0.25rem; white-space: nowrap;"><i class="fa-solid fa-arrow-down"></i> Entrada</span>
                            @elseif($adj->type === 'out')
                                <span class="badge" style="background:#fee2e2; color:#991b1b; border:1px solid #fecaca; display: inline-flex; align-items: center; gap: 0.25rem; white-space: nowrap;"><i class="fa-solid fa-arrow-up"></i> Salida</span>
                            @else
                                <span class="badge" style="background:#dbeafe; color:#1e40af; border:1px solid #bfdbfe; display: inline-flex; align-items: center; gap: 0.25rem; white-space: nowrap;"><i class="fa-solid fa-check-double"></i> Conteo</span>
                            @endif
                        </td>
                        <td>
                            @if($adj->batches->count() > 0)
                                @foreach($adj->batches->unique('batch_number') as $batch)
                                    @php
                                        $statusBadge = '';
                                        if($batch->expiration_date) {
                                            $exp = \Carbon\Carbon::parse($batch->expiration_date)->startOfDay();
                                            $today = \Carbon\Carbon::now()->startOfDay();
                                            $days = $today->diffInDays($exp, false);
                                            
                                            if($days < 0) {
                                                $statusBadge = '<span class="badge" style="background:#fee2e2; color:#991b1b; font-size: 0.65rem; padding: 2px 5px; margin-left: 5px; border-radius: 4px;">Vencido</span>';
                                            } elseif($days <= 30) {
                                                $statusBadge = '<span class="badge" style="background:#fef08a; color:#854d0e; font-size: 0.65rem; padding: 2px 5px; margin-left: 5px; border-radius: 4px;">Por Vencer</span>';
                                            } else {
                                                $statusBadge = '<span class="badge" style="background:#dcfce7; color:#166534; font-size: 0.65rem; padding: 2px 5px; margin-left: 5px; border-radius: 4px;">Vigente</span>';
                                            }
                                        }
                                    @endphp
                                    <div style="margin-bottom: 3px;">
                                        <span style="font-family: monospace; font-size: 0.85rem;" class="text-primary">{{ $batch->batch_number }}</span>
                                        {!! $statusBadge !!}
                                    </div>
                                @endforeach
                            @else
                                <span class="text-muted" style="font-size: 0.8rem;">-</span>
                            @endif
                        </td>
                        <td class="font-bold text-center">{{ number_format($adj->quantity, 2) }}</td>
                        <td class="text-center text-muted">{{ number_format($adj->previous_stock, 2) }}</td>
                        <td class="font-bold text-center">{{ number_format($adj->new_stock, 2) }}</td>
                        <td>{{ $adj->reason }}</td>
                        <td>{{ $adj->user->username }}</td>
                        <td onclick="event.stopPropagation()">
                            @if($adj->type !== 'out')
                                <button class="btn btn-secondary btn-sm" onclick="editAdjustmentBatches({{ $adj->id }})" title="Editar Lote(s)">
                                    <i class="fa-solid fa-pen"></i> Editar
                                </button>
                            @else
                                <span class="text-muted" style="font-size: 0.8rem;">No Editable</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted" style="padding: 3rem;">
                            <i class="fa-solid fa-scale-balanced" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
                            No se encontraron registros de ajustes.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <!-- Paginación -->
            @if($adjustments->hasPages())
                <div style="padding: 1rem; border-top: 1px solid var(--border);">
                    {{ $adjustments->links('pagination::bootstrap-4') }}
                </div>
            @endif
        @endif
    </div>
</div>

<!-- Modal Adjustment -->
<div class="modal-overlay" id="adjustmentModal">
    <div class="modal-content" style="max-width: 1100px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-scale-balanced"></i> Registrar Ajustes Multi-Producto</h3>
            <button type="button" class="modal-close" onclick="closeModal('adjustmentModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="adjustmentForm" onsubmit="event.preventDefault(); submitAdjustment();">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Tipo de Movimiento Global <span class="text-danger">*</span></label>
                    <select name="type" id="adjType" class="form-control" required style="width: 100%;">
                        <option value="in">Entrada (Sumar al stock)</option>
                        <option value="out">Salida (Restar al stock)</option>
                        <option value="set">Conteo Físico (Reemplazar stock)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Motivo Global <span class="text-danger">*</span></label>
                    <input type="text" name="reason" class="form-control" placeholder="Ej: Compra, Merma, Daño, Conteo Anual" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Notas Adicionales</label>
                <textarea name="notes" class="form-control" rows="1" placeholder="Observaciones opcionales..."></textarea>
            </div>

            <h4 style="font-size: 1rem; margin-top: 1.5rem; margin-bottom: 0.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                Productos a Ajustar
                <button type="button" class="btn btn-secondary btn-sm" onclick="addProductRow()" style="padding: 0.3rem 0.8rem; font-size: 0.85rem;">
                    <i class="fa-solid fa-plus"></i> Añadir Fila
                </button>
            </h4>

            <div class="table-container" style="border: none; box-shadow: none; overflow-x: auto; margin-bottom: 1rem;">
                <table class="table" id="productsTable" style="min-width: 700px; margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th style="width: 35%;">Producto</th>
                            <th style="width: 15%;">Cantidad</th>
                            <th style="width: 20%;" class="batch-col">Nro Lote</th>
                            <th style="width: 20%;" class="batch-col">Vencimiento</th>
                            <th style="width: 10%;"></th>
                        </tr>
                    </thead>
                    <tbody id="productsBody">
                        <!-- Dynamic Rows -->
                    </tbody>
                </table>
            </div>

            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('adjustmentModal')">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Procesar Ajustes</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Detalles de Lote (Ciclo de Vida) -->
<div class="modal-overlay" id="batchDetailsModal">
    <div class="modal-content" style="max-width: 900px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-route"></i> Trazabilidad del Lote</h3>
            <button class="modal-close" onclick="closeModal('batchDetailsModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="table-container" style="border: none; margin: 0; box-shadow: none; overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Lote</th>
                        <th>Vencimiento</th>
                        <th class="text-center" title="Cantidad Inicial Ingresada">Inicial</th>
                        <th class="text-center" title="Ventas Acumuladas">Vendidas</th>
                        <th class="text-center" title="Daños o Mermas">Restadas</th>
                        <th class="text-center" title="Ajustes Físicos">Reconteo</th>
                        <th class="text-center text-primary" title="Stock Actual en el Sistema">Quedan</th>
                    </tr>
                </thead>
                <tbody id="batchDetailsBody">
                    <!-- Dinámico -->
                </tbody>
            </table>
        </div>
        <div style="display: flex; justify-content: flex-end; margin-top: 1rem;">
            <button class="btn btn-secondary" onclick="closeModal('batchDetailsModal')">Cerrar</button>
        </div>
    </div>
</div>
<!-- Modal Editar Lotes de Ajuste -->
<div class="modal-overlay" id="editBatchesModal">
    <div class="modal-content" style="max-width: 1100px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-pen"></i> Editar Lote(s) del Ajuste</h3>
            <button type="button" class="modal-close" onclick="closeModal('editBatchesModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="editBatchesForm" onsubmit="event.preventDefault(); submitEditBatches();">
            @csrf
            @method('PUT')
            <input type="hidden" id="editAdjustmentId">
            <div class="table-container" style="border: none; margin: 0; box-shadow: none; overflow-x: auto; margin-bottom: 1rem;">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Lote</th>
                            <th style="width: 25%;">Vencimiento</th>
                            <th style="width: 25%;">Proveedor</th>
                            <th style="width: 25%;">Marca</th>
                        </tr>
                    </thead>
                    <tbody id="editBatchesBody">
                        <!-- Dinámico -->
                    </tbody>
                </table>
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editBatchesModal')">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let rowCount = 0;

    $(document).ready(function() {
        $('.select2-filter').select2();

        $('#adjType').on('change', function() {
            updateQtyLabel();
        });
    });

    function updateQtyLabel() {
        const type = document.getElementById('adjType').value;
        const batchCols = document.querySelectorAll('.batch-col');
        
        if (type === 'in') {
            batchCols.forEach(col => col.style.display = '');
        } else {
            batchCols.forEach(col => col.style.display = 'none');
        }
    }

    function addProductRow() {
        rowCount++;
        const tr = document.createElement('tr');
        tr.id = `row-${rowCount}`;
        
        tr.innerHTML = `
            <td>
                <select class="form-control product-select" name="products[${rowCount}][product_id]" required style="width:100%;"></select>
            </td>
            <td>
                <input type="number" class="form-control" name="products[${rowCount}][quantity]" step="0.001" min="0.001" required placeholder="0.00">
            </td>
            <td class="batch-col">
                <input type="text" class="form-control" name="products[${rowCount}][batch_number]" placeholder="Opcional">
            </td>
            <td class="batch-col">
                <input type="date" class="form-control" name="products[${rowCount}][expiry_date]">
            </td>
            <td class="text-center">
                <button type="button" class="btn text-danger" style="background:none; border:none; padding: 0.5rem;" onclick="removeRow(${rowCount})">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </td>
        `;
        document.getElementById('productsBody').appendChild(tr);

        // Initialize Select2 on the new row
        $(`#row-${rowCount} .product-select`).select2({
            dropdownParent: $('#adjustmentModal'),
            ajax: {
                url: '{{ route('inventory-adjustments.search-products') }}',
                dataType: 'json',
                delay: 250,
                data: function (params) { return { term: params.term }; },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return { text: item.name + ' (' + item.private_code + ') - Stock: ' + parseFloat(item.stock), id: item.id }
                        })
                    };
                },
                cache: true
            },
            placeholder: 'Buscar...',
            minimumInputLength: 2,
        });

        updateQtyLabel();
    }

    function removeRow(id) {
        document.getElementById(`row-${id}`).remove();
    }

    function openAdjustmentModal() {
        document.getElementById('productsBody').innerHTML = '';
        document.getElementById('adjustmentForm').reset();
        rowCount = 0;
        addProductRow();
        openModal('adjustmentModal');
    }

    function submitAdjustment() {
        const form = document.getElementById('adjustmentForm');
        
        const selects = document.querySelectorAll('.product-select');
        if (selects.length === 0) {
            showToast('Debes añadir al menos un producto.');
            return;
        }

        let valid = true;
        selects.forEach(s => {
            if(!s.value) {
                showToast('Asegúrate de seleccionar un producto en todas las filas.');
                valid = false;
            }
        });
        if(!valid) return;

        submitAjaxForm(form, '{{ route("inventory-adjustments.store") }}', () => {
            closeModal('adjustmentModal');
            window.location.reload();
        });
    }

    function loadLifecycle(id) {
        showGlobalLoader();
        fetch(`{{ url('inventory-adjustments') }}/${id}/lifecycle`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            hideGlobalLoader();
            
            const thead = document.getElementById('batchDetailsModal').querySelector('thead');
            
            // Check if it's a sale
            if (data.is_sale && data.sale) {
                document.getElementById('batchDetailsModal').querySelector('h3').innerHTML = '<i class="fa-solid fa-receipt"></i> Detalles de la Venta';
                const tbody = document.getElementById('batchDetailsBody');
                
                // Change table headers for sale view
                thead.innerHTML = `
                    <tr>
                        <th style="padding: 10px; border-bottom: 1px solid var(--border);">TICKET</th>
                        <th style="padding: 10px; border-bottom: 1px solid var(--border);">FECHA</th>
                        <th style="padding: 10px; border-bottom: 1px solid var(--border);">CAJERO</th>
                        <th style="padding: 10px; border-bottom: 1px solid var(--border);">MÉTODO DE PAGO</th>
                        <th style="padding: 10px; border-bottom: 1px solid var(--border); text-align: right;">TOTAL</th>
                    </tr>
                `;
                
                tbody.innerHTML = `
                    <tr>
                        <td class="font-bold">${data.sale.ticket}</td>
                        <td>${data.sale.date}</td>
                        <td>${data.sale.user}</td>
                        <td>${data.sale.payment}</td>
                        <td class="font-bold text-success text-right" style="text-align: right;">$ ${data.sale.total}</td>
                    </tr>
                `;
                openModal('batchDetailsModal');
                return;
            }

            // Restore standard headers
            document.getElementById('batchDetailsModal').querySelector('h3').innerHTML = '<i class="fa-solid fa-route"></i> Trazabilidad del Lote';
            thead.innerHTML = `
                <tr style="background: var(--bg-alt); text-align: left; font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase;">
                    <th style="padding: 10px; border-bottom: 1px solid var(--border);">Lote</th>
                    <th style="padding: 10px; border-bottom: 1px solid var(--border);">Vencimiento</th>
                    <th style="padding: 10px; border-bottom: 1px solid var(--border); text-align: center;">Inicial</th>
                    <th style="padding: 10px; border-bottom: 1px solid var(--border); text-align: center;">Vendidas</th>
                    <th style="padding: 10px; border-bottom: 1px solid var(--border); text-align: center;">Restadas</th>
                    <th style="padding: 10px; border-bottom: 1px solid var(--border); text-align: center;">Reconteo</th>
                    <th style="padding: 10px; border-bottom: 1px solid var(--border); text-align: center;">Quedan</th>
                </tr>
            `;

            const batches = Array.isArray(data) ? data : data.batches;
            const tbody = document.getElementById('batchDetailsBody');
            tbody.innerHTML = '';
            
            if (!batches || batches.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Este ajuste no afectó ningún lote.</td></tr>';
            } else {
                batches.forEach(b => {
                    const tr = document.createElement('tr');
                    
                    let expiryHTML = '<span class="text-muted">N/A</span>';
                    if (b.expiry_date) {
                        const expDate = new Date(b.expiry_date);
                        const today = new Date();
                        today.setHours(0,0,0,0);
                        expDate.setHours(0,0,0,0);
                        
                        const diffTime = expDate - today;
                        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                        
                        let badge = '';
                        if (diffDays < 0) {
                            badge = '<span class="badge" style="background:#fee2e2; color:#991b1b; font-size: 0.65rem; padding: 2px 5px; margin-left: 5px; border-radius: 4px;">Vencido</span>';
                        } else if (diffDays <= 30) {
                            badge = '<span class="badge" style="background:#fef08a; color:#854d0e; font-size: 0.65rem; padding: 2px 5px; margin-left: 5px; border-radius: 4px;">Por Vencer</span>';
                        } else {
                            badge = '<span class="badge" style="background:#dcfce7; color:#166534; font-size: 0.65rem; padding: 2px 5px; margin-left: 5px; border-radius: 4px;">Vigente</span>';
                        }
                        expiryHTML = b.expiry_date + badge;
                    }
                    
                    tr.innerHTML = `
                        <td class="font-bold" style="font-family: monospace; padding: 10px; border-bottom: 1px solid var(--border);">${b.batch_number}</td>
                        <td style="padding: 10px; border-bottom: 1px solid var(--border);">${expiryHTML}</td>
                        <td class="text-center" style="padding: 10px; border-bottom: 1px solid var(--border);">${b.initial}</td>
                        <td class="text-center text-success" style="padding: 10px; border-bottom: 1px solid var(--border);">${b.sold > 0 ? b.sold : '-'}</td>
                        <td class="text-center text-danger" style="padding: 10px; border-bottom: 1px solid var(--border);">${b.damaged > 0 ? b.damaged : '-'}</td>
                        <td class="text-center text-info" style="padding: 10px; border-bottom: 1px solid var(--border);">${b.recounted ? '<i class="fa-solid fa-check"></i> Sí' : '-'}</td>
                        <td class="text-center font-bold text-primary" style="padding: 10px; border-bottom: 1px solid var(--border);">${b.current}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }
            openModal('batchDetailsModal');
        })
        .catch(err => {
            hideGlobalLoader();
            console.error(err);
            showToast('Error al cargar la información del lote.');
        });
    }
    function editAdjustmentBatches(id) {
        showGlobalLoader();
        fetch(`{{ url('inventory-adjustments') }}/${id}/batches/edit`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            hideGlobalLoader();
            document.getElementById('editAdjustmentId').value = data.adjustment_id;
            const tbody = document.getElementById('editBatchesBody');
            tbody.innerHTML = '';
            
            if (data.batches.length === 0) {
                if (data.type === 'in') {
                    document.querySelector('#editBatchesForm button[type="submit"]').style.display = 'inline-block';
                    let providersOptions = '<option value="">Ninguno</option>';
                    data.providers.forEach(p => providersOptions += `<option value="${p.id}">${p.name}</option>`);
                    
                    let brandsOptions = '<option value="">Ninguno</option>';
                    data.brands.forEach(b => brandsOptions += `<option value="${b.id}">${b.name}</option>`);

                    tbody.innerHTML = `
                        <tr>
                            <td>
                                <input type="hidden" name="batches[0][id]" value="new">
                                <input type="text" class="form-control" name="batches[0][batch_number]" value="${data.default_batch}" required>
                            </td>
                            <td>
                                <input type="date" class="form-control" name="batches[0][expiry_date]" value="">
                            </td>
                            <td>
                                <select class="form-control" name="batches[0][provider_id]">
                                    ${providersOptions}
                                </select>
                            </td>
                            <td>
                                <select class="form-control" name="batches[0][brand_id]">
                                    ${brandsOptions}
                                </select>
                            </td>
                        </tr>
                        <tr><td colspan="4" class="text-muted" style="font-size:0.8rem;"><i class="fa-solid fa-info-circle"></i> Estás creando el lote para un registro antiguo que no tenía uno asignado.</td></tr>
                    `;
                } else {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Este ajuste no generó nuevos lotes modificables.</td></tr>';
                    document.querySelector('#editBatchesForm button[type="submit"]').style.display = 'none';
                }
            } else {
                document.querySelector('#editBatchesForm button[type="submit"]').style.display = 'inline-block';
                let providersOptions = '<option value="">Ninguno</option>';
                data.providers.forEach(p => providersOptions += `<option value="${p.id}">${p.name}</option>`);
                
                let brandsOptions = '<option value="">Ninguno</option>';
                data.brands.forEach(b => brandsOptions += `<option value="${b.id}">${b.name}</option>`);

                data.batches.forEach((b, index) => {
                    const tr = document.createElement('tr');
                    
                    tr.innerHTML = `
                        <td>
                            <input type="hidden" name="batches[${index}][id]" value="${b.id}">
                            <input type="text" class="form-control" name="batches[${index}][batch_number]" value="${b.batch_number}" required>
                        </td>
                        <td>
                            <input type="date" class="form-control" name="batches[${index}][expiry_date]" value="${b.expiry_date ? b.expiry_date.split(' ')[0] : ''}">
                        </td>
                        <td>
                            <select class="form-control" name="batches[${index}][provider_id]">
                                ${providersOptions}
                            </select>
                        </td>
                        <td>
                            <select class="form-control" name="batches[${index}][brand_id]">
                                ${brandsOptions}
                            </select>
                        </td>
                    `;
                    tbody.appendChild(tr);
                    
                    if (b.provider_id) {
                        tr.querySelector(`select[name="batches[${index}][provider_id]"]`).value = b.provider_id;
                    }
                    if (b.brand_id) {
                        tr.querySelector(`select[name="batches[${index}][brand_id]"]`).value = b.brand_id;
                    }
                });
            }
            openModal('editBatchesModal');
        })
        .catch(err => {
            hideGlobalLoader();
            console.error(err);
            showToast('Error al cargar la información para edición.');
        });
    }

    function submitEditBatches() {
        const id = document.getElementById('editAdjustmentId').value;
        const form = document.getElementById('editBatchesForm');
        
        submitAjaxForm(form, `{{ url('inventory-adjustments') }}/${id}/batches`, () => {
            closeModal('editBatchesModal');
            window.location.reload();
        });
    }
</script>
@endpush
