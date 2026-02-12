<div class="col-md-12 text-right">
    @if ($movements->count())
        {{-- <button type="button" onclick="report_excel()" class="btn btn-success"><i class="fa-solid fa-file-excel"></i> Excel</button> --}}
        <button type="button" onclick="report_print()" class="btn btn-dark"><i class="glyphicon glyphicon-print"></i> Imprimir</button>
    @endif
    @php
        $meses = ['', 'ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
    @endphp
</div>
<div class="col-md-12">
<div class="panel panel-bordered">
    <div class="panel-body">
        <div class="table-responsive">
            @if ($show_details==1)
                <table id="dataStyle" style="width:100%"  class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th style="width:5px">N&deg;</th>
                            <th style="text-align: center">FECHA</th>
                            <th style="text-align: center">CATEGORIA</th>
                            <th style="text-align: center">DESCRIPCIÓN</th>
                            <th style="text-align: right">MONTO</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $count = 1;
                            $total = 0;
                        @endphp
                        @forelse ($movements as $item)
                            <tr>
                                <td>{{ $count }}</td>
                                <td>{{ date('d/m/Y', strtotime($item->created_at)) }}</td>
                                <td>{{ $item->cashierMovementCategory?$item->cashierMovementCategory->name :'' }}</td>
                                <td>{{ $item->description }}</td>
                                <td style="text-align: right">{{ number_format($item->amount, 2, ',', '.') }}</td>
                            </tr>
                            @php
                                $count++;
                                $total += $item->amount;
                            @endphp
                        @empty
                            <tr style="text-align: center">
                                <td colspan="4">No se encontraron registros.</td>
                            </tr>
                        @endforelse
                        <tr>
                            <td colspan="4" style="text-align: right"><b style="font-weight: bold">TOTAL</b></td>
                            <td style="text-align: right"><b style="font-weight: bold">Bs. {{ number_format($total, 2, ',', '.') }}</b></td>
                        </tr>
                    </tbody>
                </table>
            @else
                <table id="dataStyle" style="width:100%"  class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th style="width:5px">N&deg;</th>
                            <th style="text-align: center">CATEGORIA</th>
                            <th style="text-align: right">MONTO</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $count = 1;
                            $total = 0;
                        @endphp
                        @forelse ($movements as $item)
                            <tr>
                                <td>{{ $count }}</td>
                                <td>{{ $item->cashierMovementCategory?$item->cashierMovementCategory->name :'' }}</td>
                                <td style="text-align: right">{{ number_format($item->amount, 2, ',', '.') }}</td>
                            </tr>
                            @php
                                $count++;
                                $total += $item->amount;
                            @endphp
                        @empty
                            <tr style="text-align: center">
                                <td colspan="3">No se encontraron registros.</td>
                            </tr>
                        @endforelse
                        <tr>
                            <td colspan="2" style="text-align: right"><b style="font-weight: bold">TOTAL</b></td>
                            <td style="text-align: right"><b style="font-weight: bold">Bs. {{ number_format($total, 2, ',', '.') }}</b></td>
                        </tr>
                    </tbody>
                </table>
            @endif

            
        </div>
    </div>
</div>
</div>

<script>
$(document).ready(function(){

})
</script>