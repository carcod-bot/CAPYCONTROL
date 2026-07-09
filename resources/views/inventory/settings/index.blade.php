@extends('layouts.app')
@section('title', 'Configuración de Inventario')

@section('content')
<div class="card" style="max-width: 600px;">
    <h3>Generación de Código Privado</h3>
    <p class="text-muted mb-4">Configura cómo se generan los códigos privados para los nuevos productos.</p>
    
    <form action="{{ route('settings.update') }}" method="POST" onsubmit="event.preventDefault(); submitAjaxForm(this, this.action, () => { alert('Configuración guardada exitosamente'); })">
        @csrf
        
        <div class="form-group">
            <label class="form-label">Modo de Generación</label>
            <select name="private_code_mode" class="form-control">
                <option value="incremental" {{ $settings['private_code_mode'] == 'incremental' ? 'selected' : '' }}>Incremental Automático</option>
                <option value="personalizado" {{ $settings['private_code_mode'] == 'personalizado' ? 'selected' : '' }}>Personalizado (Manual)</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Número Inicial (si es incremental)</label>
            <input type="number" name="private_code_start" class="form-control" min="1" value="{{ $settings['private_code_start'] }}" required>
            <small class="text-muted">Por ejemplo, si pones 3001, el próximo producto será 3001, luego 3002, etc.</small>
        </div>

        <hr style="margin: 2rem 0; border-color: var(--border);">
        
        <h3>Configuración de IVA (Impuestos)</h3>
        <p class="text-muted mb-4">Configura cómo se calculan los impuestos en el Punto de Venta (CapyPOS).</p>

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

        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Guardar Cambios</button>
    </form>
</div>
@endsection
