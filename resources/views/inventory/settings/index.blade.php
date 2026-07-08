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

        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Guardar Cambios</button>
    </form>
</div>
@endsection
