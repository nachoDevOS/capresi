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
                    REPORTE DE VENTAS  <br>
                </h4>
                <h5 style="margin-bottom: 0px; margin-top: 5px">
                    VENTA AL
                    @if ($typePrint == 'Credito' || $typePrint == '')
                        CREDITO
                    @endif
                    @if ($typePrint == '')
                        Y
                    @endif
                    @if ($typePrint == 'Contado' || $typePrint == '')
                        CONTADO
                    @endif
                </h5>
            
                <small style="margin-bottom: 0px; margin-top: 5px">
                    @if ($start == $finish)
                        {{ date('d', strtotime($start)) }} de {{ $months[intval(date('m', strtotime($start)))] }} de {{ date('Y', strtotime($start)) }}
                    @else
                        {{ date('d', strtotime($start)) }} de {{ $months[intval(date('m', strtotime($start)))] }} de {{ date('Y', strtotime($start)) }} Al {{ date('d', strtotime($finish)) }} de {{ $months[intval(date('m', strtotime($finish)))] }} de {{ date('Y', strtotime($finish)) }}
                    @endif
                </small>
            </td>
            <td style="text-align: right; width:20%">
                <h3 style="margin-bottom: 0px; margin-top: 5px">
                    <small style="font-size: 8px; font-weight: 100">Impreso por: {{ Auth::user()->name }} <br> {{ date('d/m/Y H:i:s') }}</small>
                </h3>
            </td>
        </tr>
    </table>
        <table style="width: 100%; font-size: 10px" border="1" class="print-friendly" cellspacing="0" cellpadding="2">
            <thead>
                <th style="width:5px">N&deg;</th>
                <th style="text-align: center">DETALLE DE VENTA</th>
                <th style="text-align: center">DETALLE DEL ARTICULO</th>
                <th style="text-align: center">CLIENTE</th>
                <th style="text-align: center">VENDIDO POR</th>
                <th style="text-align: center">SUBTOTAL</th>
                <th style="text-align: center">DESCUENTO</th>
                <th style="text-align: center">TOTAL</th>
                <th style="text-align: center">CUOTA INICIAL</th>
            </thead>
            <tbody>
                @php
                    $count = 1;
                    $total = 0;
                    $inicial =0;
                    $discount = 0;
                    $amount = 0;
                @endphp
                @forelse ($sales as $item)
                    <tr>
                        <td>{{ $count }}</td>
                        <td style="text-align: center">
                            {{ $item->code }} <br>
                            {{date('d/m/Y', strtotime($item->created_at))}} <br>
                            {{date('h:i:s a', strtotime($item->created_at))}} <br>
                            Venta al {{$item->typeSale == 'Credito'? 'Credito':'Contado'}}

                        </td>
                        <td>
                            @foreach ($item->saleDetails as $detail)
                                <table style="width: 100%">
                                    <tr>
                                        <td>
                                            <ul>
                                                <li style="font-size: 15px">
                                                    <small>
                                                        {{ floatval($detail->inventory->quantity) == intval($detail->inventory->quantity) ? intval($detail->inventory->quantity) : $detail->inventory->quantity }}
                                                    {{ $detail->inventory->item->unit }} {{ $detail->inventory->item->name }} a {{ floatval($detail->price) == intval($detail->price) ? intval($detail->price) : $detail->price }}
                                                    Bs.
                                                    </small>
                                                </li>
                                            </ul>
                                        
                                            @php
                                                $features_list = '';
                                                foreach ($detail->inventory->features as $feature) {
                                                    if ($feature->value) {
                                                        $features_list .= '<span><b>'.$feature->title.'</b>: '.$feature->value.'</span><br>';
                                                    }
                                                }
                                            @endphp
                                            {!! $features_list !!}
                                        </td>
                                    </tr>
                                </table>
                            @endforeach
                        </td>
                        

                        <td style="text-align: left">
                            @if ($item->person_id)
                                <small>CI: {{$item->person->ci}}</small> <br>
                                <small>{{$item->person->first_name}} {{$item->person->last_name1}} {{$item->person->last_name2}}</small> 
                            @else
                                S/N
                            @endif
                        </td>
                        <td style="text-align: center">{{$item->register->name}}</td>
                        <td style="text-align: right">{{ number_format($item->amount,2, ',', '.') }}</td>                                                                                  
                        <td style="text-align: right">{{ number_format($item->discount,2, ',', '.') }}</td>                                                                                  
                        <td style="text-align: right">{{ number_format($item->amountTotal,2, ',', '.') }}</td> 
                        <td style="text-align: right">{{ number_format($item->saleAgents->first()->amount,2, ',', '.') }}</td> 
                    </tr>
                    @php
                        $count++;
                        $total +=$item->amountTotal;     
                        $inicial+=$item->saleAgents->first()->amount;                       
                        $discount+=$item->discount;                       
                        $amount+=$item->amount;                         
                    @endphp
                    
                @empty
                    <tr style="text-align: center">
                        <td colspan="9">No se encontraron registros.</td>
                    </tr>
                @endforelse

                <tr>
                    <td colspan="5" style="text-align: right"><b>TOTAL</b></td>
                    <td style="text-align: right"><b><small>Bs. </small>{{ number_format($amount, 2, ',', '.') }}</b></td>
                    <td style="text-align: right"><b><small>Bs. </small>{{ number_format($discount, 2, ',', '.') }}</b></td>
                    <td style="text-align: right"><b><small>Bs. </small>{{ number_format($total, 2, ',', '.') }}</b></td>
                    <td style="text-align: right"><b><small>Bs. </small>{{ number_format($inicial, 2, ',', '.') }}</b></td>
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
