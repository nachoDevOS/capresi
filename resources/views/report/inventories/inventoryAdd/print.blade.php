@extends('layouts.template-print')

@section('page_title', 'Reporte')

@section('content')
    @php
        $months = array('', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');    
    @endphp

    <table width="100%">
        <tr>
            <td style="width: 20%"><img src="{{ asset('images/icon.png') }}" alt="CAPRESI" width="70px"></td>
            <td style="text-align: center;  width:60%">
                <h3 style="margin-bottom: 0px; margin-top: 5px">
                    EMPRESA "CAPRESI"<br>
                </h3>
                <h4 style="margin-bottom: 0px; margin-top: 5px">
                    REPORTE DE INGRESO DE ARTICULOS A INVENTARIO  <br>
                </h4>
                <h5 style="margin-bottom: 0px; margin-top: 5px">
                    INGRESADO POR: 
                    @if ($typePrint == 'Prendario' || $typePrint == '')
                        REGISTRO DE EMPEÑO
                    @endif
                    @if ($typePrint == '')
                        Y
                    @endif
                    @if ($typePrint == 'Manual' || $typePrint == '')
                        REGISTRO MANUAL
                    @endif
                    {{-- Stock Disponible {{date('d/m/Y', strtotime($start))}} Hasta {{date('d/m/Y', strtotime($finish))}} --}}
                </h5>
            
                <small style="margin-bottom: 0px; margin-top: 5px">
                    @if ($start == $finish)
                        {{ date('d', strtotime($start)) }} de {{ $months[intval(date('m', strtotime($start)))] }} de {{ date('Y', strtotime($start)) }}
                    @else
                        {{ date('d', strtotime($start)) }} de {{ $months[intval(date('m', strtotime($start)))] }} de {{ date('Y', strtotime($start)) }} Al {{ date('d', strtotime($finish)) }} de {{ $months[intval(date('m', strtotime($finish)))] }} de {{ date('Y', strtotime($finish)) }}
                    @endif
                </small>

                {{-- <br>
                <small style="font-size: 10px">
                    COBRADO POR: {{strtoupper($agent)}}
                </small>
                <br>
                <small style="font-size: 10px">
                    @if ($prestamos == 'diario' || $prestamos == 'todo')
                        <b>TOTAL COBRADO DIARIO Bs.</b> {{ number_format($amountDiario,2, '.', '') }}
                        <br>
                    @endif
                    @if ($prestamos == 'prenda' || $prestamos == 'todo')
                        <b>TOTAL COBRADO PRENDARIO Bs.</b> {{ number_format($amountPrendario,2, '.', '') }}
                        <br>
                    @endif
                </small> --}}
            </td>
            <td style="text-align: right; width:20%">
                <h3 style="margin-bottom: 0px; margin-top: 5px">
                    <small style="font-size: 8px; font-weight: 100">Impreso por: {{ Auth::user()->name }} <br> {{ date('d/m/Y H:i:s') }}</small>
                </h3>
            </td>
        </tr>
    </table>
    {{-- <table style="width: 100%; font-size: 8px" border="1" cellspacing="0" cellpadding="4"> --}}

        <table style="width: 100%; font-size: 10px" border="1" class="print-friendly" cellspacing="0" cellpadding="2">
            <thead>
                <th style="width:5px">N&deg;</th>
                <th style="text-align: center">CODIGO</th>
                <th style="text-align: center">DETALLE DEL ARTICULO</th>
                <th style="text-align: center">FECHA DE INGRESO</th>
                <th style="text-align: center">REGISTRADO POR</th>
                <th style="text-align: center">CANTIDAD</th>
                <th style="text-align: center">PRECIO</th>
                <th style="text-align: center">TOTAL</th>
            </thead>
            <tbody>
                @php
                    $count = 1;
                    $total = 0;
                @endphp
                @forelse ($inventories as $item)
                    <tr>
                        <td>{{ $count }}</td>
                        <td>{{ $item->code }}</td>
                        <td>
                            <table style="width: 100%" class="table">
                                @php
                                    $image = asset('images/default.jpg');
                                    if($item->image){
                                        $image = asset('storage/'.str_replace('.', '-cropped.', $item->image));
                                    }                                                                                                                       
                                @endphp
                                <tr>
                                    <td style="width: 10%;"><img src="{{ $image }}" alt="{{ $item->image }} " style="width: 60px; height: 60px; border-radius: 30px; margin-right: 10px"></td>
                                    <td>
                                        <ul>
                                            <li style="font-size: 15px">
                                                <small>
                                                    {{ floatval($item->quantity) == intval($item->quantity) ? intval($item->quantity) : $item->quantity }}
                                                {{ $item->item->unit }} {{ $item->item->name }} a {{ floatval($item->price) == intval($item->price) ? intval($item->price) : $item->price }}
                                                Bs.
                                                </small>
                                            </li>
                                        </ul>
                                        @php
                                            $features_list = '';
                                            foreach ($item->features as $feature) {
                                                if ($feature->value) {
                                                    $features_list .= '<span><b>'.$feature->title.'</b>: '.$feature->value.'</span><br>';
                                                }
                                            }
                                        @endphp
                                        {!! $features_list !!}
                                    </td>
                                </tr>
                            </table>
                        </td>

                        <td style="text-align: center">
                            {{date('d/m/Y h:i:s a', strtotime($item->created_at))}} <br>
                            Ingresador por: 
                            @if ($item->pawnRegisterDetail_id)
                                <small style="color: #198754">Registro de Empeño</small> <br>
                                Codigo Empeño: {{$item->pawnRegisterDetail->pawn_register->code}}
                            @else
                                <small style="color: #0d6efd">Registro Manual</small>
                            @endif
                        </td>
                        <td style="text-align: center">{{$item->register->name}}</td>

                        <td style="text-align: right">
                            {{ floatval($item->quantity) == intval($item->quantity) ? intval($item->quantity) : $item->quantity }}
                            {{ $item->item->unit }}
                        </td>
                        <td style="text-align: right">{{ number_format($item->price,2, ',', '.') }}</td>
                        <td style="text-align: right">{{ number_format($item->amountTotal,2, ',', '.') }}</td>                                                                                  
                    </tr>
                    @php
                        $count++;
                        $total = $total + $item->amountTotal;                            
                    @endphp
                    
                @empty
                    <tr style="text-align: center">
                        <td colspan="8">No se encontraron registros.</td>
                    </tr>
                @endforelse

                <tr>
                    <td colspan="7" style="text-align: right"><b>TOTAL</b></td>
                    <td style="text-align: right"><b><small>Bs. </small>{{ number_format($total, 2, ',', '.') }}</b></td>
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
            <td style="text-align: center">
                {{-- ______________________
                <br>
                <b>Firma Responsable</b> --}}
            </td>
            <td style="text-align: center">
                ______________________
                <br>
                <b>Recibido Por</b><br>
                <b>................................................</b><br>
                <b>CI: ........................</b>
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
