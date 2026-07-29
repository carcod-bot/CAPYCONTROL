<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Impresión de Etiquetas</title>
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#1e3a8a">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        :root {
            --primary: #1e3a8a;
            --bg: #e5e7eb;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background: var(--bg);
            color: #000;
        }

        /* Top App Bar (Only visible on screen) */
        .app-bar {
            background: var(--primary);
            color: white;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .app-title { font-size: 1.2rem; font-weight: 600; }
        .btn-print {
            background: white;
            color: var(--primary);
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Container for screen viewing */
        .viewer-container {
            display: flex;
            justify-content: center;
            padding: 20px;
        }

        /* Actual print area */
        .print-area {
            background: #fff;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
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
            border-right: {{ $columns == 2 ? '1px dashed transparent' : 'none' }};
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
        
        @page {
            /* Forzamos el tamaño exacto del rollo para evitar que Chrome lo estire a tamaño carta */
            size: {{ $columns == 2 ? '74mm 45mm' : '37mm 45mm' }};
            margin: 0; 
        }

        @media print {
            body { 
                background: #fff !important; 
                -webkit-print-color-adjust: exact; 
            }
            .app-bar, .viewer-container { padding: 0 !important; }
            .app-bar { display: none !important; }
            .viewer-container { display: block !important; padding: 0 !important; }
            .print-area { box-shadow: none !important; width: 100% !important; margin: 0 !important; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="app-bar">
        <div class="app-title"><i class="fa-solid fa-tags"></i> Etiquetas (Visor PWA)</div>
        <button class="btn-print" onclick="window.print()"><i class="fa-solid fa-print"></i> Imprimir</button>
    </div>

    <div class="viewer-container">
        <div class="print-area">
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
        </div>
    </div>

    <script>
        // Render Barcodes
        JsBarcode(".barcode").init();
    </script>
</body>
</html>
