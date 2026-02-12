@extends('layouts.template-print-legal')

@section('page_title', 'Contrato privado')

@php
    $numeros_a_letras = new NumeroALetras();
    // $code = str_pad($pawn->id, 5, "0", STR_PAD_LEFT);
    $code = $pawn->code;
    $months = array('', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');    
    $inicio = new DateTime($pawn->date);
	$fin = new DateTime($pawn->date_limit);
	$intervalo = $inicio->diff($fin);
@endphp

@section('qr_code')
    <div id="qr_code" style="text-align: right">
        {!! QrCode::size(80)->generate('CONTRATO PRIVADO Nro. '.$code.' SUSCRITO CON '.$pawn->person->first_name.' '.$pawn->person->last_name1.' '.$pawn->person->last_name2.', EN FECHA '.date('d/m/Y', strtotime($pawn->date))); !!} <br>
        <strong>{{ $code }}</strong>
        {{-- <strong>N&deg; {{ $code }}</strong> --}}
    </div>
@endsection

@section('content')
    <div class="content" style="text-align: justify">
        <h2 class="text-center" style="font-size: 18px; margin-top: 0px">CONTRATO PRIVADO</h2>
        <p>
            <em>Conste por el presente documento privado del préstamo que a solo reconocimiento de firmas tendrá calidad de instrumento público, que el señor
            <strong>CHRISTIAN MERCADO PERICON</strong></em><em>, con</em><em><strong> C.I.1919784 BE, </strong>, que para fines del contrato en adelante se denominará como el
            <strong>ACREEDOR</strong>, por una parte, y el (la) señor (a) 
            <strong>{{ $pawn->person->first_name}} {{$pawn->person->last_name1}} {{$pawn->person->last_name2}}</strong> mayor de edad, hábil por derecho con 
            <strong>CI. {{ $pawn->person->ci }}</strong> con domicilio
            <b>Calle {{ $pawn->person->street }} Nro {{ $pawn->person->home }}, {{ $pawn->person->zone }}</b>
            que para fines del presente contrato en adelante se denominara como <strong>EL DEUDOR</strong>, por la otra parte, suscriben el presente contrato al tenor de las siguiente cláusula: </em>
        </p>

        <p>
            <em><span style="text-decoration: underline;"><strong>PRIMERA.- EL DEUDOR</strong></span></em><em> declara ser legítimo propietario de: 
            @php
                $subtotal = 0;
            @endphp
            <table>
                @foreach ($pawn->details as $detail)
                    @php
                        $features_list = '';
                        foreach ($detail->features_list as $feature) {
                            // if ($feature->value) {
                                $features_list .= '<span><b>'.$feature->title.'</b>: '.$feature->value.'</span>&nbsp;&nbsp;&nbsp;';
                            // }
                        }
                        $image = asset('images/default.jpg');
                        if($detail->image){
                            $image = asset('storage/'.str_replace('.', '-cropped.', $detail->image));
                        }
                    @endphp
                    <tr>
                        <td width="100px"><img src="{{ $image }}" width="60px" alt="Imagen"></td>
                        <td>
                            {{ floatval($detail->quantity) ? $detail->quantity : $detail->quantity }} {{ $detail->type->unit }} {{ $detail->type->name }} con un precio de {{ $detail->price }} Bs. <br>
                            {!! $features_list !!}
                        </td>
                    </tr>
                    @php
                        // $subtotal += $detail->quantity * $detail->price;
                        $subtotal += $detail->amountTotal;
                    @endphp
                @endforeach
            </table>
        </p>

        <p><em><span style="text-decoration: underline;"><strong>SEGUNDA.- EL DEUDOR</strong></span></em><em> en la presente fecha, de su libre y espontanea libertad, por así convenir a sus interés, sin que medie presión violencia, dolor o vicio en el consentimiento, entrega todo lo anteriormente mensionado en la primer cláusula, en calidad de garantía prendaria, con opción a transferencia o venta definitiva, en favor de <strong>EL ACREEDOR</strong>, por la suma libremente convenida de <strong>{{ $numeros_a_letras->toInvoice($pawn->dollarTotal, 2, 'DOLARES AMERICANOS, ($us '.number_format($pawn->dollarTotal, 2, ',', '.').')') }}</strong> cantidad de dinero que <strong>EL DEUDOR</strong> declara haber recibido a su entera y absoluta conformidad, sin lugar a reclamos posterior alguno de su parte. Así mismo garantiza la evicción y saneamiento de ley de lo otorgado en garantía.</em></p>
        <p><em><span style="text-decoration: underline;"><strong>TERCERA.- EL DEUDOR</strong></span></em><em> se compromete a devolver la suma de la que ha sido objeto el préstamo en la segunda cláusula, mas lo correspondientes interés, a la tasa libremente acordada entre las partes, que se hubieran generado, en un plazo no mayor <b>{{ $intervalo->y ? $intervalo->y.' Año(s)' : '' }} {{ $intervalo->m ? $intervalo->m.' mes(es)' : '' }} {{ $intervalo->m && $intervalo->d ? ' y ' : '' }} {{ $intervalo->d ? $intervalo->d.' día(s)' : '' }}</b>, y a su vez recoger su garantía prendaria. Caso contrario pasada las 48 horas de cumplido el plazo acordado, no existiendo rescisión ambas partes <strong>EL DEUDOR Y EL ACREEDOR</strong> declaran perfeccionada la venta adquiriendo <strong>EL ACREEDOR</strong> el derecho propietario definitivo frente a terceras personas, pudiendo éste vender lo trasferido, y vender, sin responsabilidad civil ni penal, por lo que <strong>EL DEUDOR</strong> desiste de cualquier acción judicial o policial dando el presente carácter de transacción con el sello de cosa juzgada.</em></p>
        <p><em><span style="text-decoration: underline;"><strong>CUARTA .- </strong></span></em><em>Ambas partes expresan conformidad con cada una de las cláusulas del presente documento y para tal efecto firman el mismo.
            {{-- El vendedor, Sr. <strong>{{$pawn->person->first_name}} {{$pawn->person->last_name1}} {{$pawn->person->last_name2}}</strong> sin embargo, queda obligado, en término de ley, a la evicción y saneamiento de este contrato, en concepto de hallarse el Vehículo vendio libre de toda carga, o gravámenes.</em> --}}
        </p>
        <p style="text-align: right;"> 
            <span>Santísima Trinidad</span>, {{ date('d', strtotime($pawn->dateDelivered)) }} de {{ $months[intval(date('m', strtotime($pawn->dateDelivered)))] }} de {{ date('Y', strtotime($pawn->dateDelivered)) }}
        </p>
     
        <br>
        <br>
        <br>
        <br>
        <table class="table-signature">
            <tr>
                <td style="width: 50%">
                    ....................................................... <br>
                    <em>{{  strtoupper($pawn->person->first_name)}} {{strtoupper($pawn->person->last_name1)}} {{strtoupper($pawn->person->last_name2)}}</em><br>
                    <b>DEUDOR</b> <br>
                    <b>C.I. {{$pawn->person->ci}}</b><br>
                </td>
                <td style="width: 50%">
                    ....................................................... <br>
                    <em>CHRISTIAN MERCADO PERICÓN</em> <br>
                    <b>ACREEDOR</b><br>
                    <b>C.I. 1919784 Beni</b><br>
                </td>           
            </tr>
        </table>
    </div>
@endsection

@section('css')
    <style>

    </style>
@endsection

@section('script')
    <script>

    </script>
@endsection