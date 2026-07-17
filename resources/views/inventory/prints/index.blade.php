@extends('layouts.app')
@section('title', 'Impresiones y Habladores')

@push('styles')
<style>
    .print-options { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; background: var(--background); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border); }
    .option-group { display: flex; flex-direction: column; gap: 8px; }
    .option-label { font-weight: 600; font-size: 0.9rem; color: var(--text-main); }
    
    .qty-input { width: 80px; text-align: center; }
    
    /* Select2 customizations to match theme */
    .select2-container .select2-selection--single { height: 48px; border-radius: 12px; border: 1px solid var(--border); background: var(--background); display: flex; align-items: center; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { color: var(--text-main); line-height: 48px; padding-left: 45px !important; }
    .select2-container--default .select2-selection--single .select2-selection__placeholder { color: var(--text-muted); }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 46px; right: 15px; }
    .select2-dropdown { border: 1px solid var(--border); border-radius: 12px; box-shadow: var(--shadow-lg); background: var(--background); overflow: hidden; }
    .select2-container--default .select2-results__option--highlighted[aria-selected] { background-color: var(--primary); color: white; }
    .select2-results__option { padding: 10px 15px; }
    .select2-search--dropdown .select2-search__field { border-radius: 8px; border: 1px solid var(--border); padding: 8px 12px; outline: none; }
    .select2-search--dropdown .select2-search__field:focus { border-color: var(--primary); }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="page-title"><i class="fa-solid fa-print" style="color:var(--primary); margin-right:10px;"></i> Impresiones y Habladores</h1>
            <p class="text-muted mt-2">Genera etiquetas adhesivas o habladores para los productos.</p>
        </div>
    </div>
</div>

<div class="content-wrapper">
    <div class="card" style="padding: 2rem;">
        
        <div class="form-group mb-4" style="position: relative;">
            <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 20px; top: 50%; transform: translateY(-50%); z-index: 10; color: var(--text-muted);"></i>
            <select id="productSelect" class="form-control" style="width: 100%;"></select>
        </div>

        <form id="printForm" action="{{ route('inventory.prints.generate') }}" method="POST" target="_blank">
            @csrf
            
            <div class="print-options">
                <div class="option-group">
                    <label class="option-label">Tipo de Impresión</label>
                    <select name="type" id="printType" class="form-control" onchange="toggleOptions()">
                        <option value="labels">Etiquetas Adhesivas (Zebra)</option>
                        <option value="talkers">Habladores (Hojas A4)</option>
                    </select>
                </div>

                <div class="option-group">
                    <label class="option-label">Código a Imprimir</label>
                    <select name="code_type" class="form-control">
                        <option value="ean">Código de Barras (EAN)</option>
                        <option value="private">Código Interno</option>
                    </select>
                </div>

                <div class="option-group" id="labelsOptions">
                    <label class="option-label">Columnas (Rollos)</label>
                    <select name="columns" class="form-control">
                        <option value="2">2 Columnas (74mm total)</option>
                        <option value="1">1 Columna</option>
                    </select>
                </div>

                <div class="option-group" id="talkersOptions" style="display: none;">
                    <label class="option-label">Tamaño</label>
                    <select name="size" class="form-control">
                        <option value="small">Pequeños / Horizontales</option>
                        <option value="large">Grandes</option>
                    </select>
                </div>
            </div>

            <div class="table-container">
                <table class="table" id="printTable">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Código Barras</th>
                            <th>Código Int.</th>
                            <th>Precio (USD)</th>
                            <th width="100">Cantidad</th>
                            <th width="80" class="text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr id="emptyRow">
                            <td colspan="6" class="text-center text-muted" style="padding: 2rem;">No has agregado productos a la cola de impresión.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <input type="hidden" name="items" id="itemsPayload">

            <div class="flex justify-end mt-4">
                <button type="button" class="btn btn-primary" onclick="generatePrint()"><i class="fa-solid fa-print"></i> Generar Impresión</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    let printItems = [];

    function toggleOptions() {
        const type = document.getElementById('printType').value;
        if (type === 'labels') {
            document.getElementById('labelsOptions').style.display = 'flex';
            document.getElementById('talkersOptions').style.display = 'none';
        } else {
            document.getElementById('labelsOptions').style.display = 'none';
            document.getElementById('talkersOptions').style.display = 'flex';
        }
    }

    // Select2 Initialization
    $(document).ready(function() {
        $('#productSelect').select2({
            placeholder: 'Buscar producto por nombre, código de barras o código interno...',
            ajax: {
                url: '{{ route("inventory.prints.search") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    return {
                        results: data.map(function(item) {
                            return { id: item.id, text: item.name, item: item };
                        })
                    };
                },
                cache: true
            },
            templateResult: function(repo) {
                if (repo.loading) return repo.text;
                return $(
                    "<div>" +
                        "<div style='font-weight:600; font-size:0.95rem; margin-bottom:2px;'>" + repo.text + "</div>" +
                        "<div style='font-size:0.8rem; color:var(--text-muted); opacity:0.8;'>EAN: " + (repo.item.ean_code || '-') + " | Int: " + (repo.item.private_code || '-') + "</div>" +
                    "</div>"
                );
            },
            templateSelection: function(repo) { return repo.text || repo.id; },
            escapeMarkup: function(m) { return m; } // let our custom formatter work
        });

        $('#productSelect').on('select2:select', function (e) {
            addItemToQueue(e.params.data.item);
            $(this).val(null).trigger('change'); // reset selection
        });
    });

    function addItemToQueue(item) {
        const existing = printItems.find(i => i.id === item.id);
        if (existing) {
            existing.qty += 1;
        } else {
            printItems.push({
                id: item.id,
                name: item.name,
                ean: item.ean_code,
                private: item.private_code,
                price: item.price_usd,
                qty: 1
            });
        }
        renderTable();
    }

    function removeItem(id) {
        printItems = printItems.filter(i => i.id !== id);
        renderTable();
    }

    function updateQty(id, qty) {
        const item = printItems.find(i => i.id === id);
        if (item) {
            item.qty = Math.max(1, parseInt(qty) || 1);
        }
        renderTable();
    }

    function renderTable() {
        const tbody = document.querySelector('#printTable tbody');
        if (printItems.length === 0) {
            tbody.innerHTML = `<tr id="emptyRow"><td colspan="6" class="text-center text-muted" style="padding: 2rem;">No has agregado productos a la cola de impresión.</td></tr>`;
            return;
        }

        let html = '';
        printItems.forEach(item => {
            html += `
                <tr>
                    <td><strong>${item.name}</strong></td>
                    <td>${item.ean || '-'}</td>
                    <td>${item.private || '-'}</td>
                    <td>$${parseFloat(item.price).toFixed(2)}</td>
                    <td>
                        <input type="number" class="form-control qty-input" value="${item.qty}" min="1" onchange="updateQty(${item.id}, this.value)">
                    </td>
                    <td>
                        <div class="table-actions justify-center">
                            <button type="button" class="btn-icon btn-icon-danger" onclick="removeItem(${item.id})" title="Remover"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }

    function generatePrint() {
        if (printItems.length === 0) {
            showToast("Agrega al menos un producto a la cola.");
            return;
        }
        document.getElementById('itemsPayload').value = JSON.stringify(printItems);
        document.getElementById('printForm').submit();
    }
</script>
@endpush
@endsection
