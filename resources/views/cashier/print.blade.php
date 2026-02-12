@extends('layouts.template-print-letter')

@section('page_title', 'Cierre de caja')

@section('qr_code')
    <div id="qr_code" class="text-right">
        {!! QrCode::size(80)->generate('Caja Nro '.$cashier->id.', usuario '.$cashier->user->name.'. Monto de cierre '.$cashier->amount_real); !!} <br>
        <strong>Caja N&deg; {{ $cashier->id }}</strong> <br>
        <small>{{ date('d/m/Y H:i') }}</small>
    </div>
@endsection

@section('content')
    <div class="content">
        <table width="100%">
            <tr>
                <td><b>Descripción</b></td>
                <td>{{ $cashier->title }}</td>
                <td><b>Cajero</b></td>
                <td>{{ $cashier->user->name }}</td>
            </tr>
            <tr>
                <td><b>Observaciones</b></td>
                <td>{{ $cashier->observations ?? 'Ninguna' }}</td>
            </tr>
            @if ($cashier->amount)
            <tr>
                <td><b>Monto de cierre</b></td>
                <td><b>{{ $cashier->amount_real }}</b></td>
                <td><b>Saldo</b></td>
                <td><b class="@if($cashier->balance > 0) text-success @endif @if($cashier->balance < 0) text-danger @endif">{{ $cashier->balance }}</b></td>
            </tr>
            @endif
        </table>
        <br>
        <table class="table">
            <thead>
                <tr>
                    <th colspan="6">DINERO ABONADO</th>
                </tr>
                <tr>
                    <th>N&deg;</th>
                    <th>ID</th>
                    <th>Hora de Registro</th>
                    <th>Registrado Por</th>
                    <th>Detalle</th>
                    <th style="width: 80px">Monto</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $cont = 1;
                    $ingresos_totales = 0;
                @endphp
                @forelse ($cashier->movements->where('type', 'ingreso') as $item)
                    <tr>
                        <td>{{ $cont }}</td>
                        <td>{{ $item->id }}</td>
                        <td>{{ date('H:i:s', strtotime($item->created_at))}}</td>
                        <td>{{ $item->user->name }}</td>
                        <td>{{ $item->description }}</td>
                        <td class="text-right">{{ $item->amount }}</td>
                    </tr>
                    @php
                        $cont++;
                        $ingresos_totales += $item->amount;
                    @endphp
                @empty
                    <tr>
                        <td class="text-center" colspan="6">No hay datos</td>
                    </tr>
                @endforelse
                <tr>
                    <td colspan="5"><b>TOTAL</b></td>
                    <td class="text-right"><b>{{ number_format($ingresos_totales, 2, '.', '') }}</b></td>
                </tr>
            </tbody>
        </table>
        <br>
        <table class="table">
            <thead>
                <tr>
                    <th colspan="6">COBROS REALIZADOS</th>
                </tr>
                <tr>
                    <th>N&deg;</th>
                    <th>N&deg; Transacción</th>                                                    
                    <th>Código</th>
                    <th>Hora Pago</th>
                    <th>Cliente</th>
                    <th style="width: 80px">Monto Cobrado</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $cont = 1;
                    $total_movements = 0;
                    $total_movements_qr = 0;
                    $total_movements_deleted = 0;
                @endphp
                @forelse ($trans as $item)
                    <tr>
                        <td>{{ $cont }}</td>
                        <td>{{$item->transaction}}</td>
                        <td>{{$item->code}} <br>
                            @if ($item->deleted_at || $item->eliminado)
                                @if ($item->eliminado)
                                    <label class="label label-danger">Prestamos eliminado</label>
                                    <label class="label label-success">Transaccion activa</label>                                                        
                                @else
                                    <label class="label label-danger">Transaccion eliminada</label>                                                        
                                @endif
                            @endif
                            @if ($item->transaction_type != 'Efectivo')
                                <label class="label label-primary">Qr/Transferencia</label>  
                            @endif
                        </td>
                        <td>{{date('H:i:s', strtotime($item->created_at))}}</td>
                        <td>
                            <small>CI:</small> {{$item->ci?$item->ci:'No definido'}} <br>
                            {{$item->first_name}} {{$item->last_name1}} {{$item->last_name2}}
                        </td>
                        <td class="text-right">{{ $item->amount }}</td>
                    </tr>
                    @php
                        $cont++;
                        if(!$item->deleted_at){
                            if($item->transaction_type == 'Efectivo'){
                                $total_movements += $item->amount;
                            }else{
                                $total_movements_qr += $item->amount;
                            }
                        }else{
                            $total_movements_deleted += $item->amount;
                        }
                    @endphp
                @empty
                    <tr>
                        <td colspan="6">No hay datos disponibles en la tabla</td>
                    </tr>
                @endforelse
                <tr>
                    <td colspan="5" class="text-right"><span class="text-danger"><b>TOTAL ANULADO</b></span></td>
                    <td class="text-right"><b class="text-danger">{{ number_format($total_movements_deleted, 2, '.', '') }}</b></td>
                </tr>
                <tr>
                    <td colspan="5" class="text-right"><b>TOTAL COBROS</b></td>
                    <td class="text-right"><b>{{ number_format($total_movements_qr + $total_movements, 2, '.', '') }}</b></td>
                </tr>
                <tr>
                    <td colspan="5" class="text-right"><b>TOTAL QR/TRANSFERENCIA</b></td>
                    <td class="text-right"><b>{{ number_format($total_movements_qr, 2, '.', '') }}</b></td>
                </tr>
                <tr>
                    <td colspan="5" class="text-right"><b>TOTAL EFECTIVO</b></td>
                    <td class="text-right"><b>{{ number_format($total_movements, 2, '.', '') }}</b></td>
                </tr>
            </tbody>
        </table>
        <br>
        <table class="table">
            <thead>
                <tr>
                    <th colspan="8">PRESTAMOS ENTREGADOS</th>
                </tr>
                <tr>
                    <th>N&deg;</th>
                    <th>Codigo</th>
                    <th>Fecha Solicitud</th>
                    <th>Fecha Entrega</th>
                    <th>Nombre Completo</th>
                    <th class="text-right">Monto Prestado</th>
                    <th class="text-right">Interes a Cobrar</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $cont = 1;
                    $loans=0;
                    $interes =0;
                    $total = 0;
                @endphp
                @foreach ($loan as $item)
                    <tr>
                        <td>{{ $cont }}</td>
                        <td>
                            {{ $item->code }}<br>
                            @if ($item->deleted_at)
                                <label class="label label-danger">Anulado</label>
                            @endif
                        </td>
                        <td>{{ $item->date}}</td>
                        <td>{{ $item->dateDelivered}}</td>
                        <td>
                            <small>CI:</small> {{ $item->people->ci}} <br>
                            <p>{{ $item->people->first_name}} {{ $item->people->last_name1}} {{ $item->people->last_name2}}</p>
                            
                        </td>
                        <td style="text-align: right">
                            @if ($item->deleted_at)
                               <del>{{ number_format($item->amountLoan, 2, '.', '') }}</del>
                            @else
                               {{ number_format($item->amountLoan, 2, '.', '') }}
                            @endif
                        </td>
                        <td style="text-align: right">
                            @if ($item->deleted_at)
                               <del>{{ number_format($item->amountPorcentage, 2, '.', '') }}</del>
                            @else
                               {{ number_format($item->amountPorcentage, 2, '.', '') }}
                            @endif
                        </td>
                        <td style="text-align: right">
                            @if ($item->deleted_at)
                               <del>{{ number_format($item->amountTotal, 2, '.', '') }}</del>
                            @else
                               {{ number_format($item->amountTotal, 2, '.', '') }}
                            @endif
                        </td>
                    </tr>
                    @php
                        $cont++;
                        if (!$item->deleted_at) {
                            $interes = $interes + $item->amountPorcentage;
                            $loans = $loans + $item->amountLoan;
                            $total = $total + $item->amountTotal;
                        }
                    @endphp
                @endforeach
                <tr>
                    <td colspan="5" style="text-align: right"><b>TOTAL</b></td>
                    <td style="text-align: right"><b>{{ number_format($loans, 2, '.', '') }}</b></td>
                    <td style="text-align: right"><b>{{ number_format($interes, 2, '.', '') }}</b></td>
                    <td style="text-align: right"><b>{{ number_format($total, 2, '.', '') }}</b></td>
                </tr>
            </tbody>
        </table>
        <br>
        <table class="table">
            <thead>
                <tr>
                    <th colspan="5">GASTOS REALIZADOS</th>
                </tr>
                <tr>
                    <th>ID</th>
                    <th>Fecha y Hora de Registro</th>
                    <th>Registrado Por</th>
                    <th>Detalle</th>
                    <th class="text-right">Monto</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $gastos_totales = 0;
                @endphp
                @foreach ($cashier->movements->where('type', 'egreso') as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ date('d/m/Y H:i:s', strtotime($item->created_at))}}<br><small>{{\Carbon\Carbon::parse($item->created_at)->diffForHumans() }}</small></td>
                        <td>{{ $item->user->name }}</td>
                        <td>{{ $item->description }}</td>
                        <td style="text-align: right">{{ $item->amount }}</td>
                    </tr>
                    @php
                        $gastos_totales += $item->amount;
                    @endphp
                @endforeach
                <tr>
                    <td colspan="4"><b>TOTAL</b></td>
                    <td class="text-right"><b>{{ number_format($gastos_totales, 2, '.', '') }}</b></td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection

@section('css')
    <style>
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            border: 1px solid;
            padding: 3px 5px;
        }
        .text-right {
            text-align: right
        }
        .label {
            border-radius: 5px;
            color: white;
            padding: 1px 3px;
            font-size: 10px
        }
        .label-primary {
            background-color: #1F618D;
        }
        .label-danger {
            background-color: #A93226;
        }
        .label-success {
            background-color: #229954;
        }
        .text-danger {
            color: #A93226;
        }
        .text-success {
            color: #229954;
        }
    </style>
@endsection