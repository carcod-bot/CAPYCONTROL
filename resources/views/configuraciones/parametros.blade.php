@extends('layouts.app')
@section('title', 'Parámetros del Sistema')

@section('content')
<div class="content-header">
    <div class="header-title">
        <h1>Parámetros del Sistema</h1>
        <p class="text-muted">Configuraciones generales de CapyControl</p>
    </div>
</div>

<div class="card" style="max-width: 600px;">
    <h3>Configuración de IVA (Impuestos)</h3>
    <p class="text-muted mb-4">Configura cómo se calculan los impuestos en el Punto de Venta (CapyPOS).</p>
    
    <form action="{{ route('config.parametros.update') }}" method="POST" onsubmit="event.preventDefault(); submitAjaxForm(this, this.action, () => { Swal.fire({icon: 'success', title: 'Parámetros guardados exitosamente', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000}); })">
        @csrf
        
        <div class="form-group">
            <label class="form-label">Tipo de IVA</label>
            <select name="tax_type" class="form-control">
                <option value="percentage" {{ $settings['tax_type'] == 'percentage' ? 'selected' : '' }}>Porcentaje (%)</option>
                <option value="fixed" {{ $settings['tax_type'] == 'fixed' ? 'selected' : '' }}>Monto Fijo Fijo ($)</option>
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

        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Guardar Cambios</button>
    </form>
</div>
@endsection
