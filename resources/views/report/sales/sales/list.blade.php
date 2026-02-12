
<div class="col-md-12 text-right">
    @if ($cashier==0 || auth()->user()->hasRole('admin') || auth()->user()->hasRole('gerente') || auth()->user()->hasRole('administrador'))
        <button type="button" onclick="report_print()" class="btn btn-dark"><i class="glyphicon glyphicon-print"></i> Imprimir</button>
    @else
        <div class="alert alert-warning">
            <strong>Advertencia:</strong>
            <p>Para poder imprimir su reporte no debe tener caja abierta o pendiente.</p>
        </div>
    @endif

</div>
<div class="col-md-12">
<div class="panel panel-bordered">
    <div class="panel-body">
        <div class="table-responsive">
            <table id="dataStyle" style="width:100%"  class="table table-bordered table-striped table-sm">
                <thead>
                    <tr>
                        <th style="width:5px">N&deg;</th>
                        <th style="text-align: center">DETALLE DE VENTA</th>
                        <th style="text-align: center">DETALLE DEL ARTICULO</th>
                        <th style="text-align: center">CLIENTE</th>
                        <th style="text-align: center">VENDIDO POR</th>
                        <th style="text-align: center">SUBTOTAL</th>
                        <th style="text-align: center">DESCUENTO</th>
                        <th style="text-align: center">TOTAL A PAGAR</th>
                        <th style="text-align: center">CUOTA INICIAL</th>
                    </tr>
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
                                    {{date('d/m/Y h:i:s a', strtotime($item->created_at))}} <br>
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
        </div>
    </div>
</div>
</div>

<script>
$(document).ready(function(){

})
</script>