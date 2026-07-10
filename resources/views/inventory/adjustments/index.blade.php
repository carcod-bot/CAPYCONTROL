@extends('layouts.app')
@section('title', 'Ajustes y Conteo Físico')

@section('content')
<div class="pos-action-bar">
    <div class="pos-action-bar-left">
        <h2 class="pos-section-title"><i class="fa-solid fa-scale-balanced"></i> Ajustes de Inventario</h2>
    </div>
    <div class="pos-action-bar-right">
        <button class="btn btn-primary" onclick="openModal('adjustmentModal')">
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
            <label class="form-label">Tipo de Ajuste</label>
            <select name="type" class="form-control select2-filter" style="width: 100%;">
                <option value="">Todos</option>
                <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>Entrada (Suma)</option>
                <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>Salida (Resta)</option>
                <option value="set" {{ request('type') == 'set' ? 'selected' : '' }}>Conteo Físico (Remplaza)</option>
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
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Producto</th>
                    <th>Tipo</th>
                    <th>Cant.</th>
                    <th>Stock Anterior</th>
                    <th>Nuevo Stock</th>
                    <th>Motivo</th>
                    <th>Usuario</th>
                </tr>
            </thead>
            <tbody>
                @forelse($adjustments as $adj)
                <tr>
                    <td>{{ $adj->created_at->format('d/m/Y h:i a') }}</td>
                    <td>
                        <div class="font-bold">{{ $adj->product->name }}</div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $adj->product->private_code }}</div>
                    </td>
                    <td>
                        @if($adj->type === 'in')
                            <span class="badge badge-success"><i class="fa-solid fa-arrow-down"></i> Entrada</span>
                        @elseif($adj->type === 'out')
                            <span class="badge" style="background:#fee2e2; color:#991b1b; border:1px solid #fecaca;"><i class="fa-solid fa-arrow-up"></i> Salida</span>
                        @else
                            <span class="badge" style="background:#dbeafe; color:#1e40af; border:1px solid #bfdbfe;"><i class="fa-solid fa-check-double"></i> Conteo</span>
                        @endif
                    </td>
                    <td class="font-bold text-center">{{ number_format($adj->quantity, 2) }}</td>
                    <td class="text-center text-muted">{{ number_format($adj->previous_stock, 2) }}</td>
                    <td class="font-bold text-center">{{ number_format($adj->new_stock, 2) }}</td>
                    <td>{{ $adj->reason }}</td>
                    <td>{{ $adj->user->username }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted" style="padding: 3rem;">
                        <i class="fa-solid fa-scale-balanced" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
                        No se encontraron registros de ajustes.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $adjustments->links('pagination::bootstrap-4') }}
</div>

