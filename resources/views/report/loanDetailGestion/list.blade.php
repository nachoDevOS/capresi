
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
                        <th rowspan="2" style="width:5px">N&deg;</th>
                        <th rowspan="2" style="text-align: center">GESTION</th>
                        <th rowspan="2" style="text-align: center">CAPITAL</th>                        
                        <th rowspan="2" style="text-align: center">INTERES</th>                        
                        <th rowspan="2" style="text-align: center">MONTO PRESTADO + INTERES Bs</th>     

                        <th colspan="3" style="text-align: center">Bs.</th>
                        <th colspan="3" style="text-align: center">%</th>
                    </tr>

                    <tr>
                        <th style="text-align: center">PAGADO</th>
                        <th style="text-align: center">DEUDA</th>
                        <th style="text-align: center">MORA</th>
                        <th style="text-align: center">PAGADO</th>
                        <th style="text-align: center">DEUDA</th>
                        <th style="text-align: center">MORA</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $count = 1;
                        $amountLoan =0; 
                        $pagado = 0;
                        $deuda = 0;
                        $mora = 0;
                        $capital = 0;
                        $interes=0;
                    @endphp
                    @forelse ($datas as $item)
                        <tr>
                            <td>{{ $count }}</td>
                            <td style="text-align: left">{{ $item->yearDate }}</td>
                            <td style="text-align: right">{{ number_format($item->capital, 2, ',','.') }}</td>
                            <td style="text-align: right">{{ number_format($item->interes, 2, ',','.') }}</td>
                            <td style="text-align: right">{{ number_format($item->amountLoan, 2, ',','.') }}</td>
                            <td style="text-align: right">{{ number_format($item->pagado, 2, ',','.') }}</td>
                            <td style="text-align: right">{{ number_format($item->deuda, 2, ',','.') }}</td>
                            <td style="text-align: right">{{ number_format($item->mora, 2, ',','.') }}</td>
                            <td style="text-align: right">{{ number_format(($item->pagado/$item->amountLoan)*100, 2, ',','.') }} %</td>
                            <td style="text-align: right">{{ number_format(($item->deuda/$item->amountLoan)*100, 2, ',','.') }} %</td>
                            <td style="text-align: right">{{ number_format(($item->mora/$item->amountLoan)*100, 2, ',','.') }} %</td>
                        </tr>
                        @php
                            $count++;
                            $amountLoan+=$item->amountLoan;
                            $pagado+=$item->pagado;
                            $deuda+=$item->deuda;
                            $mora+=$item->mora;
                            $capital += $item->capital;
                            $interes += $item->interes;
                        @endphp
                    @empty
                        <tr style="text-align: center">
                            <td colspan="11">No se encontraron registros.</td>
                        </tr>
                    @endforelse
                    <tr>
                        <td colspan="2" style="text-align: left">Total</td>
                        <td style="text-align: right">{{ number_format($capital, 2, ',','.') }}</td>
                        <td style="text-align: right">{{ number_format($interes, 2, ',','.') }}</td>
                        <td style="text-align: right">{{ number_format($amountLoan, 2, ',','.') }}</td>
                        <td style="text-align: right">{{ number_format($pagado, 2, ',','.') }}</td>
                        <td style="text-align: right">{{ number_format($deuda, 2, ',','.') }}</td>
                        <td style="text-align: right">{{ number_format($mora, 2, ',','.') }}</td>
                        <td style="text-align: right"></td>
                        <td style="text-align: right"></td>
                        <td style="text-align: right"></td>
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