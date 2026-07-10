@extends('layouts.app')

@section('title', 'Monedas y Métodos de Pago')

@push('styles')
<style>
    .finances-layout {
        display: flex;
        gap: 2rem;
        height: calc(100vh - 180px);
        min-height: 600px;
    }
    
    .finances-sidebar {
        width: 350px;
        background: var(--surface);
        border-radius: 16px;
        box-shadow: var(--shadow-md);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        flex-shrink: 0;
    }
    
    .finances-sidebar-header {
        padding: 1.5rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--primary-light);
    }
    .finances-sidebar-header h3 {
        color: var(--primary);
        font-weight: 800;
        margin: 0;
        font-size: 1.1rem;
    }
    
    .tree-container {
        flex: 1;
        overflow-y: auto;
        padding: 1rem;
    }
    
    .tree-list {
        list-style: none;
        padding-left: 0;
        margin: 0;
    }
    
    .tree-list ul {
        list-style: none;
        padding-left: 1.5rem;
        border-left: 1px dashed var(--border);
        margin-left: 0.75rem;
        margin-top: 0.25rem;
    }
    
    .tree-item {
        margin-bottom: 0.25rem;
    }
    
    .tree-node {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 0.5rem;
        border-radius: 8px;
        cursor: pointer;
        transition: var(--transition);
        user-select: none;
    }
    
    .tree-node:hover {
        background: var(--background);
    }
    
    .tree-node.active {
        background: var(--primary);
        color: white;
    }
    .tree-node.active i {
        color: white;
    }
    
    .tree-node i {
        width: 20px;
        text-align: center;
        color: var(--text-muted);
        transition: var(--transition);
    }
    
    .tree-node-text {
        font-weight: 600;
        font-size: 0.95rem;
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .node-actions {
        display: none;
        gap: 5px;
    }
    .tree-node:hover .node-actions, .tree-node.active .node-actions {
        display: flex;
    }
    
    .btn-icon {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        background: var(--surface);
        color: var(--text-muted);
        transition: var(--transition);
    }
    .tree-node.active .btn-icon {
        background: rgba(255,255,255,0.2);
        color: white;
    }
    .btn-icon:hover { background: var(--primary); color: white; }
    .btn-icon.delete:hover { background: var(--danger); color: white; }
    
    .finances-content {
        flex: 1;
        background: var(--surface);
        border-radius: 16px;
        box-shadow: var(--shadow-md);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    
    .form-header {
        padding: 1.5rem 2rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .form-header h2 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--text-main);
    }
    
    .form-body {
        padding: 2rem;
        flex: 1;
        overflow-y: auto;
    }
    
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
    .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
    .grid-4 { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
    
    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        font-size: 0.9rem;
        font-weight: 500;
        color: var(--text-main);
    }
    
    .options-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        background: var(--background);
        padding: 1.5rem;
        border-radius: 12px;
        border: 1px solid var(--border);
    }
    .options-title {
        grid-column: span 2;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 0.5rem;
        border-bottom: 1px solid var(--border);
        padding-bottom: 0.5rem;
    }
    
    /* UI Tweaks for contrast and alignment */
    .form-control {
        border: 1px solid #ced4da;
        background-color: #ffffff;
        color: #495057;
    }
    body.dark-mode .form-control {
        border: 1px solid rgba(255,255,255,0.1);
        background-color: rgba(0,0,0,0.2);
        color: #f8f9fa;
    }
    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb), 0.25);
    }
    
    .form-header > div {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    #empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: var(--text-muted);
        text-align: center;
        opacity: 0.7;
    }
    #empty-state i { 
        font-size: 4.5rem; 
        margin-bottom: 1.5rem; 
        color: var(--border); 
    }
    #empty-state h3 { 
        color: var(--text-main); 
        font-weight: 700; 
        font-size: 1.4rem;
        margin-bottom: 0.5rem;
    }
    #empty-state p {
        font-size: 0.95rem;
        max-width: 300px;
        margin: 0 auto;
    }
    
    /* Loading overlay */
    .loading-overlay {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(255,255,255,0.7);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 10;
        backdrop-filter: blur(2px);
    }
    body.dark-mode .loading-overlay { background: rgba(15,23,42,0.7); }
    .finances-layout { position: relative; }
</style>
@endpush

