<style>
    .invoice-ticket {
        font-family: 'Courier New', Courier, monospace;
        font-size: 13px;
        line-height: 1.4;
        color: #111827;
        padding: 20px 18px;
        background: #ffffff;
        width: 100%;
        max-width: 360px;
        margin: 0 auto;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        box-sizing: border-box;
    }
    .invoice-ticket .ticket-header {
        text-align: center;
        margin-bottom: 16px;
    }
    .invoice-ticket .ticket-header h2 {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
        letter-spacing: -0.5px;
        color: #0f172a;
    }
    .invoice-ticket .ticket-header p {
        margin: 3px 0;
        color: #4b5563;
        font-size: 12px;
    }
    .invoice-ticket .ticket-section {
        margin-bottom: 14px;
        border-bottom: 1px dashed #cbd5e1;
        padding-bottom: 10px;
    }
    .invoice-ticket .ticket-section p {
        margin: 2px 0;
        font-size: 12px;
    }
    .invoice-ticket table {
        width: 100%;
        text-align: left;
        margin-bottom: 14px;
        border-collapse: collapse;
        font-size: 12px;
    }
    .invoice-ticket th {
        border-bottom: 1px dashed #94a3b8;
        padding-bottom: 5px;
        font-size: 11px;
        text-transform: uppercase;
        color: #334155;
    }
    .invoice-ticket td {
        padding: 5px 0;
        vertical-align: top;
    }
    .invoice-ticket .totals-section {
        border-top: 1px dashed #94a3b8;
        padding-top: 8px;
        margin-bottom: 12px;
    }
    .invoice-ticket .totals-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 4px;
        font-size: 12px;
    }
    .invoice-ticket .total-highlight {
        font-weight: 800;
        font-size: 15px;
        margin-top: 8px;
        padding-top: 6px;
        border-top: 1px solid #111827;
        color: #0f172a;
    }
    .invoice-ticket .payment-section {
        margin-bottom: 12px;
        padding-top: 4px;
        font-size: 12px;
    }
    .invoice-ticket .footer-note {
        text-align: center;
        font-size: 11px;
        color: #64748b;
        margin-top: 12px;
        border-top: 1px dashed #e2e8f0;
        padding-top: 10px;
    }
    @media print {
        body {
            margin: 0;
            padding: 0;
            background: #ffffff !important;
        }
        .invoice-ticket {
            max-width: 100% !important;
            width: 100% !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
            border-radius: 0 !important;
        }
    }
</style>

<div class="invoice-ticket">
    <div class="ticket-header">
        <h2>{{ env('APP_NAME', 'CapyControl') }}</h2>
        <p style="font-weight: 600;">FACTURA NO FISCAL</p>
        <p>Ticket: <strong>{{ $invoice->ticket_number }}</strong></p>
        <p>Fecha: {{ $invoice->created_at->format('d/m/Y H:i') }}</p>
    </div>

    @if($invoice->parentRefundSale)
    <div style="background: #fff3cd; color: #856404; padding: 10px; border: 1px solid #ffeeba; margin-bottom: 15px; font-size: 11px; text-align: center; border-radius: 6px;">
        <strong>¡ATENCIÓN!</strong><br>
        Esta factura fue generada por un cambio/devolución del Ticket:<br>
        <strong>{{ $invoice->parentRefundSale->ticket_number }}</strong>
    </div>
    @endif

    <div class="ticket-section">
        <p><strong>Caja:</strong> {{ $invoice->cashSession->cashRegister->name ?? 'N/A' }}</p>
        <p><strong>Cajero:</strong> {{ $invoice->cashSession->user->username ?? 'N/A' }}</p>
        <p><strong>Cliente:</strong> {{ $invoice->customer ? $invoice->customer->name : 'Consumidor Final' }}</p>
        @if($invoice->customer)
            <p><strong>RIF/CI:</strong> {{ $invoice->customer->document_id }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 50%;">Desc</th>
                <th style="text-align: center; width: 18%;">Cant</th>
                <th style="text-align: right; width: 32%;">SubT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td style="word-break: break-all;">
                    {{ $item->product_name }}<br>
                    <small style="color: #64748b;">({{ number_format($item->unit_price, 2) }})</small>
                </td>
                <td style="text-align: center;">{{ number_format($item->quantity, 1) }}</td>
                <td style="text-align: right;">{{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-section">
        <div class="totals-row">
            <span>Subtotal:</span>
            <span>{{ number_format($invoice->total_amount - $invoice->tax_amount, 2) }}</span>
        </div>
        <div class="totals-row">
            <span>Impuestos:</span>
            <span>{{ number_format($invoice->tax_amount, 2) }}</span>
        </div>
        <div class="totals-row total-highlight">
            <span>TOTAL:</span>
            <span>${{ number_format($invoice->total_amount, 2) }}</span>
        </div>
    </div>

    <div class="payment-section">
        <p style="margin: 0 0 6px 0;"><strong>Método de Pago:</strong> {{ $invoice->payment_method }}</p>
        <div class="totals-row">
            <span>Recibido:</span>
            <span>{{ number_format($invoice->tendered_amount, 2) }}</span>
        </div>
        <div class="totals-row">
            <span>Cambio:</span>
            <span>{{ number_format($invoice->change_amount, 2) }}</span>
        </div>
    </div>

    <div class="footer-note">
        <p style="margin: 0; font-weight: 600;">*** GRACIAS POR SU COMPRA ***</p>
        <p style="margin: 4px 0 0 0;">Documento no válido como factura fiscal.</p>
    </div>
</div>
