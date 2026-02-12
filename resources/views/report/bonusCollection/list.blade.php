
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
                            <th style="text-align: center">RECAUDACION</th>                        
                            <th style="text-align: center">RUTA X C</th>
                            <th style="text-align: center">COBRADO</th>
                            <th style="text-align: center">% COBRANZA</th>
                            <th style="text-align: center">GASTOS, QR Y REPROG.</th>
                            <th style="text-align: center">EFECTIVO Bs.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $count = 1;
                            $route=0;
                            $payment = 0;
                            $porcentage =0;
                            $totalQr=0;
                            $totalEfectivo=0;
                            // $totalCashier=0;
                        @endphp
                        @forelse ($datas as $item)
                            @php
                                $agent_id = $item->agent_id;
                                $loanDay = \App\Models\LoanDayAgent::where('deleted_at', null)
                                    ->where('agent_id', $agent_id)
                                    ->whereDate('created_at', '>=', $start)
                                    ->whereDate('created_at', '<=', $finish)
                                    ->get()->SUM('amount');

                                $qr = \App\Models\LoanDayAgent::with(['transaction'])
                                    ->where('deleted_at', null)
                                    ->where('agent_id', $agent_id)
                                    ->whereDate('created_at', '>=', $start)
                                    ->whereDate('created_at', '<=', $finish)
                                    ->whereHas('transaction', function($query) {
                                        $query->where('type', 'Qr'); 
                                    })
                                    ->get()->SUM('amount');


                                $cashier_cash_out = \App\Models\CashierMovement::with(['cashier'])
                                    ->where('type', 'egreso')
                                    ->whereDate('created_at', '>=', $start)
                                    ->whereDate('created_at', '<=', $finish)
                                    ->whereHas('cashier', function($query) use($agent_id){
                                        $query->where('user_id', $agent_id); 
                                    })                                    
                                    ->get()->sum('amount');
                            @endphp
                            <tr>
                                {{-- <td>{{ $count }}</td> --}}
                                <td style="text-align: left">{{ $item->route->name }} - {{ $item->agent->name }}</td>
                                <td style="text-align: right">{{ number_format($item->totalDailyPayment, 2, '.','') }}</td>
                                <td style="text-align: right">{{ number_format($loanDay, 2, '.','') }}</td>
                                <td style="text-align: right">{{ number_format(($loanDay / $item->totalDailyPayment)*100, 2, '.','') }}</td>
                                <td style="text-align: right">{{ number_format($qr+$cashier_cash_out, 2, '.','') }}</td>
                                <td style="text-align: right">{{ number_format(($loanDay-$qr-$cashier_cash_out), 2, '.','') }}</td>
                            </tr>
                            @php
                                $count++;
                                $route+=$item->totalDailyPayment;
                                $payment+=$loanDay;
                                $totalQr+=$qr+$cashier_cash_out;
                                $totalEfectivo+=$loanDay-$qr-$cashier_cash_out;
                            @endphp
                        @empty
                            <tr style="text-align: center">
                                <td colspan="5">No se encontraron registros.</td>
                            </tr>
                        @endforelse
                        <tr>
                            <td style="text-align: left">Total</td>
                            <td style="text-align: right">{{ number_format($route, 2, '.','') }}</td>
                            <td style="text-align: right">{{ number_format($payment, 2, '.','') }}</td>
                            <td style="text-align: right">{{ number_format(($payment / $route)*100, 2, '.','') }} %</td>
                            <td style="text-align: right">{{ number_format($totalQr, 2, '.','') }}</td>
                            <td style="text-align: right">{{ number_format($totalEfectivo, 2, '.','') }}</td>

                        </tr>

                        @foreach ($cashiers as $item)
                            @if ($item->amount>0)
                                <tr>
                                    <td colspan="5" style="text-align: center" >{{ $item->name }}</td>
                                    <td style="text-align: right">{{ number_format($item->amount, 2, '.','') }}</td>

                                </tr>
                                @php
                                    $totalEfectivo-=$item->amount;
                                @endphp
                            @endif
                        @endforeach
                        <tr>
                            <td colspan="5" style="text-align: center">SALDO EFECTIVO Bs.-</td>
                            <td style="text-align: right">{{ number_format($totalEfectivo, 2, '.','') }}</td>
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