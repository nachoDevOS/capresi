<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Pago</title>
    <style>
        body {
            font-family: "Courier New", Courier, monospace;
            font-size: 12px;
            max-width: 80mm;
            margin: 0 auto;
            text-align: center;
        }
        hr {
            border: none;
            border-top: 1px dashed black;
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 3px 0;
            text-align: left;
        }
        .titulo {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .datos td {
            text-align: left;
        }
        .total {
            font-size: 14px;
            font-weight: bold;
        }
        .firma {
            text-align: center;
            margin-top: 5px;
        }
        .firma .linea {
            border-bottom: 1px solid black;
            display: inline-block;
            width: 70%;
            margin-top: 3px;
        }
        .centrado {
            text-align: center;
        }
        @media print {
            .btn-print {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="btn-print" style="text-align: right; padding: 10px;">
        <button onclick="imprimirYCortar()">🖨️ Imprimir</button>
        <button onclick="window.close()">❌ Cerrar</button>
    </div>

    <div id="comprobante">
        <div class="titulo">COMPROBANTE DE PAGO</div>
        <hr>

        <table class="datos">
            <tr><td><b>Código Venta:</b></td><td style="text-align: right;">{{ $sale->code }}</td></tr>
            <tr><td><b>Fecha:</b></td><td style="text-align: right;">{{ Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y h:i a') }}</td></tr>
            <tr><td><b>CI:</b></td><td style="text-align: right;">{{ $sale->person_id ? $sale->person->ci : 'S/N' }}</td></tr>
            <tr><td><b>Cliente:</b></td><td style="text-align: right;">{{ $sale->person_id ? $sale->person->last_name1.' '.$sale->person->last_name2.' '.$sale->person->first_name : 'S/N' }}</td></tr>
        </table>

        <hr>
        
        <div class="titulo">DETALLE DEL PAGO</div>
        <table>
            <tr><th>Código</th><th>Fecha</th><th style="text-align: right;">Total</th></tr>
            <tr>
                <td>{{ $transaction->id }}</td>
                <td>{{ date('d/m/Y h:i a', strtotime($transaction->created_at)) }}</td>
                <td style="text-align: right;">{{ number_format($saleAgent->amount, 2, ',', '.') }}</td>
            </tr>
        </table>

        <hr>

        <div class="titulo">ATENDIDO POR</div>
        <p>{{ strtoupper($saleAgent->register->name) }}</p>
        <p>{{ strtoupper($saleAgent->agentType) }}</p>

        <hr>

        <!-- Sección de firma compacta -->
        <div class="firma">
            <p>Firma: <span class="linea"></span></p>
            <p>Nombre: <span class="linea"></span></p>
            <p>CI: <span class="linea"></span></p>
        </div>

        <hr>

        <p class="centrado">
            <small><b>Impreso por:</b> {{ Auth::user()->name }} <br> 
            {{ date('d/m/Y h:i a') }}</small>
        </p>
        <p class="centrado"><small><b>LOANSAPP V1</b></small></p>

    </div>

    <!-- Iframe oculto para impresión -->
    <iframe id="iframe-imprimir" style="display:none;"></iframe>

    <script>
        function imprimirYCortar() {
            // Ocultar el botón de impresión para evitar repetición
            document.querySelector('.btn-print').style.display = 'none';

            // Crear el contenido HTML de la factura para imprimir
            var contenidoImprimir = document.getElementById("comprobante").innerHTML;

            // Obtener el iframe oculto y cargar el contenido
            var iframe = document.getElementById("iframe-imprimir");
            var doc = iframe.contentWindow.document;
            doc.open();
            doc.write(contenidoImprimir);
            doc.close();

            // Realizar la impresión
            iframe.contentWindow.focus();
            iframe.contentWindow.print();

            // Después de la primera impresión, cortar el papel
            setTimeout(() => {
                cortarPapel();
                // Imprimir la segunda copia después de un pequeño retraso
                setTimeout(() => {
                    iframe.contentWindow.print();
                    setTimeout(cortarPapel, 2000); // Cortar después de la segunda impresión
                }, 2000);
            }, 2000);
        }

        function cortarPapel() {
            try {
                var escPos = '\x1B\x69'; // Código ESC/POS para cortar papel (ESC i)
                var printCommand = new Blob([escPos], { type: "text/plain" });

                var url = URL.createObjectURL(printCommand);
                var link = document.createElement("a");
                link.href = url;
                link.download = "cut-command.txt";
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                console.log("Comando de corte enviado.");
            } catch (error) {
                console.error("Error enviando comando de corte:", error);
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') imprimirYCortar();
            if (e.key === 'Escape') window.close();
        });
    </script>

</body>
</html>