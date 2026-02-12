<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket de Préstamo</title>
    <style>
        /* Estilo general */
        body {
            font-family: 'Courier New', Courier, monospace; /* Fuente monoespaciada */
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        .ticket {
            width: 250px; /* Tamaño típico de ticket térmico */
            margin: 0 auto;
            padding: 5px;
            text-align: left;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        hr {
            border: none;
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .item-table td {
            padding: 2px 0;
        }

        .total {
            font-size: 14px;
            font-weight: bold;
            text-align: right;
        }

        .footer {
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="ticket">
        <!-- Encabezado -->
        <div class="center bold">
            {{ env('APP_NAME', 'Sistema de Préstamos') }}<br>
            =========================
        </div>

        <!-- Información General -->
        <p><span class="bold">Fecha:</span> </p>
        <p><span class="bold">Cliente:</span> </p>
        <p><span class="bold">Préstamo #:</span> </p>

        <hr>

        <!-- Tabla de Detalles -->
        <table class="item-table">
            <tbody>
                <tr>
                    <td class="bold">Descripción</td>
                    <td class="bold" style="text-align: right;">Monto</td>
                </tr>
              
            </tbody>
        </table>

        <hr>

        <!-- Total -->
        <div class="total">
            Total: 
        </div>

        <hr>

        <!-- Pie de Página -->
        <div class="footer">
            ¡Gracias por su preferencia!<br>
            Tel: (123) 456-7890
        </div>
    </div>
</body>
</html>
