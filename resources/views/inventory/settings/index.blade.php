@extends('layouts.app')
@section('title', 'Configuración de Inventario')

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
            <h1 class="page-title"><i class="fa-solid fa-boxes-stacked" style="color:var(--primary); margin-right:10px;"></i> Configuración de Inventario</h1>
            <p class="text-muted mt-2">Ajustes de generación de códigos y lotes</p>
        </div>
    </div>
</div>

<div class="content-wrapper">
    <form action="{{ route('settings.update') }}" method="POST" onsubmit="event.preventDefault(); submitAjaxForm(this, this.action, () => { Swal.fire({icon: 'success', title: 'Configuración guardada exitosamente', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000}); })">
        @csrf
        
        <div class="tabs-container">
            <div class="tabs-header">
                <button type="button" class="tab-btn active" onclick="switchTab('codigos')"><i class="fa-solid fa-barcode"></i> Códigos de Producto</button>
                <button type="button" class="tab-btn" onclick="switchTab('lotes')"><i class="fa-solid fa-layer-group"></i> Lotes de Inventario</button>
            </div>

            <!-- TAB: CÓDIGOS -->
            <div id="tab-codigos" class="tab-pane active">
                <div class="card" style="max-width: 600px;">
                    <h3 style="font-size: 1.25rem; font-weight: 700;">Generación de Código Privado</h3>
                    <p class="text-muted mb-4">Configura cómo se generan los códigos privados para los nuevos productos.</p>
                    
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
                </div>
            </div>

            <!-- TAB: LOTES -->
            <div id="tab-lotes" class="tab-pane">
                <div class="card" style="max-width: 600px;">
                    <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 15px;">Configuración de Generación de Lotes</h3>
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
    function toggleBatchSettings() {
        const mode = document.getElementById('batch_generation_mode').value;
        const nextNumGroup = document.getElementById('batch_next_number_group');
        
        if (mode === 'sequential') {
            nextNumGroup.style.display = 'block';
        } else {
            nextNumGroup.style.display = 'none';
        }
    }

    function switchTab(tabId) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
        
        event.currentTarget.classList.add('active');
        document.getElementById('tab-' + tabId).classList.add('active');
    }
    
    // Ejecutar al inicio
    document.addEventListener('DOMContentLoaded', toggleBatchSettings);
</script>
@endpush
