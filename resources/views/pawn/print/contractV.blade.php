<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contratación</title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            line-height: 1.8;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            font-weight: bold;
        }

        .content {
            text-align: justify;
            margin: 1cm 1.5cm;
            page-break-inside: avoid;
        }

        .signature {
            margin-top: 4cm;
            text-align: center;
            page-break-inside: avoid;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1cm;
            text-align: left;
            font-size: 10px;
            /* border-top: 1px solid #000; */
            padding-top: 0cm;
        }

        @media print {
            .no-print {
                display: none;
            }

            .content {
                margin-top: 1cm;
                /* margin-right: 2cm; */
                /* margin-bottom: 10cm; */
                /* margin-left: 2cm; */
            }
         

            .footer {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    {{-- <div class="no-print" style="text-align: right; padding: 10px;">
        <button onclick="window.close()">Cancelar</button>
        <button onclick="window.print()">Imprimir</button>
    </div> --}}
    
    @php
        $numeros_a_letras = new NumeroALetras();
        // $code = str_pad($pawn->id, 5, "0", STR_PAD_LEFT);
        $code = $pawn->code;
        $months = array('', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');    

    @endphp

    <div class="content">
        <em style="text-align: right">
            <strong>{{$pawn->code}}</strong>
        </em>

        <p style="text-align: center"><strong>SEÑOR NOTARIO DE FE PÚBLICA</strong></p>
        @php
            $features_list = '';
            $article = $pawn->details->first()->type;
            // dump($article);
        @endphp
        @foreach ($pawn->details as $detail)
            @php
                foreach ($detail->features_list as $feature) {
                    if ($feature->value) {
                        $features_list .= '<span><strong>'.$feature->title.'</strong>: '.$feature->value.'</span>&nbsp;&nbsp;&nbsp;';
                    }
                }
            @endphp
        @endforeach

        <p>
            <em>En los registros de escrituras públicas que corren a su cargo, sírvase insertar una de transferencia con Pacto de Rescate de un(a) 
                <strong>{{$article->name}}</strong>, de acuerdo a las siguientes cláusulas:
            </em>
        </p>
        

        <p>
            <em>
                <strong>PRIMERA:</strong> Dirá Ud. Que yo, 
                <strong>{{ $pawn->person->first_name}} {{$pawn->person->last_name1}} {{$pawn->person->last_name2}}</strong> No. {{ $pawn->person->ci }}, Domicilio: CALLE {{ $pawn->person->street }} Nro {{ $pawn->person->home }}, {{ $pawn->person->zone }} Cél: {{ $pawn->person->cell_phone}} declaro ser legítimo propietario de un (a) 
                <strong>{{$article->name}}</strong> 
                {!! $features_list !!}.
            </em>
        </p>

        <p>
            <em><strong>SEGUNDA:</strong> Al presente, en pleno uso de mis derechos y por convenir así a mis intereses, el referido inmueble, doy y transfiero en calidad de compra-venta con pacto de rescate, de acuerdo a lo prescrito en el Art. 641 del Código Civil, en favor del Sr. <strong>CHRISTIAN MERCADO PERICÓN</strong> con C.I. No. 1919784-Beni, por la suma libremente convenida de {{ $numeros_a_letras->toInvoice($pawn->dollarTotal, 2, 'DOLARES AMERICANOS, ($us '.number_format($pawn->dollarTotal, 2, ',', '.').')') }}, valor que declaro haber recibido en moneda de curso legal y corriente, a tiempo de suscribir la presente minuta, a mi plena satisfacción.</em>
        </p>
        <p>
            <em>
                <strong>TERCERA:</strong>
                Consecuentemente, en virtud de lo expuesto en la cláusula anterior, el vendedor Sr.
                <strong>{{ $pawn->person->first_name}} {{$pawn->person->last_name1}} {{$pawn->person->last_name2}}</strong> se reserva el derecho de rescate o de retirar a su dominio, la 
                <strong>{{$article->name}}</strong> vendida, previo abono de su precio legítimo, dentro del plazo de 
                <strong>{{$pawn->cantMonth}} Mes</strong> a contar de la suscripción de la escritura pública de la transferencia, aclarandose del mismo modo que, si el vendedor no comunica al comprador Sr. <strong>CHRISTIAN MERCADO PERICÓN</strong>, su declaración de rescate con la protesta de reembolsar los gastos efectuados y que deben ser objeto de comprobación y liquidación, dentro del termino fijado en la presente cláusula, caducara ese su derecho, conforme a lo prescrito por el Art. 644 del señalado Código Civil, convirtiendolo al comprador en irrevocable propietario con todos los derechos otorgado por ley.
            </em>
        </p>
        <p>
            <em><strong>CUARTA:</strong> El vendedor, Sr. <strong>{{ $pawn->person->first_name}} {{$pawn->person->last_name1}} {{$pawn->person->last_name2}}</strong>
                sin embargo, queda obligado, en término de ley, a la evicción y saneamiento de este contrato, en concepto de hallarse el (la) {{$article->name}} vendido libre de toda carga, o gravámenes.
            </em>
        </p>
        <p>
            <em>
                <strong>QUINTA:</strong> La presente minuta a solo reconocimiento de firmas tendrá valor de Documento Privado, en tanto no llegue a protocolizarse.
            </em>
        </p>

        <p>
            <em><strong>SEXTA:</strong> Yo, <strong>{{ $pawn->person->first_name}} {{$pawn->person->last_name1}} {{$pawn->person->last_name2}}</strong> como vendedor, por una parte, y <strong>CHRISTIAN MERCADO PERICÓN</strong> como comprador, en conformidad con las cláusulas suscritas en la presente minuta, firmamos el presente documento.</em>
        </p>
        <p>
            <em>. Ud. Señor Notario se servirá agregar las demás cláusulas y seguridad y estilo, para mayor validez del protocolo.</em>
        </p>

        <p style="text-align: right;">
            <em> 
                <span>Santísima Trinidad</span>, {{ date('d', strtotime($pawn->dateDelivered)) }} de {{ $months[intval(date('m', strtotime($pawn->dateDelivered)))] }} de {{ date('Y', strtotime($pawn->dateDelivered)) }}
            </em>
        </p>
        <div class="signature">
            <table class="table-signature" width="100%">
                <tr style="font-size: 12px">
                    <td >
                        ....................................................... <br>
                        <em>{{  strtoupper($pawn->person->first_name)}} {{strtoupper($pawn->person->last_name1)}} {{strtoupper($pawn->person->last_name2)}}</em><br>
                        <b>DEUDOR</b> <br>
                        <b>C.I. {{$pawn->person->ci}}</b><br>
                    </td>
                    <td >
                        ....................................................... <br>
                        <em>CHRISTIAN MERCADO PERICÓN</em> <br>
                        <b>ACREEDOR</b><br>
                        <b>C.I. 1919784 Beni</b><br>
                    </td>           
                </tr>
            </table>
        </div>
    </div>    
</body>
</html>
