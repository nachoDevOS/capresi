<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
       
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1px;
        }
        th, td {
            padding: 1px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        td {
            /* border-bottom: 1px solid #ddd; */
        }
        .footer {
            text-align: right;
            font-size: 10px;
            margin-top: 20px;
        }
        @media print{
            .hide-print{
                display: none
            }
            .content{
                padding: 0px 0px
            }
            .sheet {
                width: 100%;
                margin: 0px;
                padding: 0px;
            }
        }
    </style>
</head>
<body>
    <div class="hide-print" style="position: fixed; right: 0px; bottom: 0px; width:100%; text-align: right; padding: 20px">
        <button class="btn-print" onclick="window.close()">Cancelar <i class="fa fa-close"></i></button>
        <button class="btn-print" onclick="window.print()"> Imprimir <i class="fa fa-print"></i></button>
    </div>

    @php
        $months = array('', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');    
    @endphp

    <table width="100%">
        <tr>
            <td style="width: 25%"><img src="{{ asset('images/icon.png') }}" alt="CAPRESI" width="70px"></td>
            <td style="text-align: center;  width:50%">
                <h4 style="margin-bottom: 0px; margin-top: 5px">
                    EMPRESA "CAPRESI"<br>
                </h4>
                <h5 style="margin-bottom: 0px; margin-top: 4px">
                    RESUMEN DIARIO DE PRESTAMOS Y COBRANZAS
                </h5>
                <small style="margin-bottom: 0px; margin-top: 5px">
                        {{ date('d', strtotime($date)) }} de {{ $months[intval(date('m', strtotime($date)))] }} de {{ date('Y', strtotime($date)) }}
                </small>
            </td>
            <td style="text-align: right; width:25%">
                <h3 style="margin-bottom: 0px; margin-top: 5px">
                    <div id="qr_code">
                        {!! QrCode::size(60)->generate('Reporte generado en fecha '.date('d', strtotime($date)).' de '.$months[intval(date('m', strtotime($date)))].' de '.date('Y', strtotime($date))); !!}
                    </div>
                    <small style="font-size: 8px; font-weight: 100">Impreso por: {{ Auth::user()->name }} <br>{{ date('d/M/Y H:i:s') }}</small>
                </h3>
            </td>
        </tr>
    </table>
    <hr>

    <h4>EFECTIVO</h4>
    
    <table style="font-size: 12px">
        <tbody>
            @php
                $cont = 1;
                $totalCashiers=0;
                $total_routes=0;
            @endphp
            <tr style="font-size: 14px; background-color: #65a053">
                <td style="width: 3%"></td>
                <td colspan="3"><b>CAJAS</b></td>
            </tr>
            @foreach ($cashiers as $cashier)
                <tr style="font-size: 14px">
                    <td style="width: 3%"></td>
                    <td style="width: 3%"></td>

                    <td>{{ $cashier->user->name }}</td>
                    <td style="text-align: right">{{ number_format($cashier->movements->sum('amount'), 2, ',', '.') }}</td>
                </tr>
                @php
                    $totalCashiers += $cashier->movements->sum('amount');
                @endphp
            @endforeach
            
            
            @php
                $grouped_loans_payments = $loans_payments->groupBy('agent')->map(function ($agent_loans_payments, $agent) {
                    // Obtén la ruta asociada a cada agente
                    $route = \Illuminate\Support\Facades\DB::table('route_collectors as ru')
                        ->join('routes as r', 'r.id', 'ru.route_id')
                        ->join('users as u', 'u.id', 'ru.user_id')
                        ->where('u.id', json_decode($agent)->id)
                        ->where('ru.status', 1)
                        ->where('ru.deleted_at', null)
                        ->select('r.name', 'r.color')
                        ->first();

                    return [
                        'agent' => json_decode($agent),
                        'route' => $route,
                        'payments' => $agent_loans_payments,
                    ];
                })->sortBy(function ($group) {
                    return $group['route']->name ?? ''; // Ordena por nombre de la ruta
                });
            @endphp
            @foreach ($grouped_loans_payments as $group)
                @php
                    $agent = $group['agent'];
                    $route = $group['route'];
                    $agent_loans_payments = $group['payments'];
                    $total_routes += $agent_loans_payments->sum('amount');
                @endphp
            @endforeach

            <tr style="font-size: 14px; background-color: #e6df88">
                <td style="width: 2%"></td>
                <td colspan="2"><b>TOTAL RUTAS</b></td>
                <td style="text-align: right">{{ number_format($total_routes, 2, ',', '.') }}</td>

            </tr>

            @foreach ($grouped_loans_payments as $group)
                @php
                    $agent = $group['agent'];
                    $route = $group['route'];
                    $agent_loans_payments = $group['payments'];
                @endphp

                <tr style="font-size: 14px; background-color: {{$route?$route->color:''}}">
                    <td style="width: 2%"></td>
                    <td style="width: 2%"></td>
                    <td>{{ $route ? $route->name : '' }} - {{ $agent->name }}</td>
                    <td style="text-align: right">{{ number_format($agent_loans_payments->sum('amount'), 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr style="font-size: 14px; background-color: #f3e95f">
                <td style="width: 2%"></td>
                <td colspan="2"><b>TOTAL PRENDARIO</b></td>
                <td style="text-align: right">{{ number_format($prendario->sum('amount'), 2, ',', '.') }}</td>
            </tr>
            @foreach ($prendario->groupBy('agent_id') as $group)
                <tr style="font-size: 14px; background-color: #c5c1af">
                    <td style="width: 2%"></td>
                    <td style="width: 2%"></td>
                    <td>
                        {{$group->first()->agent->name}}
                    </td>
                    <td style="text-align: right">{{ number_format($group->sum('amount'), 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr style="font-size: 14px; background-color: #dad9d5">
                <td style="width: 2%"></td>
                <td colspan="2"><b>TOTAL INGRESOS (CAJAS + RUTAS + PRENDARIO)</b></td>
                <td style="text-align: right">{{ number_format($total_routes+$totalCashiers +$prendario->sum('amount'), 2, ',', '.') }}</td>

            </tr>

        </tbody>
    </table>
    <br>
    <table style="font-size: 12px">
        <tbody>
            @php
                $total_routesEfectivo=0;
        
                $grouped_loans_payments = $loans_payments->groupBy('agent')->map(function ($agent_loans_payments, $agent) {
                    // Obtén la ruta asociada a cada agente
                    $route = \Illuminate\Support\Facades\DB::table('route_collectors as ru')
                        ->join('routes as r', 'r.id', 'ru.route_id')
                        ->join('users as u', 'u.id', 'ru.user_id')
                        ->where('u.id', json_decode($agent)->id)
                        ->where('ru.status', 1)
                        ->where('ru.deleted_at', null)
                        ->select('r.name', 'r.color')
                        ->first();

                    return [
                        'agent' => json_decode($agent),
                        'route' => $route,
                        'payments' => $agent_loans_payments,
                    ];
                })->sortBy(function ($group) {
                    return $group['route']->name ?? ''; // Ordena por nombre de la ruta
                });
            @endphp
            @foreach ($grouped_loans_payments as $group)
                @php
                    $agent_loans_payments = $group['payments'];
                    $total_routesEfectivo += $agent_loans_payments->where('type','Efectivo')->sum('amount');
                @endphp
            @endforeach

            <tr style="font-size: 14px; background-color: #e6df88">
                <td style="width: 2%"></td>
                <td colspan="2"><b>TOTAL RUTAS EFECTIVO</b></td>
                <td style="text-align: right">{{ number_format($total_routesEfectivo, 2, ',', '.') }}</td>

            </tr>

            @foreach ($grouped_loans_payments as $group)
                @php
                    $agent = $group['agent'];
                    $route = $group['route'];
                    $agent_loans_payments = $group['payments'];
                @endphp

                <tr style="font-size: 14px;">
                    <td style="width: 2%"></td>
                    <td style="width: 2%"></td>
                    <td>{{ $route ? $route->name : '' }} - {{ $agent->name }}</td>
                    <td style="text-align: right">{{ number_format($agent_loans_payments->where('type','Efectivo')->sum('amount'), 2, ',', '.') }}</td>
                </tr>
            @endforeach

        </tbody>
    </table>
    <br>
    <table style="font-size: 12px">
        <tbody>
            @php
                $total_routesQr=0;
        
                $grouped_loans_payments = $loans_payments->groupBy('agent')->map(function ($agent_loans_payments, $agent) {
                    // Obtén la ruta asociada a cada agente
                    $route = \Illuminate\Support\Facades\DB::table('route_collectors as ru')
                        ->join('routes as r', 'r.id', 'ru.route_id')
                        ->join('users as u', 'u.id', 'ru.user_id')
                        ->where('u.id', json_decode($agent)->id)
                        ->where('ru.status', 1)
                        ->where('ru.deleted_at', null)
                        ->select('r.name', 'r.color')
                        ->first();

                    return [
                        'agent' => json_decode($agent),
                        'route' => $route,
                        'payments' => $agent_loans_payments,
                    ];
                })->sortBy(function ($group) {
                    return $group['route']->name ?? ''; // Ordena por nombre de la ruta
                });
            @endphp
            @foreach ($grouped_loans_payments as $group)
                @php
                    $agent_loans_payments = $group['payments'];
                    $total_routesQr += $agent_loans_payments->where('type','Qr')->sum('amount');
                @endphp
            @endforeach

            <tr style="font-size: 14px; background-color: #e6df88">
                <td style="width: 2%"></td>
                <td colspan="2"><b>TOTAL RUTAS QR</b></td>
                <td style="text-align: right">{{ number_format($total_routesQr, 2, ',', '.') }}</td>

            </tr>

            @foreach ($grouped_loans_payments as $group)
                @php
                    $agent = $group['agent'];
                    $route = $group['route'];
                    $agent_loans_payments = $group['payments'];
                @endphp

                <tr style="font-size: 14px;">
                    <td style="width: 2%"></td>
                    <td style="width: 2%"></td>
                    <td>{{ $route ? $route->name : '' }} - {{ $agent->name }}</td>
                    <td style="text-align: right">{{ number_format($agent_loans_payments->where('type','Qr')->sum('amount'), 2, ',', '.') }}</td>
                </tr>
            @endforeach

        </tbody>
    </table>
  
    <br>
    <h4>GASTOS</h4>

    <table style="font-size: 12px">
        <tbody>
            <tr style="font-size: 14px;">
                <td style="width: 3%"></td>
                <td style="width: 3%"></td>
                <td >Prestamos</td>
                <td style="text-align: right">{{ number_format($loans->sum('amountLoan'), 2, ',', '.') }}</td>
            </tr>
            <tr style="font-size: 14px;">
                <td style="width: 3%"></td>
                <td style="width: 3%"></td>
                <td >Prestamos C/Garantias</td>
                {{-- <td style="text-align: right">0,00</td> --}}
                <td style="text-align: right">{{ number_format($pawns->sum('amountTotal'), 2, ',', '.') }}</td>

            </tr>
            @php
                $total_Gasto = $cashier_cash_out->sum('amount');
            @endphp
            <tr style="font-size: 14px;">
                <td style="width: 3%"></td>
                <td style="width: 3%"></td>
                <td >Gastos</td>
                <td style="text-align: right">{{ number_format($total_Gasto, 2, ',', '.') }}</td>
            </tr>
            
            
            
 
            <tr style="font-size: 14px; background-color: #dad9d5">
                <td style="width: 2%"></td>
                <td colspan="2"><b>TOTAL GASTOS</b></td>
                <td style="text-align: right">{{ number_format($loans->sum('amountLoan')+$cashier_cash_out->sum('amount')+$pawns->sum('amountTotal'), 2, ',', '.') }}</td>

            </tr>
            <tr style="font-size: 14px; background-color: #7185f3">
                <td style="width: 2%"></td>
                <td colspan="2"><b>SALDO TOTAL</b></td>
                <td style="text-align: right">{{ number_format(($total_routes+$totalCashiers +$prendario->sum('amount'))- ($loans->sum('amountLoan')+$cashier_cash_out->sum('amount')+$pawns->sum('amountTotal')), 2, ',', '.') }}</td>

            </tr>

        </tbody>
    </table>
    <br>

    <h4>PRESTAMOS</h4>
    <table style="font-size: 12px; border-collapse: collapse; border: 1px solid black" >
        <tbody>
            @php
                $cont = 1;
                $totalDiario=0;
            @endphp
            @forelse ($loans as $item)
                <tr style="font-size: 14px;">
                    <td >{{$cont}}</td>
                    <td >{{$item->people->first_name}} {{$item->people->last_name1}} {{$item->people->last_name2}}</td>
                    <td style="text-align: right">{{ number_format($item->amountLoan, 2, ',', '.') }}</td>
                </tr>
                @php
                    $cont++;;
                    $totalDiario+=$item->amountLoan;
                @endphp
            @empty
                <tr>
                    <td colspan="3" style="text-align: center">No se encontraron registros.</td>
                </tr>
            @endforelse
            <tr style="font-size: 14px; background-color: #dad9d5">
                <td colspan="2"><b>TOTAL</b></td>
                <td style="text-align: right">{{ number_format($totalDiario, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
    <br>

    <h4>PRESTAMOS C/GARANTIA</h4>
    <table style="font-size: 12px; border-collapse: collapse; border: 1px solid black" >
        <tbody>
            @php
                $cont = 1;
                $totalPrenda=0;
            @endphp
            @forelse ($pawns as $item)
                <tr style="font-size: 14px;">
                    <td >{{$cont}}</td>
                    <td >{{$item->person->first_name}} {{$item->person->last_name1}} {{$item->person->last_name2}}</td>
                    <td style="text-align: right">{{ number_format($item->amountTotal, 2, ',', '.') }}</td>
                </tr>
                @php
                    $cont++;;
                    $totalPrenda+=$item->amountTotal;
                @endphp
            @empty
                <tr>
                    <td colspan="3" style="text-align: center">No se encontraron registros.</td>
                </tr>
            @endforelse
            
            <tr style="font-size: 14px; background-color: #dad9d5">
                <td colspan="2"><b>TOTAL</b></td>
                <td style="text-align: right">{{ number_format($totalPrenda, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
    <br>

    <h4>GASTOS</h4>
    <table style="font-size: 12px; border-collapse: collapse; border: 1px solid black" >
        <tbody>
            @php
                $cont = 1;
                $totalGasto=0;
            @endphp
            @forelse ($cashier_cash_out as $item)
                <tr style="font-size: 14px;">
                    <td >{{$cont}}</td>
                    <td >{{$item->cashierMovementCategory->name}}</td>
                    <td >{{$item->description}}</td>
                    <td style="text-align: right">{{ number_format($item->amount, 2, ',', '.') }}</td>
                </tr>
                @php
                    $cont++;;
                    $totalGasto+=$item->amount;
                @endphp
            @empty
                <tr>
                    <td colspan="4" style="text-align: center">No se encontraron registros.</td>
                </tr>
            @endforelse
            <tr style="font-size: 14px; background-color: #dad9d5">
                <td colspan="3"><b>TOTAL</b></td>
                <td style="text-align: right">{{ number_format($totalGasto, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>




</body>
</html>
