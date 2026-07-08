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
            <select name="type" class="form-control">
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
                <div style="position: relative;">
                    <input type="text" id="productSearch" class="form-control" placeholder="Escribe el nombre o código del producto..." autocomplete="off">
                    <input type="hidden" name="product_id" id="productId" required>
                    <div id="productSearchResults" style="position: absolute; top: 100%; left: 0; right: 0; background: var(--surface); border: 1px solid var(--border); border-radius: 8px; box-shadow: var(--shadow-md); z-index: 10; display: none; max-height: 200px; overflow-y: auto;">
                        <!-- Results injected here -->
                    </div>
                </div>
                <div id="selectedProductInfo" style="margin-top: 10px; display: none; padding: 10px; background: var(--background); border-radius: 8px; border: 1px solid var(--border);">
                    <div class="font-bold" id="spName"></div>
                    <div style="font-size: 0.85rem; color: var(--text-muted);">Código: <span id="spCode"></span> | Stock Actual: <strong id="spStock" class="text-primary"></strong></div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Tipo de Movimiento <span class="text-danger">*</span></label>
                    <select name="type" id="adjType" class="form-control" required onchange="updateQtyLabel()">
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
    let searchTimeout = null;

    document.getElementById('productSearch').addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        const term = e.target.value.trim();
        const resultsContainer = document.getElementById('productSearchResults');
        
        if (term.length < 2) {
            resultsContainer.style.display = 'none';
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(`{{ route('inventory-adjustments.search-products') }}?term=${encodeURIComponent(term)}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                resultsContainer.innerHTML = '';
                if (data.length === 0) {
                    resultsContainer.innerHTML = '<div style="padding: 10px; color: var(--text-muted); text-align: center;">No se encontraron productos</div>';
                } else {
                    data.forEach(prod => {
                        const div = document.createElement('div');
                        div.style.padding = '10px 15px';
                        div.style.cursor = 'pointer';
                        div.style.borderBottom = '1px solid var(--border)';
                        div.innerHTML = `<strong>${prod.name}</strong> <span style="font-size:0.8rem; color:var(--text-muted);">(${prod.private_code}) - Stock: ${parseFloat(prod.stock)}</span>`;
                        
                        div.addEventListener('mouseover', () => div.style.background = 'var(--primary-light)');
                        div.addEventListener('mouseout', () => div.style.background = 'transparent');
                        
                        div.addEventListener('click', () => {
                            selectProduct(prod);
                            resultsContainer.style.display = 'none';
                            document.getElementById('productSearch').value = '';
                        });
                        resultsContainer.appendChild(div);
                    });
                }
                resultsContainer.style.display = 'block';
            });
        }, 300);
    });

    // Cierra resultados al hacer clic fuera
    document.addEventListener('click', (e) => {
        if (!e.target.closest('#productSearch') && !e.target.closest('#productSearchResults')) {
            document.getElementById('productSearchResults').style.display = 'none';
        }
    });

    function selectProduct(prod) {
        document.getElementById('productId').value = prod.id;
        document.getElementById('spName').textContent = prod.name;
        document.getElementById('spCode').textContent = prod.private_code;
        document.getElementById('spStock').textContent = parseFloat(prod.stock);
        document.getElementById('selectedProductInfo').style.display = 'block';
    }

    function updateQtyLabel() {
        const type = document.getElementById('adjType').value;
        const label = document.getElementById('qtyLabel');
        if (type === 'in') {
            label.innerHTML = 'Cantidad a Ingresar <span class="text-danger">*</span>';
        } else if (type === 'out') {
            label.innerHTML = 'Cantidad a Restar <span class="text-danger">*</span>';
        } else {
            label.innerHTML = 'Cantidad Física Real <span class="text-danger">*</span>';
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
