@extends('layouts.template-print')

@section('page_title', 'Reporte')

@section('content')
    @php
        $months = array('', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic');    
    @endphp

    <table width="100%">
        <tr>
            <td style="width: 15%"><img src="{{ asset('images/icon.png') }}" alt="CAPRESI" width="70px"></td>
            <td style="text-align: center;  width:70%">
                <h3 style="margin-bottom: 0px; margin-top: 5px">
                    EMPRESA "CAPRESI"<br>
                </h3>
                <h4 style="margin-bottom: 0px; margin-top: 5px">
                    LISTA DE COBRANZA
                    <br>
                    {{ $data->route->name }}
                </h4>
                <small style="margin-bottom: 0px; margin-top: 5px">
                    {{ date('d', strtotime($data->created_at) ) }} de {{ $months[intval(date('m', strtotime($data->created_at)))] }} de {{ date('Y', strtotime($data->created_at)) }}
                </small>
            </td>
            <td style="text-align: right; width:15%">
                <h3 style="margin-bottom: 0px; margin-top: 5px">
                   
                    <small style="font-size: 11px; font-weight: 100">Impreso por: {{ Auth::user()->name }} <br> {{ date('d/m/Y H:i:s') }}</small>
                </h3>
            </td>
        </tr>
    </table>
    <table style="width: 100%; font-size: 8px" border="1" cellspacing="0" cellpadding="4">
        <thead>
            <tr>
                <th rowspan="2" style="width:5px">N&deg;</th>
                <th rowspan="2" style="text-align: center; width:70px">CODIGO</th>
                <th rowspan="2" style="text-align: center">CLIENTE</th>
                <th rowspan="2" style="text-align: center">CELULAR</th>
                <th rowspan="2" style="text-align: center">DURACIÓN</th>
                <th rowspan="2" style="text-align: center; width: 50px">PAGO EN <br> EL DIA</th>
                <th rowspan="2" style="width: 80px">OBSERVACIONES</th>
                <th colspan="3" style="text-align: center">RETRASO</th>
            </tr>
            <tr>
                <th style="text-align: center; width:40px">DIAS</th>
                <th style="text-align: center; width:40px">TOTAL A PAGAR</th>
            </tr>
        </thead>
        <tbody>
            @php
                $count = 1;
                $pago_diario = 0;
                $pago_atrasado = 0;
                $pago_pospuestos = 0;
                $date = Illuminate\Support\Carbon::now();

            @endphp
            @forelse ($data->details as $item)
                <tr style="text-align: center;">
                    <td>{{ $count }}</td>
                    <td style="text-align: center"><b>{{ $item->loan->code}}</b></td>
                    <td style="text-align: left">{{ $item->loan->people->last_name1}} {{ $item->loan->people->last_name2}} {{ $item->loan->people->first_name}}</td>
                    <td style="text-align: center"><b>{{ $item->loan->cell_phone}}</b></td>
                    <td>
                        @php
                            $dias = App\Models\LoanDay::where('loan_id', $item->loan->id)->get();
                            $inicio = $dias->sortBy('date')->first()->date;
                            $fin = $dias->sortByDesc('date')->first()->date;
                        @endphp
                        @if (date('Y', strtotime($inicio)) == date('Y', strtotime($fin)))
                            {{ date('d', strtotime($inicio)) }}/{{ $months[intval(date('m', strtotime($inicio)))] }} al {{ date('d', strtotime($fin)) }}/{{ $months[intval(date('m', strtotime($fin)))] }} de {{ date('Y', strtotime($fin)) }}
                        @else
                            {{ date('d', strtotime($inicio)) }}/{{ $months[intval(date('m', strtotime($inicio)))] }}/{{ date('Y', strtotime($inicio)) }} al {{ date('d', strtotime($fin)) }}/{{ $months[intval(date('m', strtotime($fin)))] }}/{{ date('Y', strtotime($fin)) }}
                        @endif
                    </td>
                    <td style="text-align: right">
                        <b>
                            {{ number_format($item->dailyPayment,2,',','.') }}
                        </b>
                    </td>

                    <td style="text-align: right">
                        {{ $item->typeLoan }}
                    </td>
                    <td style="text-align: right; background-color: {{$item->color}}">
                        {{ $item->lateDays }}
                    </td>

                    <td style="text-align: right; background-color: {{$item->color}}">
                        <b>
                            {{ number_format($item->latePayment,2,',','.') }}
                        </b>
                    </td>

                </tr>
                @php
                    $pago_diario+=$item->dailyPayment;
                    $pago_atrasado+=$item->latePayment;
                    $count++;
                @endphp
               
               
            @empty
                <tr style="text-align: center">
                    <td colspan="9">No se encontraron registros.</td>
                </tr>
            @endforelse
            <tr>
                <td colspan="5" style="text-align: right"><b>Monto del Día</b></td>
                <td style="text-align: right"><b>Bs. {{ number_format($pago_diario,2,'.','') }}</b></td>
                <td colspan="2" style="text-align: right"><b>Monto Atrasado</b></td>
                <td style="text-align: right"><b>Bs. {{ number_format($pago_atrasado,2,'.','') }}</b></td>
            </tr>
            <tr>
                <td colspan="8" style="text-align: right"><b>TOTAL POR COBRAR</b></td>
                <td style="text-align: right"><b>Bs. {{ number_format($pago_diario+$pago_atrasado,2,'.','') }}</b></td>
            </tr>
            {{-- <tr>
                <td colspan="8" style="text-align: right"><b>OTROS PAGOS</b></td>
                <td style="text-align: right"><b>Bs. {{ $pago_pospuestos }}</b></td>
            </tr> --}}
        </tbody>
    </table>

@endsection

@section('css')
    <style>
        table, th, td {
            border-collapse: collapse;
        }
    </style>
@stop