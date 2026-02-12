<div class="col-md-12 text-right">
    @if (count($loans) > 0)
        <button type="button" onclick="report_print()" class="btn btn-danger"><i class="glyphicon glyphicon-print"></i> Imprimir</button>
    @endif
</div>
<div class="col-md-12">
    <div class="table-responsive">
        <table id="dataStyle" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th rowspan="2" style="text-align: center">FECHA</th>
                    <th rowspan="2" style="text-align: center">CAPITAL</th>
                    <th rowspan="2" style="text-align: center">INTERES</th>
                    <th rowspan="2" style="text-align: center">CAPITAL + INTERES</th>
                    <th colspan="2" style="text-align: center">Bs.</th>
                    <th colspan="2" style="text-align: center">%</th>
                    
                </tr>
                <tr>
                    
                    <th style="text-align: center">CAPITAL PAGADO</th>
                    <th style="text-align: center">CAPITAL VIGENTE</th>
                    
                    <th style="text-align: center">CAPITAL PAGADO</th>
                    <th style="text-align: center">CAPITAL VIGENTE</th>
                </tr>




            </thead>
            <tbody>
                @php
                    $totalCapital = 0;
                    $totalInterest = 0;
                    $totalTotal = 0;
                    $totalPaid = 0;
                    $totalDebt = 0;
                @endphp
                @forelse ($loans as $key => $group)
                    @php
                        if($type == 'grouped' || $type == 'todo'){
                            $capital = $group->sum('amountLoan');
                            $interest = $group->sum('amountPorcentage');
                            $total = $group->sum('amountTotal');
                            $debt = $group->sum('debt');
                            $paid = $total - $debt;
                        }else{
                            $capital = $group->amountLoan;
                            $interest = $group->amountPorcentage;
                            $total = $group->amountTotal;
                            $debt = $group->debt;
                            $paid = $total - $debt;
                            $key = \Carbon\Carbon::parse($group->dateDelivered)->format('d/m/Y');
                        }

                        $totalCapital += $capital;
                        $totalInterest += $interest;
                        $totalTotal += $total;
                        $totalPaid += $paid;
                        $totalDebt += $debt;
                    @endphp
                    <tr>
                        <td>{{ $key }}</td>
                        <td class="text-right">{{ number_format($capital, 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($interest, 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($total, 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($paid, 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($debt, 2, ',', '.') }}</td>
                       
                        <td class="text-right">{{ $total > 0 ? number_format(($paid / $total) * 100, 2, ',', '.') : 0 }}%</td>
                        <td class="text-right">{{ $total > 0 ? number_format(($debt / $total) * 100, 2, ',', '.') : 0 }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No hay datos registrados</td>
                    </tr>
                @endforelse
                
                <tr>
                    <td class="text-right"><strong>TOTAL</strong></td>
                    <td class="text-right"><strong>{{ number_format($totalCapital, 2, ',', '.') }}</strong></td>
                    <td class="text-right"><strong>{{ number_format($totalInterest, 2, ',', '.') }}</strong></td>
                    <td class="text-right"><strong>{{ number_format($totalTotal, 2, ',', '.') }}</strong></td>
                    <td class="text-right"><strong>{{ number_format($totalPaid, 2, ',', '.') }}</strong></td>
                    <td class="text-right"><strong>{{ number_format($totalDebt, 2, ',', '.') }}</strong></td>
                    <td class="text-right"><strong>{{ $totalTotal > 0 ? number_format(($totalPaid / $totalTotal) * 100, 2, ',', '.') : 0 }}%</strong></td>
                    <td class="text-right"><strong>{{ $totalTotal > 0 ? number_format(($totalDebt / $totalTotal) * 100, 2, ',', '.') : 0 }}%</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    $(document).ready(function(){
        // $('#dataTable').DataTable({
        //     language: {
        //             url: '{{ asset('js/Spanish.json') }}'
        //         },
        // });
    });
</script>