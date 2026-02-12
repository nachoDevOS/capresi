
<div class="col-md-12 text-right">
    <button type="button" onclick="report_print()" class="btn btn-dark"><i class="glyphicon glyphicon-print"></i> Imprimir</button>
</div>
<div class="col-md-12">
    <div class="panel panel-bordered">
        <div class="panel-body">
            <div class="table-responsive">
                <h4>EFECTIVO</h4>
    
                <table id="dataStyle" class="table table-bordered table-striped table-sm" style="font-size: 12px">
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
            
                        <tr style="font-size: 14px; background-color: #f3e95f">
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
                            <td style="text-align: right">{{ number_format($total_routes+$totalCashiers+$prendario->sum('amount'), 2, ',', '.') }}</td>
                        </tr>
            
                    </tbody>
                </table>
                <br>
                <table id="dataStyle" class="table table-bordered table-striped table-sm" style="font-size: 12px">
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
            
                        <tr style="font-size: 14px; background-color: #f3e95f">
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
                <table id="dataStyle" class="table table-bordered table-striped table-sm" style="font-size: 12px">
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
            
                        <tr style="font-size: 14px; background-color: #f3e95f">
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
                <table id="dataStyle" class="table table-bordered table-striped table-sm" style="font-size: 12px">
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
                <table id="dataStyle" class="table table-bordered table-striped table-sm" style="font-size: 12px; border-collapse: collapse; border: 1px solid black" >
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
                <table id="dataStyle" class="table table-bordered table-striped table-sm" style="font-size: 12px; border-collapse: collapse; border: 1px solid black" >
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
                <table id="dataStyle" class="table table-bordered table-striped table-sm" style="font-size: 12px; border-collapse: collapse; border: 1px solid black" >
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
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){

})
</script>