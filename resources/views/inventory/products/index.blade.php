@extends('layouts.app')
@section('title', 'Productos')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin: 0;">Inventario de Productos</h2>
        <div style="display: flex; gap: 0.75rem;">
            <button class="btn btn-secondary" onclick="openModal('massiveAdjustmentModal')">
                <i class="fa-solid fa-tags"></i> Ajuste Masivo
            </button>
            <button class="btn btn-primary" onclick="openModal('productModal')">
                <i class="fa-solid fa-plus"></i> Nuevo Producto
            </button>
        </div>
    </div>

    <!-- Filtros de Búsqueda -->
    <div style="border-bottom: 1px solid var(--border); padding-bottom: 1.5rem; margin-bottom: 1.5rem;">
        <form action="{{ route('products.index') }}" method="GET">
            <div class="flex gap-4" style="flex-wrap: wrap; align-items: flex-end;">
                <div class="form-group" style="flex: 1; min-width: 180px; margin-bottom: 0;">
                    <label class="form-label text-muted" style="font-size: 0.8rem; margin-bottom: 4px;">Código o EAN</label>
                    <input type="text" name="search_code" class="form-control" value="{{ request('search_code') }}" placeholder="Ej: PRD-001">
                </div>
                
                <div class="form-group" style="flex: 1; min-width: 180px; margin-bottom: 0;">
                    <label class="form-label text-muted" style="font-size: 0.8rem; margin-bottom: 4px;">Categoría</label>
                    <select name="category_id" class="form-control filter-select2" style="width: 100%;">
                        <option value="">Todas las categorías</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }} ({{ $cat->department->name ?? 'Sin Dpto' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="flex: 1; min-width: 180px; margin-bottom: 0;">
                    <label class="form-label text-muted" style="font-size: 0.8rem; margin-bottom: 4px;">Marca</label>
                    <select name="brand_id" class="form-control filter-select2" style="width: 100%;">
                        <option value="">Todas las marcas</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group" style="flex: 1; min-width: 100px; margin-bottom: 0;">
                    <label class="form-label text-muted" style="font-size: 0.8rem; margin-bottom: 4px;">Min (USD)</label>
                    <input type="number" step="0.01" name="price_min" class="form-control" value="{{ request('price_min') }}" placeholder="0.00">
                </div>
                
                <div class="form-group" style="flex: 1; min-width: 100px; margin-bottom: 0;">
                    <label class="form-label text-muted" style="font-size: 0.8rem; margin-bottom: 4px;">Max (USD)</label>
                    <input type="number" step="0.01" name="price_max" class="form-control" value="{{ request('price_max') }}" placeholder="99.99">
                </div>
                
                <div class="flex items-end gap-2">
                    <button type="submit" class="btn btn-primary" style="padding: 0.85rem 1rem;"><i class="fa-solid fa-search"></i> Buscar</button>
                    @if(request()->hasAny(['search_code', 'category_id', 'brand_id', 'price_min', 'price_max']))
                        <a href="{{ route('products.index') }}" class="btn btn-secondary" style="padding: 0.85rem 1rem;" title="Limpiar"><i class="fa-solid fa-eraser"></i></a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Modal for Create -->
    <div id="createProductModal" class="modal-overlay">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3>Crear Producto</h3>
                <button class="modal-close" onclick="closeModal('createProductModal')"><i class="fa-solid fa-times"></i></button>
            </div>
            
            <form id="createProductForm" action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" onsubmit="event.preventDefault(); submitAjaxForm(this, this.action, () => window.location.reload())">
                @csrf
                <div class="flex gap-4" style="flex-wrap: wrap;">
                    <div class="form-group" style="flex: 1; min-width: 250px;">
                        <label class="form-label">Nombre del Producto *</label>
                        <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
                    </div>
                    <div class="form-group" style="flex: 1; min-width: 200px;">
                        <label class="form-label">Código Interno *</label>
                        <input type="text" name="private_code" class="form-control" required value="{{ old('private_code', $codeMode == 'incremental' ? $nextCode : '') }}" {{ $codeMode == 'incremental' ? 'readonly' : '' }}>
                        @if($codeMode == 'incremental')
                            <small class="text-muted">Generado automáticamente</small>
                        @endif
                    </div>
                </div>

                <div class="flex gap-4 mb-4" style="flex-wrap: wrap;">
                    <div class="form-group" style="flex: 1; min-width: 200px;">
                        <label class="form-label">Código EAN (Opcional)</label>
                        <input type="text" name="ean_code" class="form-control">
                    </div>
                    
                    <div class="form-group" style="flex: 1; min-width: 200px;">
                        <label class="form-label">Precio (USD) *</label>
                        <div style="display: flex; gap: 5px;">
                            <input type="number" step="0.01" name="price_usd" id="create_price_usd" class="form-control" required style="flex: 1;">
                            <button type="button" class="btn btn-outline-secondary" onclick="toggleVat('create_price_usd', this)" title="Sumar IVA a este monto" style="white-space: nowrap; transition: 0.3s;">
                                + IVA
                            </button>
                        </div>
                    </div>

                    <div class="form-group" style="flex: 1; min-width: 200px;">
                        <label class="form-label">Categoría *</label>
                        <select name="category_id" class="form-control select2" required style="width: 100%;">
                            <option value="">Seleccione...</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }} {{ $cat->department ? '(' . $cat->department->name . ')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex gap-4 mb-4" style="flex-wrap: wrap;">
                    <div class="form-group" style="flex: 1; min-width: 200px;">
                        <label class="form-label">Marca (Opcional)</label>
                        <select name="brand_id" class="form-control select2" style="width: 100%;">
                            <option value="">Seleccione... (Por defecto: Genérico)</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="flex: 1; min-width: 200px;">
                        <label class="form-label">Proveedor (Opcional)</label>
                        <select name="provider_id" class="form-control select2" style="width: 100%;">
                            <option value="">Seleccione... (Por defecto: Genérico)</option>
                            @foreach($providers as $prov)
                                <option value="{{ $prov->id }}">{{ $prov->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Descripción</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Imagen</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                
                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 1rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('createProductModal')">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Producto</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal for Edit -->
    <div id="editProductModal" class="modal-overlay">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3>Editar Producto</h3>
                <button class="modal-close" onclick="closeModal('editProductModal')"><i class="fa-solid fa-times"></i></button>
            </div>
            
            <form id="editProductForm" method="POST" enctype="multipart/form-data" onsubmit="event.preventDefault(); submitAjaxForm(this, this.action, () => window.location.reload())">
                @csrf
                @method('PUT')
                <div class="flex gap-4" style="flex-wrap: wrap;">
                    <div class="form-group" style="flex: 1; min-width: 250px;">
                        <label class="form-label">Nombre del Producto *</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="form-group" style="flex: 1; min-width: 200px;">
                        <label class="form-label">Código Interno *</label>
                        <input type="text" name="private_code" id="edit_private_code" class="form-control" required>
                    </div>
                </div>

                <div class="flex gap-4 mb-4" style="flex-wrap: wrap;">
                    <div class="form-group" style="flex: 1; min-width: 200px;">
                        <label class="form-label">Código EAN (Opcional)</label>
                        <input type="text" name="ean_code" id="edit_ean_code" class="form-control">
                    </div>
                    
                    <div class="form-group" style="flex: 1; min-width: 200px;">
                        <label class="form-label">Precio (USD) *</label>
                        <div style="display: flex; gap: 5px;">
                            <input type="number" step="0.01" name="price_usd" id="edit_price_usd" class="form-control" required style="flex: 1;">
                            <button type="button" class="btn btn-outline-secondary" onclick="toggleVat('edit_price_usd', this)" title="Sumar IVA a este monto" style="white-space: nowrap; transition: 0.3s;">
                                + IVA
                            </button>
                        </div>
                    </div>

                    <div class="form-group" style="flex: 1; min-width: 200px;">
                        <label class="form-label">Categoría *</label>
                        <select name="category_id" id="edit_category_id" class="form-control select2" required style="width: 100%;">
                            <option value="">Seleccione...</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }} {{ $cat->department ? '(' . $cat->department->name . ')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex gap-4 mb-4" style="flex-wrap: wrap;">
                    <div class="form-group" style="flex: 1; min-width: 200px;">
                        <label class="form-label">Marca (Opcional)</label>
                        <select name="brand_id" id="edit_brand_id" class="form-control select2" style="width: 100%;">
                            <option value="">Seleccione... (Por defecto: Genérico)</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="flex: 1; min-width: 200px;">
                        <label class="form-label">Proveedor (Opcional)</label>
                        <select name="provider_id" id="edit_provider_id" class="form-control select2" style="width: 100%;">
                            <option value="">Seleccione... (Por defecto: Genérico)</option>
                            @foreach($providers as $prov)
                                <option value="{{ $prov->id }}">{{ $prov->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Descripción</label>
                    <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Imagen Nueva (Opcional)</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                
                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 1rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editProductModal')">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar Producto</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal for Massive Adjustment -->
    <div id="massiveAdjustmentModal" class="modal-overlay">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3><i class="fa-solid fa-tags"></i> Ajuste Masivo de Precios</h3>
                <button class="modal-close" onclick="closeModal('massiveAdjustmentModal')"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <form id="massiveAdjustmentForm" action="{{ route('products.massive-adjustment') }}" method="POST" onsubmit="event.preventDefault(); confirmMassiveAdjustment(this);">
                @csrf
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Aplicar a: <span class="text-danger">*</span></label>
                        <select name="apply_to" id="adj_apply_to" class="form-control select2" style="width: 100%;">
                            <option value="category">Por Categoría</option>
                            <option value="department">Por Departamento</option>
                            <option value="brand">Por Marca</option>
                            <option value="provider">Por Proveedor</option>
                            <option value="products">Productos Específicos</option>
                        </select>
                    </div>

                    <div class="form-group" id="adj_category_container" style="grid-column: 1 / -1;">
                        <label class="form-label">Seleccionar Categoría <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-control select2" style="width: 100%;">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" id="adj_department_container" style="display: none; grid-column: 1 / -1;">
                        <label class="form-label">Seleccionar Departamento <span class="text-danger">*</span></label>
                        <select name="department_id" class="form-control select2" style="width: 100%;">
                            @php
                                $uniqueDepts = collect();
                                foreach($categories as $cat) {
                                    if($cat->department) $uniqueDepts->put($cat->department->id, $cat->department);
                                }
                            @endphp
                            @foreach($uniqueDepts as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" id="adj_brand_container" style="display: none; grid-column: 1 / -1;">
                        <label class="form-label">Seleccionar Marca <span class="text-danger">*</span></label>
                        <select name="brand_id" class="form-control select2" style="width: 100%;">
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" id="adj_provider_container" style="display: none; grid-column: 1 / -1;">
                        <label class="form-label">Seleccionar Proveedor <span class="text-danger">*</span></label>
                        <select name="provider_id" class="form-control select2" style="width: 100%;">
                            @foreach($providers as $provider)
                                <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" id="adj_products_container" style="display: none; grid-column: 1 / -1; margin-top: 0.5rem;">
                        <h4 style="font-size: 1rem; margin-bottom: 0.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                            Productos a Ajustar
                            <button type="button" class="btn btn-secondary btn-sm" onclick="addMassiveProductRow()" style="padding: 0.3rem 0.8rem; font-size: 0.85rem;">
                                <i class="fa-solid fa-plus"></i> Añadir Fila
                            </button>
                        </h4>
                        <div class="table-container" style="border: none; box-shadow: none; overflow-x: auto; margin-bottom: 0;">
                            <table class="table" style="min-width: 600px; margin-bottom: 0;">
                                <thead>
                                    <tr>
                                        <th style="width: 40%;">Producto</th>
                                        <th style="width: 30%;">Tipo de Ajuste</th>
                                        <th style="width: 20%;">Valor</th>
                                        <th style="width: 10%;"></th>
                                    </tr>
                                </thead>
                                <tbody id="massiveProductsBody">
                                    <!-- Dynamic rows -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="form-group" id="adj_global_type">
                        <label class="form-label">Tipo de Ajuste <span class="text-danger">*</span></label>
                        <select name="adjustment_type" id="global_adjustment_type" class="form-control select2" style="width: 100%;" onchange="syncGlobalToRows()">
                            <option value="percent_inc">Aumento en Porcentaje (%)</option>
                            <option value="percent_dec">Descuento en Porcentaje (%)</option>
                            <option value="fixed_inc">Aumento Fijo ($)</option>
                            <option value="fixed_dec">Descuento Fijo ($)</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="adj_global_value">
                        <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                            <label class="form-label" style="margin-bottom: 0;">Valor General <span class="text-danger">*</span></label>
                            <button type="button" class="btn btn-secondary btn-sm" id="btn_apply_all_rows" style="display: none; padding: 0.2rem 0.5rem; font-size: 0.75rem;" onclick="syncGlobalToRows()">
                                <i class="fa-solid fa-arrows-rotate"></i> Aplicar a filas
                            </button>
                        </div>
                        <input type="number" step="0.01" min="0" name="adjustment_value" id="global_adjustment_value" class="form-control" placeholder="Ej: 10" style="margin-top: 0.5rem;" oninput="syncGlobalToRows()">
                    </div>
                </div>
                
                <div class="alert alert-warning" style="margin-top: 10px; font-size: 0.85rem;">
                    <i class="fa-solid fa-triangle-exclamation"></i> <strong>Advertencia:</strong> Esta acción modificará los precios de venta de múltiples productos a la vez de forma irreversible.
                </div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('massiveAdjustmentModal')">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Aplicar Ajuste</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Rest of the table -->
    <div class="table-container" style="overflow-x: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 60px;">Img</th>
                    <th>Cód Interno</th>
                    <th>Nombre</th>
                    <th>Dept/Cat</th>
                    <th>Stock</th>
                    <th>Precio</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr id="row-{{ $product->id }}">
                    <td>
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" style="width: 40px; height: 40px; border-radius: 8px; object-fit: cover;" onerror="this.outerHTML='<div style=\'width: 40px; height: 40px; border-radius: 8px; background: var(--border); display:flex; align-items:center; justify-content:center; color:var(--text-muted);\'><i class=\'fa-solid fa-image\'></i></div>'">
                        @else
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: var(--border); display:flex; align-items:center; justify-content:center; color:var(--text-muted);"><i class="fa-solid fa-box"></i></div>
                        @endif
                    </td>
                    <td style="font-family: monospace;">{{ $product->private_code }}</td>
                    <td style="font-weight: 600;">{{ $product->name }}<br><small class="text-muted">{{ $product->ean_code }}</small></td>
                    <td>
                        <span class="badge" style="background:var(--primary-light); color:var(--primary);">{{ $product->department->name ?? 'N/A' }}</span>
                        <span class="badge" style="background:#f1f5f9; color:#475569;">{{ $product->category->name ?? 'N/A' }}</span>
                        <br>
                        <span class="badge" style="background:#e0e7ff; color:#3730a3; margin-top:4px; font-size:0.7rem;"><i class="fa-solid fa-copyright"></i> {{ optional($product->brand)->name ?? 'Genérico' }}</span>
                        <span class="badge" style="background:#fef3c7; color:#92400e; margin-top:4px; font-size:0.7rem;"><i class="fa-solid fa-truck"></i> {{ optional($product->provider)->name ?? 'Genérico' }}</span>
                    </td>
                    <td style="font-weight: 700; color: {{ $product->stock <= 0 ? 'var(--danger)' : 'var(--text-main)' }};">{{ floatval($product->stock) }}</td>
                    <td style="font-weight: 700; color: #10b981;">${{ number_format($product->price_usd, 2) }}</td>
                    <td>
                        <button type="button" class="btn btn-secondary" onclick="editProduct({{ $product->id }})" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;"><i class="fa-solid fa-edit"></i></button>
                        <button type="button" class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;" onclick="deleteAjax('{{ route('products.destroy', $product) }}', () => document.getElementById('row-{{ $product->id }}').remove())">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="mt-4" style="padding: 0 1.5rem 1.5rem;">
        {{ $products->links('pagination::bootstrap-4') }}
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#createProductModal .select2').select2({
            dropdownParent: $('#createProductModal'),
            width: 'resolve'
        });
        
        $('#editProductModal .select2').select2({
            dropdownParent: $('#editProductModal'),
            width: 'resolve'
        });
        
        $('#massiveAdjustmentModal .select2').select2({
            dropdownParent: $('#massiveAdjustmentModal'),
            minimumResultsForSearch: Infinity,
            width: 'resolve'
        });
        
        $('#adj_apply_to').on('change', function() {
            toggleAdjustmentTarget();
        });
        
        // Initialize with one row if opened empty
        addMassiveProductRow();
        
        $('.filter-select2').select2({
            width: 'resolve'
        });

        // Inicializar estado de los campos requeridos
        toggleAdjustmentTarget();
    });

    let massiveRowCount = 0;
    function addMassiveProductRow() {
        massiveRowCount++;
        const tr = document.createElement('tr');
        tr.id = `mass-row-${massiveRowCount}`;
        
        const globalType = document.getElementById('global_adjustment_type').value || 'percent_inc';
        const globalVal = document.getElementById('global_adjustment_value').value || '';

        tr.innerHTML = `
            <td>
                <select class="form-control mass-product-select" name="products[${massiveRowCount}][product_id]" required style="width:100%;"></select>
            </td>
            <td>
                <select class="form-control mass-type-select" name="products[${massiveRowCount}][adjustment_type]" required>
                    <option value="percent_inc" ${globalType === 'percent_inc' ? 'selected' : ''}>Aumento (%)</option>
                    <option value="percent_dec" ${globalType === 'percent_dec' ? 'selected' : ''}>Descuento (%)</option>
                    <option value="fixed_inc" ${globalType === 'fixed_inc' ? 'selected' : ''}>Aumento Fijo ($)</option>
                    <option value="fixed_dec" ${globalType === 'fixed_dec' ? 'selected' : ''}>Descuento Fijo ($)</option>
                </select>
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="products[${massiveRowCount}][adjustment_value]" class="form-control mass-value-input" required placeholder="0.00" value="${globalVal}">
            </td>
            <td class="text-center">
                <button type="button" class="btn text-danger" style="background:none; border:none; padding: 0.5rem;" onclick="document.getElementById('mass-row-${massiveRowCount}').remove()">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </td>
        `;
        document.getElementById('massiveProductsBody').appendChild(tr);

        $(`#mass-row-${massiveRowCount} .mass-product-select`).select2({
            dropdownParent: $('#massiveAdjustmentModal'),
            ajax: {
                url: '{{ route('inventory-adjustments.search-products') }}',
                dataType: 'json',
                delay: 250,
                data: function (params) { return { term: params.term }; },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return { text: item.name + ' (' + item.private_code + ')', id: item.id }
                        })
                    };
                },
                cache: true
            },
            placeholder: 'Buscar producto...',
            minimumInputLength: 2,
        });
    }

    function editProduct(id) {
        showGlobalLoader();
        fetch('{{ url("products") }}/' + id + '/edit', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            hideGlobalLoader();
            if (data.success) {
                let p = data.product;
                document.getElementById('editProductForm').action = '{{ url("products") }}/' + id;
                document.getElementById('edit_name').value = p.name;
                document.getElementById('edit_private_code').value = p.private_code;
                document.getElementById('edit_ean_code').value = p.ean_code || '';
                document.getElementById('edit_price_usd').value = parseFloat(p.price_usd).toFixed(2);
                document.getElementById('edit_description').value = p.description || '';
                
                $('#edit_category_id').val(p.category_id).trigger('change');
                $('#edit_brand_id').val(p.brand_id).trigger('change');
                $('#edit_provider_id').val(p.provider_id).trigger('change');
                
                openModal('editProductModal');
            } else {
                showToast('No se pudo cargar el producto');
            }
        })
        .catch(err => {
            hideGlobalLoader();
            showToast('Error de conexión al cargar el producto');
            console.error(err);
        });
    }

    let originalPrices = {};

    function toggleVat(inputId, btn) {
        let input = document.getElementById(inputId);
        if (!input || !input.value) return;
        
        let currentPrice = parseFloat(input.value);
        if (isNaN(currentPrice)) return;
        
        let taxType = '{{ \App\Models\Setting::get("tax_type", "percentage") }}';
        let taxAmount = parseFloat('{{ \App\Models\Setting::get("tax_amount", "16") }}');
        
        let isBlue = btn.classList.contains('btn-primary');

        if (isBlue) {
            // Revertir (Quitar IVA) - Poner Gris
            if (originalPrices[inputId]) {
                input.value = parseFloat(originalPrices[inputId]).toFixed(2);
            }
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-outline-secondary');
            btn.style.backgroundColor = '';
            btn.style.color = '';
        } else {
            // Sumar IVA - Poner Azul
            originalPrices[inputId] = input.value;
            
            if (taxType === 'percentage') {
                currentPrice = currentPrice * (1 + (taxAmount / 100));
            } else if (taxType === 'fixed') {
                currentPrice = currentPrice + taxAmount;
            }
            
            input.value = currentPrice.toFixed(2);
            
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-primary');
        }

        // Si el usuario edita el campo manualmente, resetear a gris
        input.addEventListener('input', function handler() {
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-outline-secondary');
            originalPrices[inputId] = null;
            input.removeEventListener('input', handler);
        });
    }

    function toggleAdjustmentTarget() {
        const applyTo = document.getElementById('adj_apply_to').value;
        
        document.getElementById('adj_category_container').style.display = applyTo === 'category' ? 'block' : 'none';
        document.getElementById('adj_department_container').style.display = applyTo === 'department' ? 'block' : 'none';
        document.getElementById('adj_brand_container').style.display = applyTo === 'brand' ? 'block' : 'none';
        document.getElementById('adj_provider_container').style.display = applyTo === 'provider' ? 'block' : 'none';
        
        document.getElementById('adj_products_container').style.display = applyTo === 'products' ? 'block' : 'none';
        
        // Deshabilitar inputs del contenedor de productos si está oculto para evitar el error "invalid form control not focusable"
        document.getElementById('adj_products_container').querySelectorAll('input, select').forEach(el => {
            el.disabled = applyTo !== 'products';
        });
        
        // El botón de aplicar a todos solo se ve si estamos en modo productos
        document.getElementById('btn_apply_all_rows').style.display = applyTo === 'products' ? 'inline-block' : 'none';

        // Ahora los campos globales siempre son requeridos porque si no es "productos", se usan como el ajuste real
        // y si es "productos", se usan como valores por defecto/masivos (aunque en productos podríamos relajarlo)
        if (applyTo !== 'products') {
            document.getElementById('global_adjustment_type').setAttribute('required', 'required');
            document.getElementById('global_adjustment_value').setAttribute('required', 'required');
        } else {
            // Cuando es por productos, el valor global no es estrictamente requerido para enviar el form, ya que las filas tienen los suyos
            document.getElementById('global_adjustment_type').removeAttribute('required');
            document.getElementById('global_adjustment_value').removeAttribute('required');
        }
    }

    function syncGlobalToRows() {
        const applyTo = document.getElementById('adj_apply_to').value;
        if (applyTo === 'products') {
            const globalType = document.getElementById('global_adjustment_type').value;
            const globalVal = document.getElementById('global_adjustment_value').value;
            
            document.querySelectorAll('.mass-type-select').forEach(sel => sel.value = globalType);
            if (globalVal !== '') {
                document.querySelectorAll('.mass-value-input').forEach(inp => inp.value = globalVal);
            }
        }
    }

    function confirmMassiveAdjustment(form) {
        const applyTo = document.getElementById('adj_apply_to').value;
        if (applyTo === 'products') {
            const selects = document.querySelectorAll('.mass-product-select');
            if (selects.length === 0) {
                showToast('Debes añadir al menos un producto a la tabla.');
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
        }

        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción afectará el precio de venta de múltiples productos inmediatamente.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: 'var(--primary)',
            cancelButtonColor: '#ef4444',
            confirmButtonText: '<i class="fa-solid fa-check"></i> Sí, aplicar ajuste',
            cancelButtonText: 'Cancelar',
            background: document.body.classList.contains('dark-mode') ? 'var(--surface)' : '#fff',
            color: document.body.classList.contains('dark-mode') ? 'var(--text-main)' : '#545454'
        }).then((result) => {
            if (result.isConfirmed) {
                submitAjaxForm(form, '{{ route("products.massive-adjustment") }}', function() {
                    closeModal('massiveAdjustmentModal');
                    showToast('Ajuste masivo aplicado exitosamente', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                });
            }
        });
    }
</script>
@endpush
