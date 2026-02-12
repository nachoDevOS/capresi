
<div class="col-md-12 text-right">

    {{-- <button type="button" onclick="report_excel()" class="btn btn-success"><i class="fa-solid fa-file-excel"></i> Excel</button> --}}
    <button type="button" onclick="report_print()" class="btn btn-dark"><i class="glyphicon glyphicon-print"></i> Imprimir</button>

</div>
<div class="col-md-12">
<div class="panel panel-bordered">
    <div class="panel-body">
        <div class="table-responsive">
            <table id="dataStyle" style="width:100%"  class="table table-bordered table-striped table-sm">
                <thead>
                    <tr>
                        <th style="width:5px">N&deg;</th>
                        <th style="text-align: center">FECHA DE ENTREGA</th>
                        <th style="text-align: center">CODIGO</th>                        
                        <th style="text-align: center">CI</th>
                        <th style="text-align: center">CLIENTE</th>
                        <th style="text-align: center">ENTREGADO POR</th>
                        <th style="text-align: center">DIAS A PAGAR</th>
                        <th style="text-align: center">INTERES %</th>
                        <th style="text-align: center">MONTO PRESTADO</th>
                        <th style="text-align: center">INTERES Bs.</th>
                        <th style="text-align: center">TOTAL A PAGAR</th>
                        <th style="text-align: center">DEUDA</th>
                        <th style="text-align: center">MORA</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $count = 1;
                        $amountLoan = 0;
                        $amountTotal = 0;
                        $amountPorcentage = 0;
                        $debt = 0;
                        $total_monto_mora = 0;
                    @endphp
                    @forelse ($data as $item)
                        @php
                            $monto_mora =0;
                            if($item->mora)
                            {
                                $monto_mora = $item->loanDay->where('status', 1)->where('deleted_at', null)->where('debt', '>', 0)->sum('debt');
                                $total_monto_mora += $monto_mora;
                            }                            
                            $ultima_cuota = $item->loanDay->sortByDesc('date')->first();
                        @endphp
                        <tr @if($ultima_cuota->date < date('Y-m-d') && $ultima_cuota->debt > 0) style="background-color: rgba(231, 76, 60, 0.5)" @elseif($item->porcentage == 0) style="background-color: rgba(36, 113, 163, 0.5)" @endif>
                            <td>{{ $count }}</td>
                            <td style="text-align: center">{{date('d/m/Y', strtotime($item->dateDelivered))}}</td>
                            <td style="text-align: center"><small>{{ $item->code}}</small></td>
                            <td>{{ $item->ci }}</td>
                            <td>{{ strtoupper($item->people->first_name)}} {{ strtoupper($item->people->last_name1)}} {{ strtoupper($item->people->last_name2)}}</td>
                            <td>{{ strtoupper($item->agentDelivered->name)}}</td>
                            <td style="text-align: right">{{ $item->day }} Días</td>
                            <td style="text-align: right">{{ number_format($item->porcentage, 2, ',','.') }} %</td>
                            <td style="text-align: right">{{ number_format($item->amountLoan, 2, ',','.') }}</td>
                            <td style="text-align: right">{{ number_format($item->amountPorcentage, 2, ',','.') }}</td>
                            <td style="text-align: right">{{ number_format($item->amountTotal, 2, ',','.') }}</td>
                            <td style="text-align: right">{{ number_format($item->debt, 2, ',','.') }}</td>
                            <td style="text-align: right">{{ number_format($monto_mora, 2, ',','.') }}</td>
                        </tr>
                        @php
                            $count++;
                            $amountTotal+= $item->amountTotal;          
                            $amountLoan+= $item->amountLoan;          
                            $amountPorcentage+= $item->amountPorcentage;          
                            $debt+= $item->debt;
                        @endphp
                    @empty
                        <tr style="text-align: center">
                            <td colspan="12">No se encontraron registros.</td>
                        </tr>
                    @endforelse
                    <tr>
                        <td colspan="6" style="text-align: right">Total</td>
                        <td style="text-align: right"></td>
                        <td style="text-align: right"></td>
                        <td style="text-align: right"><b>{{ number_format($amountLoan,2, ',', '.') }}</b></td>
                        <td style="text-align: right"><b>{{ number_format($amountPorcentage,2, ',', '.') }}</b></td>
                        <td style="text-align: right"><b>{{ number_format($amountTotal,2, ',', '.') }}</b></td>
                        <td style="text-align: right"><b>{{ number_format($debt,2, ',', '.') }}</b></td>
                        <td style="text-align: right"><b>{{ number_format($total_monto_mora, 2, ',', '.') }}</b></td>
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