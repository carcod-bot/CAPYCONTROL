@extends('layouts.app')
@section('title', 'Promociones')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h1 class="page-title">Gestión de Promociones</h1>
</div>

<div class="card max-w-6xl mx-auto">
    <div class="flex justify-between items-center mb-4">
        <h2 style="font-size: 1.2rem; font-weight: 700;">Lista de Promociones</h2>
        <button class="btn btn-primary" onclick="openPromoModal('create')"><i class="fa-solid fa-plus"></i> Nueva Promoción</button>
    </div>

    <div class="table-container" style="overflow-x: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descuento</th>
                    <th>Aplica a</th>
                    <th>Vigencia</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($promotions as $promo)
                <tr id="row-{{ $promo->id }}">
                    <td style="font-weight: 600;">{{ $promo->name }}</td>
                    <td>
                        <span class="badge badge-info">
                            {{ $promo->discount_type === 'percentage' ? number_format($promo->discount_value, 0) . '%' : '$' . number_format($promo->discount_value, 2) }}
                        </span>
                    </td>
                    <td>
                        @php
                            $targetName = 'Desconocido';
                            $typeLabel = 'N/A';
                            if ($promo->promotable) {
                                if ($promo->promotable_type === 'App\Models\Product') { $typeLabel = 'Producto'; $targetName = $promo->promotable->name; }
                                elseif ($promo->promotable_type === 'App\Models\Category') { $typeLabel = 'Categoría'; $targetName = $promo->promotable->name; }
                                elseif ($promo->promotable_type === 'App\Models\Department') { $typeLabel = 'Departamento'; $targetName = $promo->promotable->name; }
                                elseif ($promo->promotable_type === 'App\Models\Currency') { $typeLabel = 'Moneda'; $targetName = $promo->promotable->description; }
                                elseif ($promo->promotable_type === 'App\Models\PaymentMethod') { $typeLabel = 'Método Pago'; $targetName = $promo->promotable->description; }
                            }
                        @endphp
                        <small class="text-muted">{{ $typeLabel }}</small><br>
                        <strong>{{ $targetName }}</strong>
                    </td>
                    <td>
                        <small>
                            Desde: {{ $promo->start_date ? $promo->start_date->format('d/m/Y') : 'Siempre' }}<br>
                            Hasta: {{ $promo->end_date ? $promo->end_date->format('d/m/Y') : 'Siempre' }}
                        </small>
                    </td>
                    <td>
                        <span class="badge" style="background: {{ $promo->active ? 'var(--primary-light)' : '#fee2e2' }}; color: {{ $promo->active ? 'var(--primary)' : '#991b1b' }};">
                            {{ $promo->active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-secondary" onclick="editPromo({{ $promo->id }}, '{{ addslashes($promo->name) }}', '{{ $promo->discount_type }}', {{ $promo->discount_value }}, '{{ addslashes($promo->promotable_type) }}', {{ $promo->promotable_id }}, '{{ $promo->start_date ? $promo->start_date->format('Y-m-d\TH:i') : '' }}', '{{ $promo->end_date ? $promo->end_date->format('Y-m-d\TH:i') : '' }}', {{ $promo->active }})" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;"><i class="fa-solid fa-edit"></i></button>
                        <button class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;" onclick="deleteAjax('{{ route('promotions.destroy', $promo) }}', () => document.getElementById('row-{{ $promo->id }}').remove())">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if($promotions->hasPages())
        <div class="mt-4">{{ $promotions->links('pagination::bootstrap-4') }}</div>
        @endif
    </div>
</div>

<!-- Modal Promoción -->
<div class="modal-overlay" id="promoModal">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header">
            <h3 id="promoModalTitle">Nueva Promoción</h3>
            <button class="modal-close" onclick="closeModal('promoModal')"><i class="fa-solid fa-times"></i></button>
        </div>
        <form id="promoForm" action="{{ route('promotions.store') }}" method="POST" onsubmit="event.preventDefault(); submitAjaxForm(this, this.action, () => window.location.reload())">
            @csrf
            <input type="hidden" name="_method" id="promoMethod" value="POST">
            
            <div class="form-group">
                <label class="form-label">Nombre de la Promoción *</label>
                <input type="text" name="name" id="promo_name" class="form-control" required placeholder="Ej. Black Friday">
            </div>

            <div class="flex gap-4 mb-4">
                <div class="form-group w-full" style="margin-bottom: 0;">
                    <label class="form-label">Tipo de Descuento *</label>
                    <select name="discount_type" id="promo_discount_type" class="form-control select2" required style="width: 100%;">
                        <option value="percentage">Porcentaje (%)</option>
                        <option value="fixed">Monto Fijo ($)</option>
                    </select>
                </div>
                <div class="form-group w-full" style="margin-bottom: 0;">
                    <label class="form-label">Valor del Descuento *</label>
                    <input type="number" step="0.01" min="0" name="discount_value" id="promo_discount_value" class="form-control" required>
                </div>
            </div>

            <div class="flex gap-4 mb-4">
                <div class="form-group w-full" style="margin-bottom: 0;">
                    <label class="form-label">Aplicar a (Nivel) *</label>
                    <select name="promotable_type" id="promo_promotable_type" class="form-control select2" required onchange="filterTargets()" style="width: 100%;">
                        <option value="">-- Seleccionar --</option>
                        <option value="App\Models\Product">Producto Individual</option>
                        <option value="App\Models\Category">Categoría Completa</option>
                        <option value="App\Models\Department">Departamento Completo</option>
                        <option value="App\Models\PaymentMethod">Método de Pago</option>
                        <option value="App\Models\Currency">Moneda</option>
                    </select>
                </div>
                <div class="form-group w-full" style="margin-bottom: 0;">
                    <label class="form-label">Elemento Específico *</label>
                    <select name="promotable_id" id="promo_promotable_id" class="form-control select2" required disabled style="width: 100%;">
                        <option value="">-- Seleccione Nivel Primero --</option>
                    </select>
                    
                    <!-- Hidden Master Select for Select2 Filtering -->
                    <select id="master_promotable_options" style="display:none;">
                        <!-- Productos -->
                        @foreach($products as $p)
                        <option value="{{ $p->id }}" data-type="App\Models\Product">{{ $p->name }}</option>
                        @endforeach
                        <!-- Categorias -->
                        @foreach($categories as $c)
                        <option value="{{ $c->id }}" data-type="App\Models\Category">{{ $c->name }}</option>
                        @endforeach
                        <!-- Departamentos -->
                        @foreach($departments as $d)
                        <option value="{{ $d->id }}" data-type="App\Models\Department">{{ $d->name }}</option>
                        @endforeach
                        <!-- Metodos -->
                        @foreach($paymentMethods as $pm)
                        <option value="{{ $pm->id }}" data-type="App\Models\PaymentMethod">{{ $pm->description }} ({{ $pm->code }})</option>
                        @endforeach
                        <!-- Monedas -->
                        @foreach($currencies as $curr)
                        <option value="{{ $curr->id }}" data-type="App\Models\Currency">{{ $curr->description }} ({{ $curr->code }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex gap-4 mb-4">
                <div class="form-group w-full" style="margin-bottom: 0;">
                    <label class="form-label">Fecha Inicio (Opcional)</label>
                    <input type="datetime-local" name="start_date" id="promo_start_date" class="form-control">
                </div>
                <div class="form-group w-full" style="margin-bottom: 0;">
                    <label class="form-label">Fecha Fin (Opcional)</label>
                    <input type="datetime-local" name="end_date" id="promo_end_date" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="active" id="promo_active" value="1" checked style="width: 1.2rem; height: 1.2rem;">
                    Promoción Activa
                </label>
            </div>

            <button type="submit" class="btn btn-primary w-full"><i class="fa-solid fa-save"></i> Guardar Promoción</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#promoModal .select2').select2({
            dropdownParent: $('#promoModal'),
            width: '100%'
        });

        flatpickr("#promo_start_date", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            time_24hr: true
        });

        flatpickr("#promo_end_date", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            time_24hr: true
        });

        // Initialize Select2 change event trigger for promotable_type
        $('#promo_promotable_type').on('change', function() {
            filterTargets();
        });
    });

    function filterTargets(selectedId = null) {
        const type = document.getElementById('promo_promotable_type').value;
        const targetSelect = $('#promo_promotable_id');
        const masterSelect = document.getElementById('master_promotable_options');
        
        // Destruir select2 antes de modificar el DOM
        if (targetSelect.hasClass("select2-hidden-accessible")) {
            targetSelect.select2('destroy');
        }
        targetSelect.empty();

        if (!type) {
            targetSelect.prop('disabled', true);
            targetSelect.append('<option value="">-- Seleccione Nivel Primero --</option>');
            targetSelect.select2({ dropdownParent: $('#promoModal'), width: '100%' });
            return;
        }

        targetSelect.prop('disabled', false);
        const options = masterSelect.querySelectorAll(`option[data-type="${type.replace(/\\/g, '\\\\')}"]`);
        
        let firstMatch = null;
        options.forEach(opt => {
            // Usamos textContent porque opt.text puede estar vacío si el elemento padre tiene display:none
            targetSelect.append(`<option value="${opt.value}">${opt.textContent}</option>`);
            if (!firstMatch) firstMatch = opt.value;
        });

        if (selectedId) {
            targetSelect.val(selectedId);
        } else {
            targetSelect.val(firstMatch || "");
        }
        
        // Reinicializar select2
        targetSelect.select2({ dropdownParent: $('#promoModal'), width: '100%' });
    }

    function openPromoModal(mode) {
        const form = document.getElementById('promoForm');
        const method = document.getElementById('promoMethod');
        const title = document.getElementById('promoModalTitle');

        if (mode === 'create') {
            form.action = '{{ route("promotions.store") }}';
            method.value = 'POST';
            title.innerText = 'Nueva Promoción';
            form.reset();
            document.getElementById('promo_active').checked = true;
            filterTargets();
        }
        openModal('promoModal');
    }

    function editPromo(id, name, discountType, discountValue, promotableType, promotableId, startDate, endDate, active) {
        openPromoModal('edit');
        
        const form = document.getElementById('promoForm');
        form.action = '{{ url("promotions") }}/' + id;
        document.getElementById('promoMethod').value = 'PUT';
        document.getElementById('promoModalTitle').innerText = 'Editar Promoción';

        document.getElementById('promo_name').value = name;
        $('#promo_discount_type').val(discountType).trigger('change');
        $('#promo_promotable_type').val(promotableType).trigger('change');
        
        filterTargets(promotableId);

        document.getElementById('promo_start_date').value = startDate;
        document.getElementById('promo_end_date').value = endDate;
        document.getElementById('promo_active').checked = active == 1;
    }
</script>
@endpush
