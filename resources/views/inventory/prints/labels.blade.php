<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Impresión de Etiquetas</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        @page {
            /* Forzamos el tamaño exacto del rollo para evitar que Chrome lo estire a tamaño carta */
            size: {{ $columns == 2 ? '74mm 45mm' : '37mm 45mm' }};
            margin: 0; 
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background: #fff;
            color: #000;
            /* Restringimos el ancho del body al tamaño del papel */
            width: {{ $columns == 2 ? '74mm' : '37mm' }};
        }

        .labels-container {
            display: flex;
            flex-wrap: wrap;
            width: {{ $columns == 2 ? '74mm' : '37mm' }};
            align-content: flex-start;
        }

        .label {
            /* Height 45mm is standard for their roll. Width is approx 37mm. */
            height: 45mm;
            width: 37mm;
            box-sizing: border-box;
            padding: 2mm;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            page-break-inside: avoid;
            overflow: hidden;
            border-right: {{ $columns == 2 ? '1px dashed transparent' : 'none' }}; /* Helps with visual debugging if needed */
        }

        .product-name {
            font-size: 8pt;
            font-weight: bold;
            line-height: 1.1;
            max-height: 18pt;
            overflow: hidden;
            margin-bottom: 2mm;
            text-transform: uppercase;
        }

        .product-price {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 2mm;
        }

        .barcode-container {
            width: 100%;
            display: flex;
            justify-content: center;
        }

        .barcode-container svg {
            max-width: 100%;
            height: 12mm; /* Ensure it fits vertically */
        }
        
        /* Hide everything else when printing */
        @media print {
            body { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="labels-container">
        @foreach($printData as $index => $item)
            <div class="label">
                <div class="product-name">{{ $item['name'] }}</div>
                <div class="product-price">${{ $item['price'] }}</div>
                @if($item['barcode'])
                    <div class="barcode-container">
                        <svg class="barcode" 
                            jsbarcode-format="CODE128"
                            jsbarcode-value="{{ $item['barcode'] }}"
                            jsbarcode-textmargin="0"
                            jsbarcode-height="30"
                            jsbarcode-fontSize="10"
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