<!-- Modal Adjustment -->
<div class="modal-overlay" id="adjustmentModal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-scale-balanced"></i> Registrar Ajuste / Conteo</h3>
            <button class="modal-close" onclick="closeModal('adjustmentModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="adjustmentForm" onsubmit="event.preventDefault(); submitAdjustment();">
            <div class="form-group">
                <label class="form-label">Buscar Producto <span class="text-danger">*</span></label>
                <select id="productId" name="product_id" class="form-control select2-ajax" style="width: 100%;" required>
                    <option value="">Buscar producto...</option>
                </select>
                <div id="selectedProductInfo" style="margin-top: 10px; display: none; padding: 10px; background: var(--background); border-radius: 8px; border: 1px solid var(--border);">
                    <div class="font-bold" id="spName"></div>
                    <div style="font-size: 0.85rem; color: var(--text-muted);">Código: <span id="spCode"></span> | Stock Actual: <strong id="spStock" class="text-primary"></strong></div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Tipo de Movimiento <span class="text-danger">*</span></label>
                    <select name="type" id="adjType" class="form-control select2-modal" required style="width: 100%;">
                        <option value="in">Entrada (Sumar al stock)</option>
                        <option value="out">Salida (Restar al stock)</option>
                        <option value="set">Conteo Físico (Reemplazar stock)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" id="qtyLabel">Cantidad a Ingresar <span class="text-danger">*</span></label>
                    <input type="number" name="quantity" class="form-control" step="0.001" min="0.001" required>
                </div>
            </div>

            <!-- Batch Details (only for IN) -->
            <div id="batchFieldsContainer" style="background: var(--background); padding: 15px; border-radius: 8px; border: 1px dashed var(--border); margin-bottom: 1rem;">
                <h4 style="font-size: 0.9rem; margin-top: 0; margin-bottom: 15px; color: var(--text-main);"><i class="fa-solid fa-box-open"></i> Datos del Lote</h4>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 10px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Nro de Lote (Opcional)</label>
                        <input type="text" name="batch_number" class="form-control" placeholder="Autogenerado si vacío">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Vencimiento (Opcional)</label>
                        <input type="date" name="expiry_date" class="form-control">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Marca (Opcional)</label>
                        <select name="brand_id" class="form-control select2-modal" style="width: 100%;">
                            <option value="">Por defecto del producto</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Proveedor (Opcional)</label>
                        <select name="provider_id" class="form-control select2-modal" style="width: 100%;">
                            <option value="">Por defecto del producto</option>
                            @foreach($providers as $prov)
                                <option value="{{ $prov->id }}">{{ $prov->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Motivo <span class="text-danger">*</span></label>
                <input type="text" name="reason" class="form-control" placeholder="Ej: Compra, Merma, Daño, Conteo Anual" required>
            </div>

            <div class="form-group">
                <label class="form-label">Notas Adicionales</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Observaciones opcionales..."></textarea>
            </div>

            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('adjustmentModal')">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Guardar Ajuste</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2-filter').select2();

        $('#adjType').select2({
            dropdownParent: $('#adjustmentModal')
        }).on('change', function() {
            updateQtyLabel();
        });

        $('.select2-modal').select2({
            dropdownParent: $('#adjustmentModal')
        });

        $('.select2-ajax').select2({
            dropdownParent: $('#adjustmentModal'),
            ajax: {
                url: '{{ route('inventory-adjustments.search-products') }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { term: params.term };
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.name + ' (' + item.private_code + ')',
                                id: item.id,
                                stock: item.stock,
                                name: item.name,
                                private_code: item.private_code
                            }
                        })
                    };
                },
                cache: true
            },
            placeholder: 'Escribe para buscar un producto...',
            minimumInputLength: 2,
        }).on('select2:select', function (e) {
            selectProduct(e.params.data);
        });
    });

    function selectProduct(prod) {
        document.getElementById('spName').textContent = prod.name;
        document.getElementById('spCode').textContent = prod.private_code;
        document.getElementById('spStock').textContent = parseFloat(prod.stock);
        document.getElementById('selectedProductInfo').style.display = 'block';
    }

    function updateQtyLabel() {
        const type = document.getElementById('adjType').value;
        const label = document.getElementById('qtyLabel');
        const batchFields = document.getElementById('batchFieldsContainer');
        
        if (type === 'in') {
            label.innerHTML = 'Cantidad a Ingresar <span class="text-danger">*</span>';
            batchFields.style.display = 'block';
        } else if (type === 'out') {
            label.innerHTML = 'Cantidad a Restar <span class="text-danger">*</span>';
            batchFields.style.display = 'none';
        } else {
            label.innerHTML = 'Cantidad Física Real <span class="text-danger">*</span>';
            batchFields.style.display = 'none';
        }
    }

    function submitAdjustment() {
        if (!document.getElementById('productId').value) {
            alert('Debes seleccionar un producto.');
            return;
        }

        const form = document.getElementById('adjustmentForm');
        submitAjaxForm(form, '{{ route("inventory-adjustments.store") }}', () => {
            closeModal('adjustmentModal');
            window.location.reload();
        });
    }
</script>
@endpush
