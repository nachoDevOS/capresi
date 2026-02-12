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
            <td style="text-align: center;  width:60%">
                <h3 style="margin-bottom: 0px; margin-top: 5px">
                    EMPRESA "CAPRESI"<br>
                </h3>
                <h4 style="margin-bottom: 0px; margin-top: 5px">
                    DETALLE DE PAGO POR GESTION Y % PAGO
                    <br>
                    @if ($start == $finish)
                        {{ $start }}
                    @else
                        {{ $start }} Al {{ $finish }}
                    @endif
                </h4>
            </td>
            <td style="text-align: right; width:20%">
                <h3 style="margin-bottom: 0px; margin-top: 5px">

                    <small style="font-size: 8px; font-weight: 100">Impreso por: {{ Auth::user()->name }} <br>
                        {{ date('d/m/Y H:i:s') }}</small>
                </h3>
            </td>
        </tr>
    </table>
    <br>
    <table style="width: 100%; font-size: 12px" border="1" cellspacing="0" cellpadding="4">
        <thead>
            <tr>
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
            </tr>
        </thead>
        <tbody>
            @php
                $count = 1;
                $amountLoan = 0;
                $pagado = 0;
                $deuda = 0;
                $mora = 0;
                $capital = 0;
                $interes = 0;
            @endphp
            @forelse ($datas as $item)
                <tr>
                    <td>{{ $count }}</td>
                    <td style="text-align: left">{{ $item->yearDate }}</td>
                    <td style="text-align: right">{{ number_format($item->capital, 2, ',', '.') }}</td>
                    <td style="text-align: right">{{ number_format($item->interes, 2, ',', '.') }}</td>
                    <td style="text-align: right">{{ number_format($item->amountLoan, 2, ',', '.') }}</td>
                    <td style="text-align: right">{{ number_format($item->pagado, 2, ',', '.') }}</td>
                    <td style="text-align: right">{{ number_format($item->deuda, 2, ',', '.') }}</td>
                    <td style="text-align: right">{{ number_format($item->mora, 2, ',', '.') }}</td>
                    <td style="text-align: right">{{ number_format(($item->pagado / $item->amountLoan) * 100, 2, ',', '.') }} %
                    </td>
                    <td style="text-align: right">{{ number_format(($item->deuda / $item->amountLoan) * 100, 2, ',', '.') }} %
                    </td>
                    <td style="text-align: right">{{ number_format(($item->mora / $item->amountLoan) * 100, 2, ',', '.') }} %
                    </td>
                </tr>
                @php
                    $count++;
                    $amountLoan += $item->amountLoan;
                    $pagado += $item->pagado;
                    $deuda += $item->deuda;
                    $mora += $item->mora;
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
                <td style="text-align: right">{{ number_format($capital, 2, ',', '.') }}</td>
                <td style="text-align: right">{{ number_format($interes, 2, ',', '.') }}</td>
                <td style="text-align: right">{{ number_format($amountLoan, 2, ',', '.') }}</td>
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

    <div id="chartContainer" style="height: 250px; width: 50%;"></div>




@endsection
@section('css')
    <style>
        table,
        th,
        td {
            border-collapse: collapse;
        }
    </style>

    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
    <script>
        window.onload = function() {

            let incomeData = @json($datas);
            let m = new Array("", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto",
                "Septiembre", "Octubre", "Noviembre", "Diciembre");

            labels = [];
            capital = [];

            incomeData.map(item => {
                labels.push(item.monthDate);
                capital.push(parseInt(item.amountLoan));
            });
            // alert(capital)

            var chart = new CanvasJS.Chart("chartContainer", {
                theme: "light2",
                animationEnabled: true,
                title: {
                    // text: "REPORTE MENSUAL DE PRESTAMOS GESTION "
                },
                subtitles: [{
                    text: "DETALLE DE PAGO POR GESTION Y % PAGO"
                }],
                axisY: {
                    title: "Monto",
                    suffix: "Bs"
                },
                toolTip: {
                    shared: "true"
                },
                legend: {
                    cursor: "pointer",
                    itemclick: toggleDataSeries,
                },
                data: [{
                        type: "spline",
                        visible: true,
                        showInLegend: true,
                        yValueFormatString: "##.00",
                        name: "MONTO PRESTADO",
                        dataPoints: incomeData.map(item => ({
                            label: item.yearDate,
                            y: parseFloat(item.amountLoan)
                        }))
                    },
                    {
                        type: "spline",
                        visible: true,
                        showInLegend: true,
                        yValueFormatString: "##.00",
                        name: "PAGADO Bs",
                        dataPoints: incomeData.map(item => ({
                            label: item.yearDate,
                            y: parseFloat(item.pagado)
                        }))
                    },
                    {
                        type: "spline",
                        visible: true,
                        showInLegend: true,
                        yValueFormatString: "##.00",
                        name: "SALDO DEUDA POR PAGAR Bs",
                        dataPoints: incomeData.map(item => ({
                            label: item.yearDate,
                            y: parseFloat(item.deuda)
                        }))
                    },
                    {
                        type: "spline",
                        visible: true,
                        showInLegend: true,
                        yValueFormatString: "##.00",
                        name: "PAGO DE PRESTAMOS",
                        dataPoints: incomeData.map(item => ({
                            label: item.yearDate,
                            y: parseFloat((item.pagado / item.amountLoan) * 100)
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.1.4/Chart.bundle.min.js"></script>

    <script>
        $(document).ready(function() {


        });
    </script>
@stop
