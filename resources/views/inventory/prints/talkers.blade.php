<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Impresión de Habladores</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background: #fff;
            color: #000;
        }

        .talkers-container {
            display: grid;
            /* Small: 3 columns, Large: 2 columns */
            grid-template-columns: {{ $size === 'small' ? 'repeat(3, 1fr)' : 'repeat(2, 1fr)' }};
            gap: 5mm;
            width: 100%;
        }

        .talker {
            border: 2px solid #000;
            border-radius: 8px;
            padding: 10mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            text-align: center;
            page-break-inside: avoid;
            /* Height: Small ~ 60mm, Large ~ 120mm */
            height: {{ $size === 'small' ? '60mm' : '120mm' }};
            box-sizing: border-box;
        }

        .talker-header {
            width: 100%;
            border-bottom: 2px solid #eee;
            padding-bottom: 5mm;
            margin-bottom: 5mm;
        }

        .product-name {
            font-size: {{ $size === 'small' ? '12pt' : '18pt' }};
            font-weight: 800;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .product-price {
            font-size: {{ $size === 'small' ? '24pt' : '48pt' }};
            font-weight: 900;
            color: #000;
            margin: auto 0;
        }
        
        .currency {
            font-size: {{ $size === 'small' ? '14pt' : '24pt' }};
            vertical-align: top;
        }

        .barcode-container {
            width: 100%;
            display: flex;
            justify-content: center;
            margin-top: 5mm;
        }

        .barcode-container svg {
            max-width: 100%;
            height: {{ $size === 'small' ? '15mm' : '20mm' }}; 
        }

        @media print {
            body { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="talkers-container">
        @foreach($printData as $index => $item)
            <div class="talker">
                <div class="talker-header">
                    <div class="product-name">{{ $item['name'] }}</div>
                </div>
                
                <div class="product-price">
                    <span class="currency">$</span>{{ $item['price'] }}
                </div>
                
                @if($item['barcode'])
                    <div class="barcode-container">
                        <svg class="barcode" 
                            jsbarcode-format="CODE128"
                            jsbarcode-value="{{ $item['barcode'] }}"
                            jsbarcode-textmargin="2"
                            jsbarcode-height="40"
                            jsbarcode-fontSize="14"
                            jsbarcode-margin="0"
                            jsbarcode-displayValue="true">
                        </svg>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <script>
        // Render Barcodes
        JsBarcode(".barcode").init();
    </script>
</body>
</html>
