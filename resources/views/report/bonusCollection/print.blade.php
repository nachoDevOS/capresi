@extends('layouts.template-print')

@section('page_title', 'Reporte')

@section('content')
    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>

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
                    REPORTE EFECTIVO COBRANZAS   
                    {{-- <br>
                    @if ($start == $finish)
                        {{ $start }}
                    @else
                        {{ $start }} Al {{ $finish }}
                    @endif               --}}
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
    <br>
    <table style="width: 100%; font-size: 12px" border="1" cellspacing="0" cellpadding="5">
        <thead>
            <tr>
                <th style="text-align: center">RECAUDACION</th>                        
                <th style="text-align: center">RUTA X C</th>
                <th style="text-align: center">COBRADO</th>
                <th style="text-align: center">% COBRANZA</th>
                <th style="text-align: center">GASTOS, QR Y REPROG.</th>
                <th style="text-align: center">EFECTIVO Bs.</th>
            </tr>
        </thead>
        <tbody>
            @php
                $count = 1;
                $route=0;
                $payment = 0;
                $porcentage =0;
                $totalQr=0;
                $totalEfectivo=0;
                $totalCashiers=0;
            @endphp
            @forelse ($datas as $item)
                @php
                    $agent_id = $item->agent_id;
                    $loanDay = \App\Models\LoanDayAgent::where('deleted_at', null)
                        ->where('agent_id', $agent_id)
                        ->whereDate('created_at', '>=', $start)
                        ->whereDate('created_at', '<=', $finish)
                        ->get()->SUM('amount');

                    $qr = \App\Models\LoanDayAgent::with(['transaction'])
                        ->where('deleted_at', null)
                        ->where('agent_id', $agent_id)
                        ->whereDate('created_at', '>=', $start)
                        ->whereDate('created_at', '<=', $finish)
                        ->whereHas('transaction', function($query) {
                            $query->where('type', 'Qr'); 
                        })
                        ->get()->SUM('amount');


                    $cashier_cash_out = \App\Models\CashierMovement::with(['cashier'])
                        ->where('type', 'egreso')
                        ->whereDate('created_at', '>=', $start)
                        ->whereDate('created_at', '<=', $finish)
                        ->whereHas('cashier', function($query) use($agent_id){
                            $query->where('user_id', $agent_id); 
                        })                                    
                        ->get()->sum('amount');
                @endphp
                <tr>
                    {{-- <td>{{ $count }}</td> --}}
                    <td style="text-align: left">{{ $item->route->name }} - {{ $item->agent->name }}</td>
                    <td style="text-align: right">{{ number_format($item->totalDailyPayment, 2, '.','') }}</td>
                    <td style="text-align: right">{{ number_format($loanDay, 2, '.','') }}</td>
                    <td style="text-align: right">{{ number_format(($loanDay / $item->totalDailyPayment)*100, 2, '.','') }}</td>
                    <td style="text-align: right">{{ number_format($qr+$cashier_cash_out, 2, '.','') }}</td>
                    <td style="text-align: right">{{ number_format(($loanDay-$qr-$cashier_cash_out), 2, '.','') }}</td>
                </tr>
                @php
                    $count++;
                    $route+=$item->totalDailyPayment;
                    $payment+=$loanDay;
                    $totalQr+=$qr+$cashier_cash_out;
                    $totalEfectivo+=$loanDay-$qr-$cashier_cash_out;
                @endphp
            @empty
                <tr style="text-align: center">
                    <td colspan="5">No se encontraron registros.</td>
                </tr>
            @endforelse
            <tr>
                <td style="text-align: left">Total</td>
                <td style="text-align: right">{{ number_format($route, 2, '.','') }}</td>
                <td style="text-align: right">{{ number_format($payment, 2, '.','') }}</td>
                <td style="text-align: right">{{ number_format(($payment / $route)*100, 2, '.','') }} %</td>
                <td style="text-align: right">{{ number_format($totalQr, 2, '.','') }}</td>
                <td style="text-align: right">{{ number_format($totalEfectivo, 2, '.','') }}</td>
            </tr>
            @foreach ($cashiers as $item)
                @if ($item->amount>0)
                    <tr>
                        <td colspan="5" style="text-align: center" >{{ $item->name }}</td>
                        <td style="text-align: right">{{ number_format($item->amount, 2, '.','') }}</td>

                    </tr>
                    @php
                        $totalEfectivo-=$item->amount;
                    @endphp
                @endif
            @endforeach
            <tr>
                <th colspan="5" style="text-align: center">SALDO EFECTIVO Bs.-</th>
                <th style="text-align: right">{{ number_format($totalEfectivo, 2, '.','') }}</th>
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

@section('javascript')

    {{-- <script>

        $(document).ready(function() {

            
        });
    </script> --}}
@stop
