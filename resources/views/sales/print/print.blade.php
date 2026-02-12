<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura de Venta</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f9;
        }
        .invoice {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            border-radius: 12px;
            border: 1px solid #ddd;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            position: relative;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        .header h1 {
            margin: 0;
            font-size: 32px;
            color: #2c3e50;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .header p {
            margin: 5px 0;
            color: #7f8c8d;
            font-size: 16px;
        }
        .details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
            border: 1px solid #eee;
        }
        .details .left, .details .right {
            width: 48%;
        }
        .details h2 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #34495e;
            font-weight: 600;
        }
        .details p {
            margin: 5px 0;
            color: #555;
            font-size: 14px;
        }
        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 30px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }
        .table th, .table td {
            padding: 12px;
            text-align: left;
            font-size: 14px;
        }
        .table th {
            background-color: #34495e;
            color: #fff;
            font-weight: bold;
            border-bottom: 2px solid #2c3e50;
        }
        .table td {
            color: #555;
            border-bottom: 1px solid #eee;
        }
        .table tbody tr:last-child td {
            border-bottom: none;
        }
        .table tbody tr:hover {
            background-color: #f1f1f1;
            transition: background-color 0.3s ease;
        }
        .table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .totals {
            text-align: right;
            margin-bottom: 30px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
            border: 1px solid #eee;
        }
        .totals p {
            margin: 8px 0;
            color: #555;
            font-size: 16px;
        }
        .totals p strong {
            color: #34495e;
            font-weight: 600;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #7f8c8d;
            border-top: 2px solid #eee;
            padding-top: 15px;
            margin-top: 20px;
        }
        .footer p {
            margin: 5px 0;
        }
        .buttons {
            text-align: center;
            margin-top: 20px;
        }
        .buttons button {
            padding: 10px 20px;
            font-size: 14px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 0 10px;
            transition: background-color 0.3s ease;
        }
        .buttons button#print {
            background-color: #34495e;
            color: #fff;
        }
        .buttons button#print:hover {
            background-color: #2c3e50;
        }
        .buttons button#cancel {
            background-color: #e74c3c;
            color: #fff;
        }
        .buttons button#cancel:hover {
            background-color: #c0392b;
        }
    </style>
</head>
@php
    $meses = ['', 'ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
@endphp
<body>
    <div class="invoice">
        <div class="header">
            <h1>CAPRESI</h1>
            <p>Factura de Venta</p>
        </div>

        <div class="details">
            <div class="left">
                <h2>FACTURAR A:</h2>
                @if ($sale->person_id)
                    <p>{{$sale->person->first_name}} {{$sale->person->last_name1}} {{$sale->person->last_name2}}</p>
                    <p>CI: {{$sale->person->ci}}</p>
                    <p>Celular: {{$sale->cell_phone}}</p>
                @else
                    <p>S/N</p>
                @endif
            </div>
            <div class="right">
                <h2>POR:</h2>
                <p>{{$sale->description}}</p>
                <p><strong>FECHA:</strong> 
                    {{ date('d/', strtotime($sale->saleDate)).$meses[intval(date('m', strtotime($sale->saleDate)))].date('/Y h:i:s a', strtotime($sale->saleDate)) }}
                </p>
                <p><strong>FACTURA:</strong> {{$sale->code}} </p>
            </div>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>DETALLE</th>
                    <th style="text-align: right">CANTIDAD</th>
                    <th style="text-align: right">PRECIO</th>
                    <th style="text-align: right">SUBTOTAL</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $cont = 1;
                    $total = 0;
                @endphp
                @foreach ($sale->saleDetails as $detail)
                    <tr>
                        <td>
                            <small style="font-size: 15px">
                                {{ floatval($detail->quantity) == intval($detail->quantity) ? intval($detail->quantity) : $detail->quantity }}
                                {{ $detail->inventory->item->unit }} {{ $detail->inventory->item->name }} 
                            </small> <br>
                            @php
                                $features_list = '';
                                foreach ($detail->inventory->features as $feature) {
                                    if ($feature->value) {
                                        $features_list .= '<span style="font-size: 12px"><b>'.$feature->title.'</b>: '.$feature->value.'</span><br>';
                                    }
                                }
                            @endphp
                            {!! $features_list !!}
                        </td>
                        <td style="text-align: right">{{ ($detail->quantity - intval($detail->quantity))*100 ? $detail->quantity : intval($detail->quantity) }}{{ $detail->inventory->item->unit }}</td>
                        <td style="text-align: right">{{ $detail->price }}</td>
                        <td style="text-align: right">{{ number_format($detail->amountTotal, 2, ',', '.') }}</td>
                    </tr>
                    @php
                        $cont++;
                        $total += $detail->amountTotal;
                    @endphp
                @endforeach
            </tbody>
        </table>
        <div class="totals">
            <p><strong>SUBTOTAL:</strong> {{ number_format($total, 2, ',', '.') }}</p>
            <p><strong>DESCUENTO:</strong> {{ number_format($sale->discount, 2, ',', '.') }}</p>
            <p><strong>TOTAL:</strong> {{ number_format($sale->amountTotal, 2, ',', '.') }}</p>
        </div>

        <div class="footer">
            <p>Gracias por su compra. ¡Esperamos verlo nuevamente!</p>
            <p>CAPRESI - {{ date('d/m/Y h:i a') }}</p>
        </div>
        <div class="buttons">
            <button id="print">Imprimir Factura</button>
            <button id="cancel">Cancelar</button>
        </div>
    </div>

    <script>
        document.getElementById('print').addEventListener('click', function () {
            window.print();
        });

        document.getElementById('cancel').addEventListener('click', function () {
            if (confirm('¿Estás seguro de que deseas cancelar?')) {
                window.close();
            }
        });
    </script>
</body>
</html>