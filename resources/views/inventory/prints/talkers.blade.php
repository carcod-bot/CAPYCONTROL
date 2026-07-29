<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Impresión de Habladores</title>
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
            width: 210mm; /* A4 width */
            min-height: 297mm; /* A4 height */
            padding: 10mm;
            box-sizing: border-box;
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

        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        @media print {
            body { 
                background: #fff !important; 
                -webkit-print-color-adjust: exact; 
            }
            .app-bar, .viewer-container { padding: 0 !important; }
            .app-bar { display: none !important; }
            .viewer-container { display: block !important; padding: 0 !important; }
            .print-area { box-shadow: none !important; width: 100% !important; min-height: auto !important; padding: 0 !important; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="app-bar">
        <div class="app-title"><i class="fa-solid fa-tags"></i> Habladores (Visor PWA)</div>
        <button class="btn-print" onclick="window.print()"><i class="fa-solid fa-print"></i> Imprimir</button>
    </div>

    <div class="viewer-container">
        <div class="print-area">
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
        </div>
    </div>

    <script>
        // Render Barcodes
        JsBarcode(".barcode").init();
    </script>
</body>
</html>
