@extends('layouts.app')
@section('title', 'Parámetros del Sistema')

@push('styles')
<style>
    .tabs-container { margin-bottom: 2rem; }
    .tabs-header { display: flex; border-bottom: 2px solid var(--border); gap: 2rem; }
    .tab-btn { background: none; border: none; padding: 1rem 0; font-size: 1rem; font-weight: 700; color: var(--text-muted); cursor: pointer; position: relative; transition: var(--transition); }
    .tab-btn:hover { color: var(--text-main); }
    .tab-btn.active { color: var(--primary); }
    .tab-btn.active::after { content: ''; position: absolute; bottom: -2px; left: 0; width: 100%; height: 2px; background: var(--primary); border-radius: 2px 2px 0 0; }
    .tab-pane { display: none; padding-top: 1.5rem; }
    .tab-pane.active { display: block; }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="page-title"><i class="fa-solid fa-cogs" style="color:var(--primary); margin-right:10px;"></i> Parámetros del Sistema</h1>
            <p class="text-muted mt-2">Configuraciones generales de CapyControl</p>
        </div>
    </div>
</div>

<div class="content-wrapper">
    <form action="{{ route('config.parametros.update') }}" method="POST" onsubmit="event.preventDefault(); submitAjaxForm(this, this.action, () => { Swal.fire({icon: 'success', title: 'Parámetros guardados exitosamente', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000}); })">
        @csrf
        
        <div class="tabs-container">
            <div class="tabs-header">
                <button type="button" class="tab-btn active" onclick="switchTab('impuestos')"><i class="fa-solid fa-file-invoice-dollar"></i> Impuestos (IVA)</button>
                <button type="button" class="tab-btn" onclick="switchTab('empresa')"><i class="fa-solid fa-building"></i> Empresa y Modalidad</button>
            </div>

            <!-- TAB: IMPUESTOS -->
            <div id="tab-impuestos" class="tab-pane active">
                <div class="card" style="max-width: 600px;">
                    <h3 style="font-size: 1.25rem; font-weight: 700;">Configuración de IVA</h3>
                    <p class="text-muted mb-4">Configura cómo se calculan los impuestos en el Punto de Venta (CapyPOS).</p>
                    
                    <div class="form-group">
                        <label class="form-label">Tipo de IVA</label>
                        <select name="tax_type" class="form-control">
                            <option value="percentage" {{ $settings['tax_type'] == 'percentage' ? 'selected' : '' }}>Porcentaje (%)</option>
                            <option value="fixed" {{ $settings['tax_type'] == 'fixed' ? 'selected' : '' }}>Monto Fijo ($)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Valor del IVA</label>
                        <input type="number" step="0.01" name="tax_amount" class="form-control" min="0" value="{{ $settings['tax_amount'] }}" required>
                        <small class="text-muted">Si es porcentaje, pon ej: 16 (para 16%). Si es fijo, pon el monto exacto (ej. 2.50).</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Cálculo de Precios e IVA (Punto de Venta)</label>
                        <select name="tax_included" class="form-control">
                            <option value="false" {{ $settings['tax_included'] == 'false' ? 'selected' : '' }}>Los precios registrados NO incluyen IVA (Se suma al cobrar)</option>
                            <option value="true" {{ $settings['tax_included'] == 'true' ? 'selected' : '' }}>Los precios registrados YA incluyen IVA (Se desglosa al cobrar)</option>
                        </select>
                        <small class="text-muted">Si eliges que ya lo incluyen, el POS no sumará el impuesto al final de la cuenta.</small>
                    </div>
                </div>
            </div>

            <!-- TAB: EMPRESA -->
            <div id="tab-empresa" class="tab-pane">
                <div class="card" style="max-width: 600px;">
                    <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 15px;">Datos de la Empresa y Modalidad</h3>
                    
                    <div class="form-group">
                        <label class="form-label">Nombre de la Empresa</label>
                        <input type="text" name="company_name" class="form-control" value="{{ $settings['company_name'] }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">RIF / Identificación Fiscal</label>
                        <input type="text" name="company_rif" class="form-control" value="{{ $settings['company_rif'] }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Ubicación</label>
                        <input type="text" name="company_location" class="form-control" value="{{ $settings['company_location'] }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Sucursal</label>
                        <input type="text" name="company_branch" class="form-control" value="{{ $settings['company_branch'] }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Modo de Facturación (Impresora Fiscal)</label>
                        <select name="is_fiscal" class="form-control">
                            <option value="true" {{ $settings['is_fiscal'] == 'true' ? 'selected' : '' }}>Sí, usar Impresora Fiscal (TFHKA)</option>
                            <option value="false" {{ $settings['is_fiscal'] == 'false' ? 'selected' : '' }}>No, usar Impresora Térmica Genérica o Ninguna (Omitir impresión fiscal)</option>
                        </select>
                        <small class="text-muted">Si seleccionas "No", el Punto de Venta registrará las facturas pero no intentará comunicarse con el puente local de impresión fiscal.</small>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Guardar Todos los Cambios</button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function switchTab(tabId) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
        
        event.currentTarget.classList.add('active');
        document.getElementById('tab-' + tabId).classList.add('active');
    }
</script>
@endpush
