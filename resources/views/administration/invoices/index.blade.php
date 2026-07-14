@extends('layouts.app')

@section('title', 'Búsqueda de Facturas')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="card-title"><i class="fa-solid fa-file-invoice"></i> Búsqueda Avanzada de Facturas</h2>
    </div>
    
    <div class="card-body">
        <form method="GET" action="{{ route('admin.invoices.index') }}" class="mb-4" style="background: var(--surface); padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border);">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                
                <div class="form-group">
                    <label class="form-label">Desde Fecha</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Hasta Fecha</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Caja</label>
                    <select name="cash_register_id" class="form-select select2">
                        <option value="">Todas</option>
                        @foreach($registers as $register)
                            <option value="{{ $register->id }}" {{ request('cash_register_id') == $register->id ? 'selected' : '' }}>
                                {{ $register->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Cajero</label>
                    <select name="user_id" class="form-select select2">
                        <option value="">Todos</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->username }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Número de Ticket/Factura</label>
                    <input type="text" name="ticket_number" class="form-control" value="{{ request('ticket_number') }}" placeholder="Ej: TKT-00000001">
                </div>

                <div class="form-group">
                    <label class="form-label">RIF / Cédula Cliente</label>
                    <input type="text" name="customer_document" class="form-control" value="{{ request('customer_document') }}" placeholder="Ej: J-12345678">
                </div>

                <div class="form-group">
                    <label class="form-label">Producto (Nombre / Código)</label>
                    <input type="text" name="product_name" class="form-control" value="{{ request('product_name') }}" placeholder="Nombre, EAN o Interno">
                </div>

                <div class="form-group">
                    <label class="form-label">Nombre Cliente</label>
                    <input type="text" name="customer_name" class="form-control" value="{{ request('customer_name') }}" placeholder="Ej: Juan Perez">
                </div>

                <div class="form-group" style="display: flex; align-items: flex-end;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fa-solid fa-search"></i> Buscar Facturas
                    </button>
                </div>
                
            </div>
        </form>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Ticket Nº</th>
                        <th>Caja</th>
                        <th>Cajero</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr>
                        <td>{{ $invoice->created_at->format('d/m/Y H:i') }}</td>
                        <td class="font-bold">
                            {{ $invoice->ticket_number }}
                            @if($invoice->refund_parent_sale_id)
                                <i class="fa-solid fa-arrow-right-arrow-left text-warning" title="Generada por cambio/devolución"></i>
                            @endif
                        </td>
                        <td>{{ $invoice->cashSession->cashRegister->name ?? 'N/A' }}</td>
                        <td>{{ $invoice->cashSession->user->username ?? 'N/A' }}</td>
                        <td>
                            @if($invoice->customer)
                                {{ $invoice->customer->name }} <br>
                                <small class="text-muted">{{ $invoice->customer->document_id }}</small>
                            @else
                                <span class="text-muted">Consumidor Final</span>
                            @endif
                        </td>
                        <td class="font-bold">${{ number_format($invoice->total_amount, 2) }}</td>
                        <td>
                            @if($invoice->status === 'completed')
                                <span class="badge badge-success">Completada</span>
                            @elseif($invoice->status === 'voided')
                                <span class="badge" style="background: var(--danger); color: white;">Anulada</span>
                            @else
                                <span class="badge badge-closed">{{ ucfirst($invoice->status) }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-dropdown" id="dropdown-{{ $invoice->id }}">
                                <button class="action-dropdown-toggle" onclick="toggleActionDropdown('dropdown-{{ $invoice->id }}', event)">
                                    Acciones <i class="fa-solid fa-chevron-down"></i>
                                </button>
                                <div class="action-dropdown-menu">
                                    <button class="action-dropdown-item" onclick="viewInvoice({{ $invoice->id }})">
                                        <i class="fa-solid fa-eye"></i> Visualizar
                                    </button>
                                    <button class="action-dropdown-item" onclick="printInvoice({{ $invoice->id }})">
                                        <i class="fa-solid fa-print"></i> Imprimir
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 2rem;">
                            <i class="fa-solid fa-file-invoice fa-3x" style="color: var(--text-muted); margin-bottom: 1rem;"></i>
                            <p>No se encontraron facturas que coincidan con los criterios de búsqueda.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 1.5rem;">
            {{ $invoices->appends(request()->query())->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

<!-- Invoice Modal -->
<div id="invoiceModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 400px; padding: 2rem;">
        <div class="modal-header" style="border-bottom: none; padding-bottom: 0;">
            <button class="modal-close" onclick="closeModal('invoiceModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div id="invoiceModalBody" style="display: flex; justify-content: center;">
            <!-- Fetched content will go here -->
        </div>
        <div style="text-align: center; margin-top: 20px;">
            <button class="btn btn-secondary" onclick="closeModal('invoiceModal')">Cerrar</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({ width: '100%' });
        
        flatpickr("input[type='date']", {
            locale: "es",
            dateFormat: "Y-m-d",
            allowInput: true
        });
    });

    function toggleActionDropdown(id, event) {
        event.stopPropagation();
        const el = document.getElementById(id);
        const wasOpen = el.classList.contains('open');
        
        // Close all other dropdowns
        document.querySelectorAll('.action-dropdown').forEach(d => d.classList.remove('open'));
        
        if (!wasOpen) el.classList.add('open');
    }

    // Close when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.action-dropdown')) {
            document.querySelectorAll('.action-dropdown').forEach(d => d.classList.remove('open'));
        }
    });

    function viewInvoice(id) {
        const url = `{{ url('admin/invoices') }}/${id}`;
        
        document.getElementById('invoiceModalBody').innerHTML = '<div style="text-align:center; padding: 20px;"><i class="fa-solid fa-spinner fa-spin fa-2x"></i><br>Cargando factura...</div>';
        document.getElementById('invoiceModal').classList.add('open');

        fetch(url)
            .then(response => response.text())
            .then(html => {
                document.getElementById('invoiceModalBody').innerHTML = html;
            })
            .catch(error => {
                document.getElementById('invoiceModalBody').innerHTML = '<div style="text-align:center; color: var(--danger);"><i class="fa-solid fa-circle-exclamation fa-2x"></i><br>Error cargando la factura.</div>';
                console.error('Error fetching invoice:', error);
            });
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('open');
    }

    function printInvoice(id) {
        const printWindow = window.open(`{{ url('admin/invoices') }}/${id}`, '_blank', 'width=400,height=600');
        printWindow.onload = function() {
            printWindow.print();
        };
    }
</script>
@endpush
