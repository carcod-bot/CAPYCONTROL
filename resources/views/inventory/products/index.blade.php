@extends('layouts.app')
@section('title', 'Productos')

@section('content')
<div class="card">
    <div class="flex justify-between items-center mb-4">
        <h3>Inventario de Productos</h3>
        <button class="btn btn-primary" onclick="openModal('createProductModal')">
            <i class="fa-solid fa-plus"></i> Nuevo Producto
        </button>
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
                        <input type="number" step="0.01" name="price_usd" class="form-control" required>
                    </div>
                </div>

                <div class="flex gap-4 mb-4" style="flex-wrap: wrap;">
                    <div class="form-group" style="flex: 1; min-width: 200px;">
                        <label class="form-label">Tamaño</label>
                        <select name="size_type" class="form-control">
                            <option value="pequeño">Pequeño</option>
                            <option value="mediano" selected>Mediano</option>
                            <option value="grande">Grande</option>
                        </select>
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
                        <input type="number" step="0.01" name="price_usd" id="edit_price_usd" class="form-control" required>
                    </div>
                </div>

                <div class="flex gap-4 mb-4" style="flex-wrap: wrap;">
                    <div class="form-group" style="flex: 1; min-width: 200px;">
                        <label class="form-label">Tamaño</label>
                        <select name="size_type" id="edit_size_type" class="form-control">
                            <option value="pequeño">Pequeño</option>
                            <option value="mediano">Mediano</option>
                            <option value="grande">Grande</option>
                        </select>
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

    <!-- Rest of the table -->
    <div class="table-container" style="overflow-x: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 60px;">Img</th>
                    <th>Cód Interno</th>
                    <th>Nombre</th>
                    <th>Dept/Cat</th>
                    <th>Tamaño</th>
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
                    <td><span style="text-transform: capitalize;">{{ $product->size_type }}</span></td>
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
        
        $('.filter-select2').select2({
            width: 'resolve'
        });
    });

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
                document.getElementById('edit_size_type').value = p.size_type;
                document.getElementById('edit_description').value = p.description || '';
                
                $('#edit_category_id').val(p.category_id).trigger('change');
                $('#edit_brand_id').val(p.brand_id).trigger('change');
                $('#edit_provider_id').val(p.provider_id).trigger('change');
                
                openModal('editProductModal');
            } else {
                alert('No se pudo cargar el producto');
            }
        })
        .catch(err => {
            hideGlobalLoader();
            alert('Error de conexión al cargar el producto');
            console.error(err);
        });
    }
</script>
@endpush
