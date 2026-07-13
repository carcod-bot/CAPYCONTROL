<div style="font-family: monospace; font-size: 14px; color: #000; padding: 20px; background: #fff; width: 100%; max-width: 350px; margin: 0 auto; border: 1px solid #ddd; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="margin: 0; font-size: 18px; font-weight: bold;">{{ env('APP_NAME', 'CapyControl') }}</h2>
        <p style="margin: 5px 0 0;">FACTURA NO FISCAL</p>
        <p style="margin: 0;">Ticket: <strong>{{ $invoice->ticket_number }}</strong></p>
        <p style="margin: 0;">Fecha: {{ $invoice->created_at->format('d/m/Y H:i') }}</p>
    </div>

    <div style="margin-bottom: 20px; border-bottom: 1px dashed #000; padding-bottom: 10px;">
        <p style="margin: 2px 0;"><strong>Caja:</strong> {{ $invoice->cashSession->cashRegister->name ?? 'N/A' }}</p>
        <p style="margin: 2px 0;"><strong>Cajero:</strong> {{ $invoice->cashSession->user->username ?? 'N/A' }}</p>
        <p style="margin: 2px 0;"><strong>Cliente:</strong> {{ $invoice->customer ? $invoice->customer->name : 'Consumidor Final' }}</p>
        @if($invoice->customer)
            <p style="margin: 2px 0;"><strong>RIF/CI:</strong> {{ $invoice->customer->document_id }}</p>
        @endif
    </div>

    <table style="width: 100%; text-align: left; margin-bottom: 20px; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 1px solid #000;">
                <th style="padding-bottom: 5px; width: 50%;">Desc</th>
                <th style="padding-bottom: 5px; text-align: center; width: 15%;">Cant</th>
                <th style="padding-bottom: 5px; text-align: right; width: 35%;">SubT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td style="padding: 5px 0; word-break: break-all;">{{ $item->product_name }}<br><small style="color: #666;">({{ number_format($item->unit_price, 2) }})</small></td>
                <td style="padding: 5px 0; text-align: center; vertical-align: top;">{{ number_format($item->quantity, 1) }}</td>
                <td style="padding: 5px 0; text-align: right; vertical-align: top;">{{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="border-top: 1px solid #000; padding-top: 10px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
            <span>Subtotal:</span>
            <span>{{ number_format($invoice->total_amount - $invoice->tax_amount, 2) }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
            <span>Impuestos:</span>
            <span>{{ number_format($invoice->tax_amount, 2) }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 16px; margin-top: 10px;">
            <span>TOTAL:</span>
            <span>${{ number_format($invoice->total_amount, 2) }}</span>
        </div>
    </div>

    <div style="margin-bottom: 20px;">
        <p style="margin: 0;"><strong>Método de Pago:</strong> {{ $invoice->payment_method }}</p>
        <div style="display: flex; justify-content: space-between; margin-top: 5px;">
            <span>Recibido:</span>
            <span>{{ number_format($invoice->tendered_amount, 2) }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-top: 5px;">
            <span>Cambio:</span>
            <span>{{ number_format($invoice->change_amount, 2) }}</span>
        </div>
    </div>

    <div style="text-align: center; font-size: 12px; color: #666;">
        <p style="margin: 0;">*** GRACIAS POR SU COMPRA ***</p>
        <p style="margin: 5px 0 0;">Documento no válido como factura fiscal.</p>
    </div>
</div>
