
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
        @if ($prestamos == 'diario' || $prestamos == 'todo')
            <div class="table-responsive">
                <table id="dataStyle" style="width:100%"  class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th colspan="9" style="text-align: center">DIARIO</th>
                        </tr>
                        <tr>
                            <th>N&deg;</th>
                            <th>Codigo</th>
                            <th>Fecha Solicitud</th>
                            <th>Fecha Entrega</th>
                            <th>Nombre Completo</th>
                            <th>Entregado Por</th>
                            <th style="text-align: right">Monto Prestado</th>
                            <th style="text-align: right">Interes a Cobrar</th>
                            <th style="text-align: right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $cont = 1;

                            $loans=0;
                            $interes =0;
                            $total = 0;
                        @endphp
                        @forelse ($dataDiario as $item)

                            <tr>
                                <td>{{ $cont }}</td>
                                <td>
                                    {{ $item->code }}
                                </td>
                                <td>{{ date("d-m-Y", strtotime($item->date))}}</td>
                                <td>{{ date("d-m-Y", strtotime($item->dateDelivered))}}</td>
                                <td>
                                    <small>CI:</small> {{ $item->people->ci}} <br>
                                    <p>{{ $item->people->first_name}} {{ $item->people->last_name1}} {{ $item->people->last_name2}}</p>
                                </td>
                                <td>
                                    <small>{{ $item->agentDelivered->role->name=='cajeros'?'Cajero':''}}</small> <br>
                                    <p>{{ $item->agentDelivered->name}}</p>
                                </td>
                                <td style="text-align: right">
                                    <small>Bs.</small> {{ number_format($item->amountLoan, 2, ',', '.') }}
                                </td>
                                <td style="text-align: right">
                                    <small>Bs.</small>  {{ number_format($item->amountPorcentage, 2, ',', '.') }}
                                </td>
                                <td style="text-align: right">
                                    <small>Bs.</small> {{ number_format($item->amountTotal, 2, ',', '.') }}
                                </td>
                                
                            </tr>
                            @php
                                $cont++;
                                    
                                $interes = $interes + $item->amountPorcentage;
                                $loans = $loans + $item->amountLoan;
                                $total = $total + $item->amountTotal;
                            @endphp
                            
                        @empty
                            <tr style="text-align: center">
                                <td colspan="19">No se encontraron registros.</td>
                            </tr>
                        @endforelse
                        <tr>
                            <td colspan="6" style="text-align: right"><b>TOTAL</b></td>
                            <td style="text-align: right"><b><small>Bs. </small>{{ number_format($loans, 2, ',', '.') }}</b></td>
                            <td style="text-align: right"><b><small>Bs. </small>{{ number_format($interes, 2, ',', '.') }}</b></td>
                            <td style="text-align: right"><b><small>Bs. </small>{{ number_format($total, 2, ',', '.') }}</b></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
        @if ($prestamos == 'prenda' || $prestamos == 'todo')
            <div class="table-responsive">
                <table id="dataStyle" style="width:100%"  class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th colspan="8" style="text-align: center">PRENDARIO</th>
                        </tr>
                        <tr>
                            <th>N&deg;</th>
                            <th>Codigo</th>
                            <th>Fecha Solicitud</th>
                            <th>Fecha Entrega</th>
                            <th>Nombre Completo</th>
                            <th>Entregado Por</th>
                            <th style="text-align: right">% Interes</th>
                            <th style="text-align: right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $cont = 1;
                            $total = 0;
                            $subtotal = 0;
                        @endphp
                        @forelse ($dataPrendario as $item)

                            <tr>
                                <td>{{ $cont }}</td>
                                <td>
                                    {{ $item->code }}
                                </td>
                                <td>{{ date("d-m-Y", strtotime($item->date))}}</td>
                                <td>{{ date("d-m-Y", strtotime($item->dateDelivered))}}</td>
                                <td>
                                    <small>CI:</small> {{ $item->person->ci}} <br>
                                    <p>{{ $item->person->first_name}} {{ $item->person->last_name1}} {{ $item->person->last_name2}}</p>
                                </td>
                                <td>
                                    <p>{{ $item->agentDelivered->name}}</p>
                                </td>
                                @foreach ($item->details as $detail)
                                    @php
                                        $subtotal += $detail->quantity * $detail->price;
                                    @endphp
                                @endforeach
                                <td style="text-align: right">
                                    {{ number_format($item->interest_rate, 2, '.', '') }}<small> %</small>
                                </td>
                                <td style="text-align: right">
                                    <small>Bs.</small> {{ number_format($subtotal, 2, '.', '') }}
                                
                            </tr>
                            @php
                                $cont++;
                                $total = $total + $subtotal;
                            @endphp
                            
                        @empty
                            <tr style="text-align: center">
                                <td colspan="19">No se encontraron registros.</td>
                            </tr>
                        @endforelse
                        <tr>
                            <td colspan="7" style="text-align: right"><b>TOTAL</b></td>
                            <td style="text-align: right"><b><small>Bs. </small>{{ number_format($total, 2, '.', '') }}</b></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
</div>

<script>
$(document).ready(function(){

})
</script>