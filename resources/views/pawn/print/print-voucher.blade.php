<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Comprobante de Entrega</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace; /* Fuente monoespaciada */
            font-size: 12px; /* Tamaño pequeño */
            max-width: 80mm; /* Ancho estándar para impresora térmica */
            margin: 0 auto; /* Centrar contenido */
        }
        h1, h2, h3 {
            font-size: 14px;
            text-align: center;
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: left;
            padding: 2px 0;
        }
        .separator {
            width: 100%;
            border-top: 1px dashed black; /* Línea simple */
            margin: 5px 0;
        }
        .center {
            text-align: center;
        }
        .bold {
            font-weight: bold;
        }
        .small {
            font-size: 10px;
        }
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: right; padding: 10px;">
        <button onclick="window.close()">Cancelar</button>
        <button onclick="window.print()">Imprimir</button>
    </div>

    <h1>Comprobante de Entrega</h1>
    <h2>PRÉSTAMO</h2>
    <div class="separator"></div>
    
    <table>
        <tr>
            <th>Código:</th>
            <td class="bold">{{ $pawn->code }}</td>
        </tr>
        <tr>
            <th>Fecha:</th>
            <td>{{ Carbon\Carbon::parse($pawn->dateDelivered)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <th>Beneficiario:</th>
            <td>{{ $pawn->person->last_name1 }} {{ $pawn->person->last_name2 }} {{ $pawn->person->first_name }}</td>
        </tr>
        <tr>
            <th>CI:</th>
            <td>{{ $pawn->person->ci ? $pawn->person->ci : 'No definido' }}</td>
        </tr>
    </table>
    
    <div class="separator"></div>
    <h3>Detalle del Préstamo</h3>
    <div class="separator"></div>

    <table>
        <tr>
            <th>Monto Prestado (Bs):</th>
            <td class="bold">Bs. {{ number_format($pawn->amountTotal, 2, '.', '') }}</td>
        </tr>
        <tr>
            <th>Monto Prestado ($):</th>
            <td class="bold">$ {{ number_format($pawn->dollarTotal, 2, '.', '') }}</td>
        </tr>
        <tr>
            <th>Interés (%):</th>
            <td>% {{ number_format($pawn->interest_rate, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Tiempo de Espera:</th>
            <td>{{ $pawn->cantMonth }} Mes(es)</td>
        </tr>
    </table>
    
    <div class="separator"></div>
    <h3>Entregado Por</h3>
    <div class="separator"></div>

    <table>
        <tr>
            <th>Cargo:</th>
            <td>{{ strtoupper($pawn->agentDelivered->role->name) }}</td>
        </tr>
        <tr>
            <th>Nombre:</th>
            <td>{{ strtoupper($pawn->agentDelivered->name) }}</td>
        </tr>
    </table>
    
    <div class="separator"></div>
    <h3>Firma del Beneficiario</h3>
    <div class="separator"></div>

    <p style="text-align: center">___________________________</p>
    <p style="text-align: center">Nombre: ____________________</p>
    <p style="text-align: center">CI: ________________________</p>
    
    <div class="separator"></div>
    <p class="center small">
        Impreso por: {{ Auth::user()->name }} <br>
        {{ date('d/M/Y H:i:s') }} <br>
        <b>LOANSAPP V1</b>
    </p>
</body>
</html>
