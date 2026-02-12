
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
                        <th style="text-align: center">CODIGO</th>
                        <th style="text-align: center">DETALLE DEL ARTICULO</th>
                        <th style="text-align: center">FECHA DE INGRESO</th>
                        <th style="text-align: center">REGISTRADO POR</th>
                        <th style="text-align: center">CANTIDAD</th>
                        <th style="text-align: center">PRECIO</th>
                        <th style="text-align: center">TOTAL</th>
                    </tr>
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
        </div>
    </div>
</div>
</div>

<script>
$(document).ready(function(){

})
</script>