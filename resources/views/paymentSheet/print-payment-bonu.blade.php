<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Boleta de pago</title>
    <link rel="shortcut icon" href="{{ asset('images/icon.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body{
            margin: 0px auto;
            font-family: Arial, sans-serif;
            font-weight: 100;
            max-width: 740px;
        }
        #watermark {
            position: absolute;
            opacity: 0.1;
            z-index:  -1000;
        }
        #watermark-stamp {
            position: absolute;
            /* opacity: 0.9; */
            z-index:  -1000;
        }
        #watermark img{
            position: relative;
            width: 300px;
            height: 300px;
            left: 205px;
        }
        #watermark-stamp img{
            position: relative;
            width: 4cm;
            height: 4cm;
            left: 50px;
            top: 70px;
        }
        .show-print{
            display: none;
            padding-top: 15px
        }
        .btn-print{
            padding: 5px 10px
        }
        @media print{
            .hide-print, .btn-print{
                display: none
            }
            .show-print, .border-bottom{
                display: block
            }
            .border-bottom{
                border-bottom: 1px solid rgb(90, 90, 90);
                padding: 20px 0px;
            }
        }
    </style>
</head>
<body>
    <div class="hide-print" style="text-align: right; padding: 10px 0px">
        <button class="btn-print" onclick="window.close()">Cancelar <i class="fa fa-close"></i></button>
        <button class="btn-print" onclick="window.print()"> Imprimir <i class="fa fa-print"></i></button>
    </div>
    @php
        
        $months = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    @endphp
    @for ($i = 0; $i < 2; $i++)
    <div style="height: 45vh" @if ($i == 1) class="show-print" @else class="border-bottom" @endif>
        <table width="100%">
            <tr>
                <td><img src="{{ asset('images/icon.png') }}" alt="CAPRESI" width="80px"></td>
                <td style="text-align: right">
                    <h2 style="margin-bottom: 0px; margin-top: 5px">BOLETA DE PAGO</h2>
                    <small>Impreso por {{ Auth::user()->name }} - {{ date('d/m/Y H:i:s') }}</small>
                    <br>
                </td>

            </tr>
        </table>
        <div id="watermark">
            <img src="{{ asset('images/icon.png') }}" height="100%" width="100%" /> 
        </div>
        <table width="100%" border="1" cellpadding="5" style="font-size: 12px">
            <tr>
                <td rowspan="2">

                    <b>NOMBRE: </b><small style="font-size: 15px">{{$bonuDetail->people->first_name }} {{$bonuDetail->people->last_name1 }} {{$bonuDetail->people->last_name2}}</small> <br>
                    <b>CI: </b><small style="font-size: 15px">{{$bonuDetail->people->ci }} </small><br>
                    <b>CELULAR: </b> <small style="font-size: 15px">{{$bonuDetail->people->cell_phone}} </small> <br>
                </td>
                <td valign="top">
                    <b>AÑO: </b><small style="font-size: 15px">{{$bonuDetail->bonu->year}}</small><br>
                    <b>TOTAL GANADO: </b><small style="font-size: 15px">Bs. {{ number_format($bonuDetail->payment, 2, '.', '') }} </small>
                </td>
            </tr>
           
            <tr style="height: 80px">
                <td valign="bottom" style="text-align: center"><b><small>SELLO Y FIRMA</small></b></td>
            </tr>
           
        </table>
    </div>
    @endfor
    <script>
        document.body.addEventListener('keypress', function(e) {
            switch (e.key) {
                case 'Enter':
                    window.print();
                    break;
                case 'Escape':
                    window.close();
                default:
                    break;
            }
        });
    </script>
</body>
</html>