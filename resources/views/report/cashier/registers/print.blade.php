@extends('layouts.template-print')

@section('page_title', 'Reporte Ingresos/Egreso')

@section('content')
    @php
        $months = array('', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic');    
    @endphp

    <table width="100%">
        <tr>
            <td style="width: 30%"><img src="{{ asset('images/icon.png') }}" alt="CAPRESI" width="70px"></td>
            <td style="text-align: center;  width:40%">
                <h3 style="margin-bottom: 0px; margin-top: 5px">
                    EMPRESA "CAPRESI"<br>
                </h3>
                <h4 style="margin-bottom: 0px; margin-top: 5px">
                    LISTA DE {{ Str::upper($type) }}
                </h4>
                <small style="margin-bottom: 0px; margin-top: 5px">
                    @if ($start)
                    Desde {{ date('d', strtotime($start)) }} de {{ $months[intval(date('m', strtotime($start)))] }} de {{ date('Y', strtotime($start)) }}
                    @endif
                    @if ($finish)
                    hasta {{ date('d', strtotime($finish)) }} de {{ $months[intval(date('m', strtotime($finish)))] }} de {{ date('Y', strtotime($finish)) }}
                    @endif
                </small>
                <br>
                <small>
                    {{$user->name}}
                </small>
            </td>
            <td style="text-align: right; width:30%">
                <h3 style="margin-bottom: 0px; margin-top: 5px">
                   
                    <small style="font-size: 11px; font-weight: 100">Impreso por: {{ Auth::user()->name }} <br> {{ date('d/m/Y H:i:s') }}</small>
                </h3>
            </td>
        </tr>
    </table>

    @if ($show_details==1)
    <table style="width: 100%; font-size: 8px" border="1" cellspacing="0" cellpadding="4">
        <thead>
            <tr>
                <th style="width:5px">N&deg;</th>
                <th style="text-align: center">FECHA</th>
                <th style="text-align: center">DESCRIPCIÓN</th>
                <th style="text-align: right">MONTO</th>
            </tr>
        </thead>
        <tbody>
            @php
                $count = 1;
                $total = 0;
            @endphp
            @forelse ($movements as $item)
                <tr>
                    <td>{{ $count }}</td>
                    <td>{{ date('d/m/Y', strtotime($item->created_at)) }}</td>
                    <td>{{ $item->description }}</td>
                    <td style="text-align: right">{{ number_format($item->amount, 2, ',', '.') }}</td>
                </tr>
                @php
                    $count++;
                    $total += $item->amount;
                @endphp
            @empty
                <tr style="text-align: center">
                    <td colspan="4">No se encontraron registros.</td>
                </tr>
            @endforelse
            <tr>
                <td colspan="3" style="text-align: right"><b style="font-weight: bold">TOTAL</b></td>
                <td style="text-align: right"><b style="font-weight: bold">Bs. {{ number_format($total, 2, ',', '.') }}</b></td>
            </tr>
        </tbody>
    </table>
    @else
        <table style="width: 100%; font-size: 8px" border="1" cellspacing="0" cellpadding="4">
            <thead>
                <tr>
                    <th style="width:5px">N&deg;</th>
                    <th style="text-align: center">CATEGORIA</th>
                    <th style="text-align: right">MONTO</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $count = 1;
                    $total = 0;
                @endphp
                @forelse ($movements as $item)
                    <tr>
                        <td>{{ $count }}</td>
                        <td>{{ $item->cashierMovementCategory?$item->cashierMovementCategory->name :'' }}</td>
                        <td style="text-align: right">{{ number_format($item->amount, 2, ',', '.') }}</td>
                    </tr>
                    @php
                        $count++;
                        $total += $item->amount;
                    @endphp
                @empty
                    <tr style="text-align: center">
                        <td colspan="3">No se encontraron registros.</td>
                    </tr>
                @endforelse
                <tr>
                    <td colspan="2" style="text-align: right"><b style="font-weight: bold">TOTAL</b></td>
                    <td style="text-align: right"><b style="font-weight: bold">Bs. {{ number_format($total, 2, ',', '.') }}</b></td>
                </tr>
            </tbody>
        </table>
    @endif

@endsection

@section('css')
    <style>
        table, th, td {
            font-size: 12px !important;
            border-collapse: collapse;
        }
    </style>
@stop