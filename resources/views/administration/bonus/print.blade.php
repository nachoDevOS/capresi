@extends('layouts.template-print')

@section('page_title', 'Planilla de Pago de Aguinaldo')

@section('content')
    @php
        $months = array('', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');    
        $monthList = [
                '1' => 'Enero',
                '2' => 'Febrero',
                '3' => 'Marzo',
                '4' => 'Abril',
                '5' => 'Mayo',
                '6' => 'Junio',
                '7' => 'Julio',
                '8' => 'Agosto',
                '9' => 'Septiembre',
                '10' => 'Octubre',
                '11' => 'Noviembre',
                '12' => 'Diciembre'
            ];
    @endphp

    <table width="100%">
        <tr>
            <td style="width: 20%"><img src="{{ asset('images/icon.png') }}" alt="CAPRESI" width="70px"></td>
            <td style="text-align: center;  width:50%">
                <h3 style="margin-bottom: 0px; margin-top: 5px">
                    EMPRESA "CAPRESI"<br>
                </h3>
                <h4 style="margin-bottom: 0px; margin-top: 5px">
                    PLANILLA DE AGUINALDO DEl {{$bonuses->year }}
                    {{-- Stock Disponible {{date('d/m/Y', strtotime($start))}} Hasta {{date('d/m/Y', strtotime($finish))}} --}}
                </h4>
                <small style="margin-bottom: 0px; margin-top: 5px; font-size: 10px">
                        {{-- {{ date('d', strtotime($date)) }} DE {{ strtoupper($months[intval(date('m', strtotime($date)))] )}} DE {{ date('Y', strtotime($date)) }} --}}
                </small>
            </td>
            <td style="text-align: right; width:30%">
                <h3 style="margin-bottom: 0px; margin-top: 5px">
                    <div id="qr_code">
                        {!! QrCode::size(80)->generate('Planilla de pago de aguinaldo del '.$bonuses->year); !!}
                    </div>
                    <small style="font-size: 8px; font-weight: 100">Impreso por: {{ Auth::user()->name }} {{ date('d/m/Y H:i:s') }}</small>
                </h3>
            </td>
        </tr>
    </table>
    {{-- <table style="width: 100%; font-size: 8px" border="1" cellspacing="0" cellpadding="4"> --}}
    <table style="width: 100%; font-size: 10px" border="1" class="print-friendly" cellspacing="0" cellpadding="2">

        <thead>
            <tr>
                <th style="width:3%">N&deg;</th>   
                <th style="text-align: center; width:15%">CARNET DE IDENTIDAD</th>
                <th style="text-align: center; width:40%">APELLIDO Y NOMBRE</th>
                <th style="text-align: center; width:9%">MONTO</th>
                <th style="text-align: center; width:15%">FIRMA.</th>
            </tr>
        </thead>
        <tbody>
            @php
                $count = 1;
                $total = 0;
            @endphp
            @forelse ($bonuses->bonuDetail as $item)
                <tr>
                    <td>{{ $count }}</td>
                    <td><b>{{ $item->people->ci}}</b></td>
                    <td>                        
                        {{ strtoupper($item->people->last_name1)}} {{ strtoupper($item->people->last_name2)}} {{strtoupper($item->people->first_name)}} 
                        @if ($item->paid==1)
                            <label style="background-color: rgb(7, 199, 0);" class="label label-success">PAGADO</label> 
                        @endif
                    </td>
                    <td style="text-align: right">{{ number_format($item->payment, 2, '.', '') }}</td>
                    <td></td>
                                                                            
                </tr>
                @php
                    $count++;                 
                    $total+= number_format($item->payment, 2, '.', '');                    
                @endphp
            @empty
                <tr style="text-align: center">
                    <td colspan="5">No se encontraron registros.</td>
                </tr>
            @endforelse 
            <tr>
                <th colspan="3" style="text-align: right">Total</th>
                <td style="text-align: right"><strong>Bs. {{ number_format($total,2, ',', '.') }}</strong></td>
                <td></td>
            </tr>
        </tbody>       
       

    </table>

    <br>
    <br>
    <table width="100%" style="font-size: 9px">
        <tr>
            <td style="text-align: center">
                ______________________
                <br>
                <b>Entregado Por</b><br>
                <b>{{ Auth::user()->name }}</b><br>
                <b>CI: {{ Auth::user()->ci }}</b>
            </td>
            
        </tr>
    </table>
    <script>

    </script>

@endsection
@section('css')
    <style>
        table, th, td {
            border-collapse: collapse;
        }
        /* @media print { div{ page-break-inside: avoid; } }  */
          
        table.print-friendly tr td, table.print-friendly tr th {
            page-break-inside: avoid;
        }
          
    </style>
@stop