@section('content')
<div class="finances-layout" id="financesApp">
    <div class="loading-overlay" id="globalLoader">
        <i class="fa-solid fa-circle-notch fa-spin fa-3x" style="color: var(--primary);"></i>
    </div>

    <!-- Sidebar / Tree -->
    <div class="finances-sidebar">
        <div class="finances-sidebar-header">
            <h3><i class="fa-solid fa-coins"></i> MONEDAS</h3>
            <button class="btn btn-primary" onclick="showCurrencyForm()" title="Nueva Moneda" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                <i class="fa-solid fa-plus"></i> Crear
            </button>
        </div>
        <div class="tree-container">
            <ul class="tree-list" id="treeRoot">
                <!-- Tree will be populated via JS -->
            </ul>
        </div>
    </div>
    
    <!-- Content / Forms -->
    <div class="finances-content">
        <!-- Empty State -->
        <div id="empty-state">
            <i class="fa-solid fa-coins"></i>
            <h3>Selecciona un elemento</h3>
            <p>Haz clic en una moneda o denominación a la izquierda para ver y editar sus detalles.</p>
        </div>
        
        <!-- Currency Form -->
        <div id="currency-form-container" style="display: none; height: 100%; flex-direction: column;">
            <div class="form-header">
                <h2 id="currencyFormTitle">Ficha de Moneda</h2>
                <div>
                    <button class="btn btn-danger" id="btnDeleteCurrency" style="display: none;" onclick="deleteCurrency()">
                        <i class="fa-solid fa-trash"></i> Eliminar
                    </button>
                    <button class="btn btn-primary" onclick="saveCurrency()">
                        <i class="fa-solid fa-save"></i> Guardar
                    </button>
                </div>
            </div>
            <div class="form-body">
                <form id="currencyForm">
                    <input type="hidden" id="c_id">
                    
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Código</label>
                            <input type="text" id="c_code" class="form-control" placeholder="Ej: VED, USD" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Descripción</label>
                            <input type="text" id="c_description" class="form-control" placeholder="Ej: Bolívar Digital" required>
                        </div>
                    </div>
                    
                    <div class="grid-4">
                        <div class="form-group">
                            <label class="form-label">Símbolo</label>
                            <input type="text" id="c_symbol" class="form-control" placeholder="Ej: Bs, $">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Max. Nº Decimales</label>
                            <input type="number" id="c_max_decimals" class="form-control" value="2" min="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Factor (Tasa)</label>
                            <input type="number" step="0.0001" id="c_exchange_rate" class="form-control" value="1.0000" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Código ISO</label>
                            <input type="text" id="c_iso_code" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Observación</label>
                        <input type="text" id="c_observation" class="form-control">
                    </div>
                    
                    <div class="options-grid" style="margin-top: 1rem;">
                        <div class="options-title">Configuraciones de la Moneda</div>
                        <label class="checkbox-group">
                            <input type="checkbox" id="c_is_default"> Predeterminada (Moneda Base)
                        </label>
                        <label class="checkbox-group">
                            <input type="checkbox" id="c_is_active" checked> Activa
                        </label>
                        <label class="checkbox-group">
                            <input type="checkbox" id="c_used_in_pos" checked> Usado en POS
                        </label>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Payment Method Form -->
        <div id="pm-form-container" style="display: none; height: 100%; flex-direction: column;">
            <div class="form-header">
                <h2 id="pmFormTitle">Ficha de Denominación / Método</h2>
                <div>
                    <button class="btn btn-danger" id="btnDeletePM" style="display: none;" onclick="deletePM()">
                        <i class="fa-solid fa-trash"></i> Eliminar
                    </button>
                    <button class="btn btn-primary" onclick="savePM()">
                        <i class="fa-solid fa-save"></i> Guardar
                    </button>
                </div>
            </div>
            <div class="form-body">
                <form id="pmForm">
                    <input type="hidden" id="pm_id">
                    <input type="hidden" id="pm_currency_id">
                    
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Código</label>
                            <input type="text" id="pm_code" class="form-control" placeholder="Ej: 01, ZELLE" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Descripción</label>
                            <input type="text" id="pm_description" class="form-control" placeholder="Ej: BILLETE DE 10, ZELLE" required>
                        </div>
                    </div>
                    
                    <div class="grid-3">
                        <div class="form-group">
                            <label class="form-label">Valor Nominal (Opcional)</label>
                            <input type="number" step="0.01" id="pm_value" class="form-control" placeholder="0.00">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Monto máx. para Vuelto</label>
                            <input type="number" step="0.01" id="pm_max_change_amount" class="form-control" value="0.00">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Monto mín. de Compra</label>
                            <input type="number" step="0.01" id="pm_min_purchase_amount" class="form-control" value="0.00">
                        </div>
                    </div>
                    
                    <div class="options-grid">
                        <div class="options-title">Opciones adicionales</div>
                        <label class="checkbox-group"><input type="checkbox" id="pm_is_real_denomination"> Denominación Real</label>
                        <label class="checkbox-group"><input type="checkbox" id="pm_admin_serial"> Administra Serial</label>
                        <label class="checkbox-group"><input type="checkbox" id="pm_allows_change"> Permite Vuelto</label>
                        <label class="checkbox-group"><input type="checkbox" id="pm_auto_declare"> Auto Declarar (POS)</label>
                        <label class="checkbox-group"><input type="checkbox" id="pm_used_in_pos" checked> Usado en POS</label>
                        <label class="checkbox-group"><input type="checkbox" id="pm_auto_deposit"> Auto Depositar (POS)</label>
                        <label class="checkbox-group"><input type="checkbox" id="pm_electronic_verification"> Verificación Electrónica</label>
                        <label class="checkbox-group"><input type="checkbox" id="pm_used_in_admin_billing"> Usado en Fact. Adm.</label>
                        <label class="checkbox-group"><input type="checkbox" id="pm_cash_advance"> Avance de Efectivo</label>
                    </div>
                </form>
            </div>
        </div>
        
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currenciesData = [];
    let currentCurrencyId = null;
    let currentPmId = null;
    let activeNode = null;

    // UI State Management
    function showLoader() { document.getElementById('globalLoader').style.display = 'flex'; }
    function hideLoader() { document.getElementById('globalLoader').style.display = 'none'; }
    
    function resetForms() {
        document.getElementById('empty-state').style.display = 'none';
        document.getElementById('currency-form-container').style.display = 'none';
        document.getElementById('pm-form-container').style.display = 'none';
    }
    
    function setActiveNode(element) {
        if(activeNode) activeNode.classList.remove('active');
        element.classList.add('active');
        activeNode = element;
    }

    // API Calls
    async function loadData() {
        showLoader();
        try {
            const res = await fetch('{{ url("api/currencies") }}');
            currenciesData = await res.json();
            renderTree();
            
            // Re-select if needed
            if (currentCurrencyId) {
                const cNode = document.getElementById('node-c-' + currentCurrencyId);
                if (cNode) {
                    if (currentPmId) {
                        const pmNode = document.getElementById('node-pm-' + currentPmId);
                        if (pmNode) {
                            pmNode.click();
                        } else {
                            cNode.click();
                        }
                    } else {
                        cNode.click();
                    }
                }
            } else {
                document.getElementById('empty-state').style.display = 'flex';
            }
        } catch (e) {
            console.error(e);
            alert("Error cargando datos.");
        }
        hideLoader();
    }

    // Render Tree
    function renderTree() {
        const root = document.getElementById('treeRoot');
        root.innerHTML = '';
        
        currenciesData.forEach(c => {
            const li = document.createElement('li');
            li.className = 'tree-item';
            
            const hasChildren = c.payment_methods && c.payment_methods.length > 0;
            const chevron = hasChildren ? `<i class="fa-solid fa-chevron-right chevron-icon" style="transition: transform 0.2s; font-size: 0.8rem; margin-right: 4px;"></i>` : `<i class="fa-solid fa-minus" style="font-size: 0.8rem; margin-right: 4px; opacity: 0.3;"></i>`;
            
            // Currency Node
            const cNode = document.createElement('div');
            cNode.className = 'tree-node';
            cNode.id = 'node-c-' + c.id;
            cNode.innerHTML = `
                ${chevron}
                <i class="fa-solid fa-coins"></i>
                <span class="tree-node-text">${c.description} (${c.code})</span>
                <div class="node-actions">
                    <button class="btn-icon" onclick="event.stopPropagation(); showPMForm(${c.id})" title="Añadir Método/Denominación"><i class="fa-solid fa-plus"></i></button>
                </div>
            `;
            
            let ul = null;
            if (hasChildren) {
                ul = document.createElement('ul');
                ul.style.display = 'none'; // Collapse by default
                c.payment_methods.forEach(pm => {
                    const pmLi = document.createElement('li');
                    pmLi.className = 'tree-item';
                    const pmNode = document.createElement('div');
                    pmNode.className = 'tree-node';
                    pmNode.id = 'node-pm-' + pm.id;
                    pmNode.innerHTML = `
                        <i class="fa-regular fa-money-bill-1"></i>
                        <span class="tree-node-text">${pm.description}</span>
                    `;
                    pmNode.onclick = (e) => {
                        e.stopPropagation();
                        setActiveNode(pmNode);
                        editPM(pm, c.id);
                    };
                    pmLi.appendChild(pmNode);
                    ul.appendChild(pmLi);
                });
            }
            
            cNode.onclick = () => {
                if (hasChildren && ul) {
                    const isExpanded = ul.style.display === 'block';
                    ul.style.display = isExpanded ? 'none' : 'block';
                    const icon = cNode.querySelector('.chevron-icon');
                    if (icon) icon.style.transform = isExpanded ? 'rotate(0deg)' : 'rotate(90deg)';
                }
                
                setActiveNode(cNode);
                editCurrency(c);
            };
            li.appendChild(cNode);
            
            if (hasChildren && ul) {
                li.appendChild(ul);
            }
            
            root.appendChild(li);
        });
    }

    // Forms Logic
    function showCurrencyForm() {
        resetForms();
        document.getElementById('currency-form-container').style.display = 'flex';
        document.getElementById('currencyForm').reset();
        document.getElementById('c_id').value = '';
        document.getElementById('currencyFormTitle').innerText = 'Nueva Moneda';
        document.getElementById('btnDeleteCurrency').style.display = 'none';
        
        if(activeNode) activeNode.classList.remove('active');
        activeNode = null;
        currentCurrencyId = null;
        currentPmId = null;
    }

    function editCurrency(c) {
        resetForms();
        document.getElementById('currency-form-container').style.display = 'flex';
        document.getElementById('currencyFormTitle').innerText = 'Ficha de Moneda';
        document.getElementById('btnDeleteCurrency').style.display = 'block';
        
        document.getElementById('c_id').value = c.id;
        document.getElementById('c_code').value = c.code || '';
        document.getElementById('c_description').value = c.description || '';
        document.getElementById('c_symbol').value = c.symbol || '';
        document.getElementById('c_max_decimals').value = c.max_decimals || 2;
        document.getElementById('c_exchange_rate').value = parseFloat(c.exchange_rate).toFixed(4) || '1.0000';
        document.getElementById('c_iso_code').value = c.iso_code || '';
        document.getElementById('c_observation').value = c.observation || '';
        
        document.getElementById('c_is_default').checked = c.is_default;
        document.getElementById('c_is_active').checked = c.is_active;
        document.getElementById('c_used_in_pos').checked = c.used_in_pos;
        
        currentCurrencyId = c.id;
        currentPmId = null;
    }

    function showPMForm(currencyId) {
        resetForms();
        document.getElementById('pm-form-container').style.display = 'flex';
        document.getElementById('pmForm').reset();
        document.getElementById('pm_id').value = '';
        document.getElementById('pm_currency_id').value = currencyId;
        document.getElementById('pmFormTitle').innerText = 'Nuevo Método/Denominación';
        document.getElementById('btnDeletePM').style.display = 'none';
        
        currentCurrencyId = currencyId;
        currentPmId = null;
    }

    function editPM(pm, currencyId) {
        resetForms();
        document.getElementById('pm-form-container').style.display = 'flex';
        document.getElementById('pmFormTitle').innerText = 'Ficha de Denominación / Método';
        document.getElementById('btnDeletePM').style.display = 'block';
        
        document.getElementById('pm_id').value = pm.id;
        document.getElementById('pm_currency_id').value = currencyId;
        
        document.getElementById('pm_code').value = pm.code || '';
        document.getElementById('pm_description').value = pm.description || '';
        document.getElementById('pm_value').value = pm.value ? parseFloat(pm.value).toFixed(2) : '';
        document.getElementById('pm_max_change_amount').value = pm.max_change_amount ? parseFloat(pm.max_change_amount).toFixed(2) : '0.00';
        document.getElementById('pm_min_purchase_amount').value = pm.min_purchase_amount ? parseFloat(pm.min_purchase_amount).toFixed(2) : '0.00';
        
        document.getElementById('pm_is_real_denomination').checked = pm.is_real_denomination;
        document.getElementById('pm_allows_change').checked = pm.allows_change;
        document.getElementById('pm_used_in_pos').checked = pm.used_in_pos;
        document.getElementById('pm_electronic_verification').checked = pm.electronic_verification;
        document.getElementById('pm_cash_advance').checked = pm.cash_advance;
        document.getElementById('pm_admin_serial').checked = pm.admin_serial;
        document.getElementById('pm_auto_declare').checked = pm.auto_declare;
        document.getElementById('pm_auto_deposit').checked = pm.auto_deposit;
        document.getElementById('pm_used_in_admin_billing').checked = pm.used_in_admin_billing;
        
        currentCurrencyId = currencyId;
        currentPmId = pm.id;
    }

    // API Interactions
    async function saveCurrency() {
        if(!document.getElementById('currencyForm').checkValidity()) {
            document.getElementById('currencyForm').reportValidity();
            return;
        }
        
        showLoader();
        const id = document.getElementById('c_id').value;
        const data = {
            code: document.getElementById('c_code').value,
            description: document.getElementById('c_description').value,
            symbol: document.getElementById('c_symbol').value,
            max_decimals: document.getElementById('c_max_decimals').value,
            exchange_rate: document.getElementById('c_exchange_rate').value,
            iso_code: document.getElementById('c_iso_code').value,
            observation: document.getElementById('c_observation').value,
            is_default: document.getElementById('c_is_default').checked ? 1 : 0,
            is_active: document.getElementById('c_is_active').checked ? 1 : 0,
            used_in_pos: document.getElementById('c_used_in_pos').checked ? 1 : 0,
            _token: '{{ csrf_token() }}'
        };
        
        try {
            const url = id ? '{{ url("api/currencies") }}/' + id : '{{ url("api/currencies") }}';
            if (id) data._method = 'PUT';
            
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(data)
            });
            const json = await res.json();
            if(json.success) {
                currentCurrencyId = json.currency.id;
                await loadData();
            } else {
                showToast(json.message || "Error al guardar");
            }
        } catch (e) {
            console.error(e);
            showToast("Error de conexión");
        }
        hideLoader();
    }

    async function savePM() {
        if(!document.getElementById('pmForm').checkValidity()) {
            document.getElementById('pmForm').reportValidity();
            return;
        }
        
        showLoader();
        const id = document.getElementById('pm_id').value;
        const data = {
            currency_id: document.getElementById('pm_currency_id').value,
            code: document.getElementById('pm_code').value,
            description: document.getElementById('pm_description').value,
            value: document.getElementById('pm_value').value || null,
            max_change_amount: document.getElementById('pm_max_change_amount').value || 0,
            min_purchase_amount: document.getElementById('pm_min_purchase_amount').value || 0,
            
            is_real_denomination: document.getElementById('pm_is_real_denomination').checked ? 1 : 0,
            allows_change: document.getElementById('pm_allows_change').checked ? 1 : 0,
            used_in_pos: document.getElementById('pm_used_in_pos').checked ? 1 : 0,
            electronic_verification: document.getElementById('pm_electronic_verification').checked ? 1 : 0,
            cash_advance: document.getElementById('pm_cash_advance').checked ? 1 : 0,
            admin_serial: document.getElementById('pm_admin_serial').checked ? 1 : 0,
            auto_declare: document.getElementById('pm_auto_declare').checked ? 1 : 0,
            auto_deposit: document.getElementById('pm_auto_deposit').checked ? 1 : 0,
            used_in_admin_billing: document.getElementById('pm_used_in_admin_billing').checked ? 1 : 0,
            _token: '{{ csrf_token() }}'
        };
        
        try {
            const url = id ? '{{ url("api/payment-methods") }}/' + id : '{{ url("api/payment-methods") }}';
            if (id) data._method = 'PUT';
            
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(data)
            });
            const json = await res.json();
            if(json.success) {
                currentPmId = json.payment_method.id;
                await loadData();
            } else {
                alert(json.message || "Error al guardar");
            }
        } catch (e) {
            console.error(e);
            alert("Error de conexión");
        }
        hideLoader();
    }

    async function deleteCurrency() {
        if(!confirm("¿Estás seguro de eliminar esta moneda? Se eliminarán también sus denominaciones.")) return;
        showLoader();
        try {
            const res = await fetch('{{ url("api/currencies") }}/' + currentCurrencyId, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            const json = await res.json();
            if(json.success) {
                currentCurrencyId = null;
                currentPmId = null;
                resetForms();
                document.getElementById('empty-state').style.display = 'flex';
                await loadData();
            }
        } catch (e) { console.error(e); }
        hideLoader();
    }

    async function deletePM() {
        if(!confirm("¿Estás seguro de eliminar esta denominación?")) return;
        showLoader();
        try {
            const res = await fetch('{{ url("api/payment-methods") }}/' + currentPmId, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            const json = await res.json();
            if(json.success) {
                currentPmId = null;
                await loadData();
            }
        } catch (e) { console.error(e); }
        hideLoader();
    }

    // Init
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('empty-state').style.display = 'flex';
        loadData();
    });
</script>
@endpush
