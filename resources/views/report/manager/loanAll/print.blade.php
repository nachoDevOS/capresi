@extends('layouts.template-print')

@section('page_title', 'Reporte')

@section('content')
    @php
        $months = array('', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');    
    @endphp

    <table width="100%">
        <tr>
            <td style="width: 20%"><img src="{{ asset('images/icon.png') }}" alt="CAPRESI" width="70px"></td>
            <td style="text-align: center;  width:50%">
                <h3 style="margin-bottom: 0px; margin-top: 5px">
                    EMPRESA "CAPRESI"<br>
                </h3>
                <h4 style="margin-bottom: 0px; margin-top: 5px">
                    @if ($ok=='todo')
                        REPORTE DETALLADO DE TODOS LOS PRESTAMOS
                    @endif
                    @if ($ok=='enpago')
                        REPORTE DETALLADO DE LOS PRESTAMOS EN PAGOS VIGENTE
                    @endif
                    @if ($ok=='pagado')
                        REPORTE DETALLADO DE LOS PRESTAMOS PAGADOS
                    @endif
                </h4>
                <small style="margin-bottom: 0px; margin-top: 5px">
                    @if ($start == $finish)
                        {{ date('d', strtotime($start)) }} de {{ $months[intval(date('m', strtotime($start)))] }} de {{ date('Y', strtotime($start)) }}
                    @else
                        {{ date('d', strtotime($start)) }} de {{ $months[intval(date('m', strtotime($start)))] }} de {{ date('Y', strtotime($start)) }} Al {{ date('d', strtotime($finish)) }} de {{ $months[intval(date('m', strtotime($finish)))] }} de {{ date('Y', strtotime($finish)) }}
                    @endif
                </small>
            </td>
            <td style="text-align: right; width:30%">
                <h3 style="margin-bottom: 0px; margin-top: 5px">
                    <div id="qr_code">
                        {{-- @if ($start != $finish)
                            {!! QrCode::size(80)->generate('Total Cobrado: Bs'.number_format($amountTotal,2, ',', '.').', Recaudado en Fecha '.date('d', strtotime($start)).' de '.strtoupper($months[intval(date('m', strtotime($start)))] ).' de '.date('Y', strtotime($start)).' al '.date('d', strtotime($finish)).' de '.strtoupper($months[intval(date('m', strtotime($finish)))] ).' de '.date('Y', strtotime($finish))); !!}
                        @else
                            {!! QrCode::size(80)->generate('Total Cobrado: Bs'.number_format($amountTotal,2, ',', '.').', Recaudado en Fecha '.date('d', strtotime($start)).' de '.strtoupper($months[intval(date('m', strtotime($start)))] ).' de '.date('Y', strtotime($start))); !!}
                        @endif --}}
                    </div>
                    <small style="font-size: 8px; font-weight: 100">Impreso por: {{ Auth::user()->name }} <br> {{ date('d/m/Y H:i:s') }}</small>
                </h3>
            </td>
        </tr>
    </table>
    
    <table style="width: 100%; font-size: 10px" border="1" class="print-friendly" cellspacing="0" cellpadding="2">
        <thead>
            <tr>
                <th style="width:5px">N&deg;</th>
                <th style="text-align: center">FECHA DE ENTREGA</th>
                <th style="text-align: center">CODIGO</th>                        
                <th style="text-align: center">CI</th>
                <th style="text-align: center">CLIENTE</th>
                <th style="text-align: center">ENTREGADO POR</th>
                <th style="text-align: center">DIAS A PAGAR</th>
                <th style="text-align: center">INTERES A PAGAR (%)</th>
                <th style="text-align: center">MONTO PRESTADO</th>
                <th style="text-align: center">INTERES A PAGAR (Bs.)</th>
                <th style="text-align: center">TOTAL A PAGAR</th>
                <th style="text-align: center">DEUDA TOTAL</th>
                <th style="text-align: center">DEUDA CON MORA</th>
            </tr>
        </thead>
        <tbody>
            @php
                $count = 1;
                $amountLoan = 0;
                $amountTotal = 0;
                $amountPorcentage = 0;
                $debt = 0;
                $total_monto_mora = 0;
            @endphp
            @forelse ($data as $item)
                @php
                    $monto_mora = $item->loanDay->where('status', 1)->where('late', 1)->sum('debt');
                    $total_monto_mora += $monto_mora;
                    $ultima_cuota = $item->loanDay->sortByDesc('date')->first();
                @endphp
                <tr @if($ultima_cuota->date < date('Y-m-d') && $ultima_cuota->debt > 0) style="background-color: rgba(231, 76, 60, 0.5)" @elseif($item->porcentage == 0) style="background-color: rgba(36, 113, 163, 0.5)" @endif>
                    <td>{{ $count }}</td>
                    <td style="text-align: center">{{date('d/m/Y', strtotime($item->dateDelivered))}}</td>
                    <td style="text-align: center"><small>{{ $item->code}}</small></td>
                    <td>{{ $item->ci }}</td>
                    <td>{{ strtoupper($item->people->first_name)}} {{ strtoupper($item->people->last_name1)}} {{ strtoupper($item->people->last_name2)}}</td>
                    <td>{{ strtoupper($item->agentDelivered->name)}}</td>
                    <td style="text-align: right">{{ $item->day }} Días</td>
                    <td style="text-align: right">{{ number_format($item->porcentage, 2, ',','.') }}</td>
                    <td style="text-align: right">{{ number_format($item->amountLoan, 2, ',','.') }}</td>
                    <td style="text-align: right">{{ number_format($item->amountPorcentage, 2, ',','.') }}</td>
                    <td style="text-align: right">{{ number_format($item->amountTotal, 2, ',','.') }}</td>
                    <td style="text-align: right">{{ number_format($item->debt, 2, ',','.') }}</td>
                    <td style="text-align: right">{{ number_format($monto_mora, 2, ',','.') }}</td>
                </tr>
                @php
                    $count++;
                    $amountTotal+= $item->amountTotal;          
                    $amountLoan+= $item->amountLoan;          
                    $amountPorcentage+= $item->amountPorcentage;          
                    $debt+= $item->debt;
                @endphp
            @empty
                <tr style="text-align: center">
                    <td colspan="12">No se encontraron registros.</td>
                </tr>
            @endforelse
            <tr>
                <td colspan="6" style="text-align: right">Total</td>
                <td style="text-align: right"></td>
                <td style="text-align: right"></td>
                <td style="text-align: right"><b>{{ number_format($amountLoan,2, ',', '.') }}</b></td>
                <td style="text-align: right"><b>{{ number_format($amountPorcentage,2, ',', '.') }}</b></td>
                <td style="text-align: right"><b>{{ number_format($amountTotal,2, ',', '.') }}</b></td>
                <td style="text-align: right"><b>{{ number_format($debt,2, ',', '.') }}</b></td>
                <td style="text-align: right"><b>{{ number_format($total_monto_mora, 2, ',', '.') }}</b></td>
            </tr>
        </tbody>
    </table>

@endsection
@section('css')
    <style>
        table, th, td {
            border-collapse: collapse;
        }
          
        table.print-friendly tr td, table.print-friendly tr th {
            page-break-inside: avoid;
        }
    </style>
@stop
