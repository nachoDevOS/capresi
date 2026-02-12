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
                    LISTA DE COBRANZA RECUPERACION
                </h4>
                {{-- <small style="margin-bottom: 0px; margin-top: 5px">
                    {{ date('d') }} de {{ $months[intval(date('m'))] }} de {{ date('Y') }}
                </small> --}}
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
                <th style="width:5px">N&deg;</th>
                <th style="text-align: center; width:70px">CODIGO</th>
                <th style="text-align: center; width:70px">FECHA</th>
                <th style="text-align: center">CLIENTE</th>
                <th style="text-align: center">CELULAR</th>
                <th style="text-align: center">MONTO PRESTADO</th>
                <th style="text-align: center">DEUDA</th>
            </tr>
        </thead>
        <tbody>
            @php
                $i=1;
                $debtTotal=0;
            @endphp
            @forelse ($data as $item)
                <tr>
                    <td>{{$i}}</td>
                    <td>{{$item->code}}</td>
                    <td>{{$item->dateDelivered}}</td>
                    <td style="text-align: left">{{ $item->people->last_name1}} {{ $item->people->last_name2}} {{ $item->people->first_name}}</td>
                    <td style="text-align: center">
                        @if ($item->people->cell_phone)
                            {{ $item->people->cell_phone }}
                        @elseif($item->people->phone)
                            {{ $item->people->phone }}
                        @endif
                    </td>
                    <td style="text-align: right"><b>{{ number_format($item->amountLoan,2,',','.') }}</b></td>
                    <td style="text-align: right"><b>{{ number_format($item->debt,2,',','.') }}</b></td>
                </tr>                
                @php
                    $i++;
                    $debtTotal+=$item->debt;
                @endphp
            @empty
                <tr style="text-align: center">
                    <td colspan="7">No se encontraron registros.</td>
                </tr>
            @endforelse
            <tr>
                <td colspan="6" style="text-align: left"><b>TOTAL POR COBRAR</b></td>
                <td style="text-align: right"><b>Bs. {{ number_format($debtTotal,2,',','.') }}</b></td>
            </tr>
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
