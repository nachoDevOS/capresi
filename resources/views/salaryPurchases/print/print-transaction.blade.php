<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Pago</title>
    <style>
        /* ESTILOS PROFESIONALES PARA IMPRESIÓN TÉRMICA */
        body {
            font-family: 'Arial Narrow', Arial, sans-serif; /* Fuente profesional */
            font-size: 9px;
            width: 80mm;
            margin: 0 auto;
            padding: 1mm 2mm 0;
            line-height: 1.2;
            background-color: #fff;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        
        /* ENCABEZADO EMPRESARIAL */
        .company-header {
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
            margin-bottom: 3px;
            text-align: center;
        }
        
        .company-name {
            font-size: 12px;
            font-weight: bold;
            margin: 2px 0;
            letter-spacing: 0.5px;
        }
        
        .company-info {
            font-size: 8px;
            margin: 1px 0;
        }
        
        .receipt-title {
            font-size: 11px;
            font-weight: bold;
            margin: 3px 0;
            text-transform: uppercase;
        }
        
        /* DISEÑO TABULAR PROFESIONAL */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 3px 0;
            font-size: 9px;
        }
        
        th {
            font-weight: bold;
            text-align: left;
            padding: 2px 0;
            border-bottom: 1px solid #000;
        }
        
        td {
            padding: 2px 0;
            vertical-align: top;
        }
        
        .total-row {
            font-weight: bold;
            border-top: 1px solid #000;
            border-bottom: 2px solid #000;
        }
        
        /* QR PROFESIONAL */
        .qr-container {
            text-align: center;
            margin: 4px 0;
        }
        
        .qr-with-logo {
            position: relative;
            display: inline-block;
            margin: 0 auto;
            border: 1px solid #eee;
            padding: 2px;
        }
        
        .qr-logo {
            position: absolute;
            width: 30px;
            height: 30px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #fff;
            box-shadow: 0 0 3px rgba(0,0,0,0.1);
        }
        
        /* PIE DE PÁGINA CORPORATIVO */
        .footer {
            font-size: 8px;
            margin-top: 4px;
            line-height: 1.2;
            text-align: center;
        }
        
        .legal-text {
            font-size: 7px;
            margin-top: 3px;
            line-height: 1.1;
        }
        
        @media print {
            body { 
                margin: 0; 
                padding: 0 2mm;
            }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <!-- ENCABEZADO CORPORATIVO -->
    <div class="company-header">
        <div class="company-name uppercase">CAPRESI</div>
        <div class="company-info">Calle Sucre • Tel: (591) 72817259</div>
        <div class="receipt-title">comprobante de pago</div>
        <div style="font-size: 10px;">N° {{ $salary->code }}</div>
    </div>

    <!-- DATOS DEL CLIENTE (FORMATO PROFESIONAL) -->
    <table>        
        <tr>
            <td><span class="bold">Cliente:</span></td>
            <td class="uppercase">{{ $salary->person->last_name1 }} {{ $salary->person->last_name2 }}, {{ $salary->person->first_name }}</td>
        </tr>
        <tr>
            <td><span class="bold">CI/NIT:</span></td>
            <td>{{ $salary->person->ci }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th style="width: 20%;">Concepto</th>
                <th style="width: 50%;">Periodo</th>
                <th style="width: 30%; text-align: right;">Importe (Bs)</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach ($salaryMonthAgent as $item)
            <tr>
                <td>{{ $item->prm_id ? 'Interés' : 'Capital' }}</td>
                <td>
                    @if ($item->prm_id)
                        {{ date('d/m/Y', strtotime($item->start)) }} - {{ date('d/m/Y', strtotime($item->finish)) }}
                    @else
                        Amortización capital
                    @endif
                </td>
                <td style="text-align: right;">{{ number_format($item->amount, 2, ',', '.') }}</td>
            </tr>
            @php $total += $item->amount; @endphp
            @endforeach
            <tr class="total-row">
                <td colspan="2" class="text-right bold">TOTAL PAGADO:</td>
                <td style="text-align: right;">{{ number_format($total, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <table>
        <tr>
            <td style="width: 30%;"><span class="bold">Forma de pago:</span></td>
            <td>EFECTIVO</td>
        </tr>
        <tr>
            <td><span class="bold">Cobrado Por:</span></td>
            <td class="uppercase">{{ $salaryMonthAgent[0]->name }}</td>
        </tr>

        <tr>
            <td><span class="bold">Fecha/Hora:</span></td>
            <td class="uppercase">{{ Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y h:i:s a') }}</td>
        </tr>

        <tr>
            <td><span class="bold">Transacción:</span></td>
            <td>{{ $transaction->transaction }}</td>
        </tr>
    </table>

    <div class="qr-container">
        @php
            $qrContent = '
                TRANSACCION: '.$transaction->transaction.'
                FECHA DE PAGO: '. Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y h:i:s a').'
                MONTO PAGADO: '.number_format($total, 2, ',', '.').'
                CI.: '.$salary->person->ci;
        @endphp
        
        <div class="qr-with-logo">
            {!! QrCode::size(100)
                ->backgroundColor(255, 255, 255)
                ->color(40, 40, 40)
                ->margin(1)
                ->errorCorrection('H')
                ->generate($qrContent) !!}
            <img src="{{asset('images/logoQr.png')}}" class="qr-logo" alt="Logo Capresi">
        </div>
        <div style="font-size: 8px; margin-top: 2px;">Código de verificación</div>
    </div>

    <div style="text-align: center; margin-top: 18px;">
        <div style="display: inline-block; width: 60%;">
            <div style="border-top: 1px solid #000; margin-top: 5px;"></div>
            <div style="font-size: 8px; margin-top: 2px;">Firma del cliente</div>
        </div>
    </div>

    <div class="footer">
        <div>Impreso por: {{ Auth::user()->name }} | {{ date('d/m/Y h:i:s a') }}</div>
        <div class="bold">Sistema de Cobranza <br>CAPRESI</div>
    </div>

    <script>
        document.addEventListener('keydown', function(e) {
            if (e.key === 'p' || e.key === 'P') window.print();
            if (e.key === 'Escape') window.close();
        });
    </script>
</body>
</html>