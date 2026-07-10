@extends('layouts.app')
@section('title', 'Editar Producto')

@section('content')
<div class="card mx-auto" style="max-width: 800px; margin: 0 auto;">
    <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="flex gap-4" style="flex-wrap: wrap;">
            <div class="form-group" style="flex: 1; min-width: 250px;">
                <label class="form-label">Nombre del Producto *</label>
                <input type="text" name="name" class="form-control" required value="{{ old('name', $product->name) }}">
            </div>
            
            <div class="form-group" style="flex: 1; min-width: 200px;">
                <label class="form-label">Código Privado *</label>
                <input type="text" name="private_code" class="form-control" required value="{{ old('private_code', $product->private_code) }}">
            </div>
        </div>

        <div class="flex gap-4" style="flex-wrap: wrap;">
            <div class="form-group" style="flex: 1; min-width: 200px;">
                <label class="form-label">Código EAN</label>
                <input type="text" name="ean_code" class="form-control" value="{{ old('ean_code', $product->ean_code) }}">
            </div>
            
            <div class="form-group" style="flex: 1; min-width: 200px;">
                <label class="form-label">Precio (USD) *</label>
                <div style="display: flex; gap: 5px;">
                    <input type="number" step="0.01" name="price_usd" id="standalone_edit_price_usd" class="form-control" required value="{{ old('price_usd', $product->price_usd) }}" style="flex: 1;">
                    <button type="button" class="btn btn-outline-secondary" onclick="toggleVat('standalone_edit_price_usd', this)" title="Sumar IVA a este monto" style="white-space: nowrap; transition: 0.3s;">
                        + IVA
                    </button>
                </div>
            </div>
        </div>

        <div class="flex gap-4" style="flex-wrap: wrap;">
            <div class="form-group" style="flex: 1; min-width: 200px;">
                <label class="form-label">Tamaño</label>
                <select name="size_type" class="form-control">
                    <option value="pequeño" {{ $product->size_type == 'pequeño' ? 'selected' : '' }}>Pequeño</option>
                    <option value="mediano" {{ $product->size_type == 'mediano' ? 'selected' : '' }}>Mediano</option>
                    <option value="grande" {{ $product->size_type == 'grande' ? 'selected' : '' }}>Grande</option>
                </select>
            </div>
            <div class="form-group" style="flex: 1; min-width: 200px;">
                <label class="form-label">Categoría *</label>
                <select name="category_id" class="form-control select2" required style="width: 100%;">
                    <option value="">Seleccione...</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }} {{ $cat->department ? '(' . $cat->department->name . ')' : '' }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex gap-4" style="flex-wrap: wrap;">
            <div class="form-group" style="flex: 1; min-width: 200px;">
                <label class="form-label">Marca (Opcional)</label>
                <select name="brand_id" class="form-control select2" style="width: 100%;">
                    <option value="">Seleccione... (Por defecto: Genérico)</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ $product->brand_id == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="flex: 1; min-width: 200px;">
                <label class="form-label">Proveedor (Opcional)</label>
                <select name="provider_id" class="form-control select2" style="width: 100%;">
                    <option value="">Seleccione... (Por defecto: Genérico)</option>
                    @foreach($providers as $prov)
                        <option value="{{ $prov->id }}" {{ $product->provider_id == $prov->id ? 'selected' : '' }}>{{ $prov->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Descripción</label>
            <textarea name="description" class="form-control" rows="3">{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Imagen del Producto (Dejar en blanco para mantener actual)</label>
            @if($product->image)
                <div class="mb-4">
                    <img src="{{ asset('storage/' . $product->image) }}" alt="Actual" style="height: 100px; border-radius: 8px;">
                </div>
            @endif
            <input type="file" name="image" class="form-control" accept="image/*">
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Actualizar Producto</button>
            <a href="{{ route('products.index') }}" class="btn btn-secondary" style="margin-left: 10px;">Cancelar</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            width: 'resolve'
        });
    });

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
</script>
@endpush
@endsection
