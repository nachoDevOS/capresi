@extends('layouts.template-print-horizontal')

@section('page_title', 'Reporte')

@section('content')
    @php
        $months = [
            '',
            'Enero',
            'Febrero',
            'Marzo',
            'Abril',
            'Mayo',
            'Junio',
            'Julio',
            'Agosto',
            'Septiembre',
            'Octubre',
            'Noviembre',
            'Diciembre',
        ];
    @endphp

    <table width="100%">
        <tr>
            <td style="width: 20%"><img src="{{ asset('images/icon.png') }}" alt="CAPRESI" width="70px"></td>
            <td style="text-align: center;  width:50%">
                <h3 style="margin-bottom: 0px; margin-top: 5px">
                    EMPRESA "CAPRESI"<br>
                </h3>
                <h4 style="margin-bottom: 0px; margin-top: 5px">
                    REPORTE DE PRESTAMOS
                </h4>
                <small style="margin-bottom: 0px; margin-top: 5px">
                    @if ($start == $finish)
                        {{ date('d', strtotime($start)) }} de {{ $months[intval(date('m', strtotime($start)))] }} de
                        {{ date('Y', strtotime($start)) }}
                    @else
                        {{ date('d', strtotime($start)) }} de {{ $months[intval(date('m', strtotime($start)))] }} de
                        {{ date('Y', strtotime($start)) }} Al {{ date('d', strtotime($finish)) }} de
                        {{ $months[intval(date('m', strtotime($finish)))] }} de {{ date('Y', strtotime($finish)) }}
                    @endif
                </small>
            </td>
            <td style="text-align: right; width:30%">
                <h3 style="margin-bottom: 0px; margin-top: 5px">
                    <div id="qr_code">
                    </div>
                    <small style="font-size: 8px; font-weight: 100">Impreso por: {{ Auth::user()->name }} <br>
                        {{ date('d/m/Y H:i:s') }}</small>
                </h3>
            </td>
        </tr>
    </table>
    <br>
    {{-- <table style="width: 100%; font-size: 10px" border="1" class="print-friendly" cellspacing="0" cellpadding="4"> --}}
    <table style="width: 100%; font-size: 12px" border="1" cellspacing="0" cellpadding="4">
        <thead>
            <tr>
                <th rowspan="2" style="width:5px">N&deg;</th>
                <th rowspan="2" style="text-align: center">MES</th>
                <th rowspan="2" style="text-align: center">CAPITAL</th>
                <th rowspan="2" style="text-align: center">INTERES</th>
                <th rowspan="2" style="text-align: center">K + INT. GENERADO Bs.</th>
                <th colspan="3" style="text-align: center">Bs.</th>
                <th colspan="3" style="text-align: center">%</th>
            </tr>
            <tr>
                <th style="text-align: center">PAGADO</th>
                <th style="text-align: center">DEUDA</th>
                <th style="text-align: center">MORA</th>

                <th style="text-align: center; width: 60px">PAGADO</th>
                <th style="text-align: center; width: 60px">DEUDA</th>
                <th style="text-align: center; width: 60px">MORA</th>                
            </tr>
        </thead>
        <tbody>
            @php
                $count = 1;
                $months = [
                    '',
                    'Enero',
                    'Febrero',
                    'Marzo',
                    'Abril',
                    'Mayo',
                    'Junio',
                    'Julio',
                    'Agosto',
                    'Septiembre',
                    'Octubre',
                    'Noviembre',
                    'Diciembre',
                ];
                $amountLoan = 0;
                $amountPorcentage = 0;
                $capitalPorcentage = 0;
                $pagado = 0;
                $deuda = 0;
                $mora = 0;
            @endphp
            @forelse ($datas as $item)
                <tr>
                    <td>{{ $count }}</td>
                    <td style="text-align: left">{{ $item->monthDate ? $months[$item->monthDate].'-' : '' }}{{ $item->yearDate }}</td>
                    <td style="text-align: right">{{ number_format($item->amountLoan, 2, ',', '.') }}</td>
                    <td style="text-align: right">{{ number_format($item->amountPorcentage, 2, ',', '.') }}</td>
                    <td style="text-align: right">{{ number_format($item->capitalPorcentage, 2, ',', '.') }}</td>
                    <td style="text-align: right">{{ number_format($item->pagado, 2, ',', '.') }}</td>  
                    <td style="text-align: right">{{ number_format($item->deuda, 2, ',', '.') }}</td>
                    <td style="text-align: right">{{ number_format($item->mora, 2, ',', '.') }}</td>
                    <td style="text-align: right">
                        {{ number_format(($item->pagado / $item->capitalPorcentage) * 100, 2, ',', '.') }} %
                    </td>
                    <td style="text-align: right">
                        {{ number_format(($item->deuda / $item->capitalPorcentage) * 100, 2, ',', '.') }} %
                    </td>
                    <td style="text-align: right">
                        {{ number_format(($item->mora / $item->capitalPorcentage) * 100, 2, ',', '.') }} %
                    </td>

                    
                </tr>
                @php
                    $count++;
                    $amountLoan += $item->amountLoan;
                    $amountPorcentage += $item->amountPorcentage;
                    $capitalPorcentage += $item->capitalPorcentage;
                    $pagado += $item->pagado;
                    $deuda += $item->deuda;
                    $mora += $item->mora;
                @endphp
            @empty
                <tr style="text-align: center">
                    <td colspan="11">No se encontraron registros.</td>
                </tr>
            @endforelse
            <tr>
                <td colspan="2" style="text-align: left">Total</td>
                <td style="text-align: right">{{ number_format($amountLoan, 2, ',', '.') }}</td>
                <td style="text-align: right">{{ number_format($amountPorcentage, 2, ',', '.') }}</td>
                <td style="text-align: right">{{ number_format($capitalPorcentage, 2, ',', '.') }}</td>
                <td style="text-align: right">{{ number_format($pagado, 2, ',', '.') }}</td>
                <td style="text-align: right">{{ number_format($deuda, 2, ',', '.') }}</td>
                <td style="text-align: right">{{ number_format($mora, 2, ',', '.') }}</td>
                <td style="text-align: right"></td>
                <td style="text-align: right"></td>
                <td style="text-align: right"></td>
                
            </tr>
        </tbody>
    </table>

    <br>


    {{-- <div id="chartContainer" style="height: 250px; width: 100%;"></div> --}}
    <div id="chartContainer" style="height: 250px; width: 50%;"></div>



