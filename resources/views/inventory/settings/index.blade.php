@extends('layouts.app')
@section('title', 'Configuración de Inventario')

@section('content')
<div class="card" style="max-width: 600px;">
    <h3>Generación de Código Privado</h3>
    <p class="text-muted mb-4">Configura cómo se generan los códigos privados para los nuevos productos.</p>
    
    <form action="{{ route('settings.update') }}" method="POST" onsubmit="event.preventDefault(); submitAjaxForm(this, this.action, () => { showToast('Configuración guardada exitosamente', 'success'); })">
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
        
        <h3>Configuración de Generación de Lotes</h3>
        <p class="text-muted mb-4">Configura cómo se generarán los números de lote cuando no los especifiques manualmente.</p>

        <div class="form-group">
            <label class="form-label">Modo de Generación</label>
            <select name="batch_generation_mode" class="form-control" id="batch_generation_mode" onchange="toggleBatchSettings()">
                <option value="auto_date" {{ ($settings['batch_generation_mode'] ?? 'auto_date') == 'auto_date' ? 'selected' : '' }}>Autogenerado con Fecha (ej. LOTE-2026...)</option>
                <option value="sequential" {{ ($settings['batch_generation_mode'] ?? '') == 'sequential' ? 'selected' : '' }}>Secuencial Numérico (ej. LOTE-1, LOTE-2, o solo 1, 2...)</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Prefijo del Lote (Opcional)</label>
            <input type="text" name="default_batch_prefix" class="form-control" value="{{ $settings['default_batch_prefix'] ?? '' }}" placeholder="Ej: LOTE-, L-, o déjalo vacío">
            <small class="text-muted">Si lo dejas vacío e inicias en secuencial, los lotes serán puramente numéricos.</small>
        </div>

        <div class="form-group" id="batch_next_number_group">
            <label class="form-label">Próximo Número (Solo para Secuencial)</label>
            <input type="number" name="batch_next_number" class="form-control" value="{{ $settings['batch_next_number'] ?? '1' }}" min="1">
            <small class="text-muted">El número a partir del cual se autogenerarán los siguientes lotes.</small>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Guardar Cambios</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function toggleBatchSettings() {
        const mode = document.getElementById('batch_generation_mode').value;
        const nextNumGroup = document.getElementById('batch_next_number_group');
        
        if (mode === 'sequential') {
            nextNumGroup.style.display = 'block';
        } else {
            nextNumGroup.style.display = 'none';
        }
    }
    
    // Ejecutar al inicio
    document.addEventListener('DOMContentLoaded', toggleBatchSettings);
</script>
@endpush