@endsection
@section('css')
    <style>
        table,
        th,
        td {
            border-collapse: collapse;
        }

        /* table.print-friendly tr td, table.print-friendly tr th {
                    page-break-inside: avoid;
                } */
    </style>
    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
    <script>
        window.onload = function() {

            let incomeData = @json($datas);
            let m = new Array("", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto",
                "Septiembre", "Octubre", "Noviembre", "Diciembre");




            var chart = new CanvasJS.Chart("chartContainer", {
                // theme:"light2",
                animationEnabled: true,
                title: {
                    // text: "REPORTE MENSUAL DE PRESTAMOS GESTION "
                },
                subtitles: [{
                    text: "REPORTE MENSUAL DE PRESTAMOS GESTION "
                }],
                axisX: {
                    lineColor: "black",
                    labelFontColor: "black"
                },
                axisY: {
                    title: "Monto",
                    suffix: "Bs"
                },
                toolTip: {
                    shared: "true"
                },
                legend: {
                    cursor: "pointer",
                    itemclick: toggleDataSeries
                },
                data: [{
                        type: "spline",
                        visible: true,
                        showInLegend: true,
                        yValueFormatString: "##.00",
                        name: "CAPITAL",
                        dataPoints: incomeData.map(item => ({
                            label: (item.monthDate ? m[item.monthDate] + '-' : '') + item.yearDate,
                            y: parseFloat(item.amountLoan)
                        }))
                    },
                    {
                        type: "spline",
                        visible: true,
                        showInLegend: true,
                        yValueFormatString: "##.00",
                        name: "INTERES GENERADO Bs",
                        dataPoints: incomeData.map(item => ({
                            label: (item.monthDate ? m[item.monthDate] + '-' : '') + item.yearDate,
                            y: parseFloat(item.amountPorcentage)
                        }))
                    },
                    {
                        type: "spline",
                        visible: true,
                        showInLegend: true,
                        yValueFormatString: "##.00",
                        name: "K + INT. GENERADO Bs",
                        dataPoints: incomeData.map(item => ({
                            label: (item.monthDate ? m[item.monthDate] + '-' : '') + item.yearDate,
                            y: parseFloat(item.capitalPorcentage)
                        }))
                    },
                    {
                        type: "spline",
                        visible: true,
                        showInLegend: true,
                        yValueFormatString: "##.00",
                        name: "PAGADO Bs",
                        dataPoints: incomeData.map(item => ({
                            label: (item.monthDate ? m[item.monthDate] + '-' : '') + item.yearDate,
                            y: parseFloat(item.pagado)
                        }))
                    },
                    {
                        type: "spline",
                        visible: true,
                        showInLegend: true,
                        yValueFormatString: "##.00",
                        name: "DEUDA TOTAL",
                        dataPoints: incomeData.map(item => ({
                            label: (item.monthDate ? m[item.monthDate] + '-' : '') + item.yearDate,
                            y: parseFloat(item.deuda)
                        }))
                    },
                    {
                        type: "spline",
                        visible: true,
                        showInLegend: true,
                        yValueFormatString: "##.00",
                        name: "DEUDA CON MORA Bs",
                        dataPoints: incomeData.map(item => ({
                            label: (item.monthDate ? m[item.monthDate] + '-' : '') + item.yearDate,
                            y: parseFloat(item.mora)
                        }))
                    }



                ]
            });


            chart.render();

            function toggleDataSeries(e) {
                if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                    e.dataSeries.visible = false;
                } else {
                    e.dataSeries.visible = true;
                }
                chart.render();
            }

        }
    </script>
@stop

@section('javascript')

@stop
