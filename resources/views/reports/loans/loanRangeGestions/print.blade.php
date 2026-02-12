@extends('layouts.template-print-horizontal')

@section('page_title', 'Reporte')

@section('content')
    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>

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
                    REPORTE MENSUAL DE PRESTAMOS DE LA GESTION
                    {{-- <br>
                    @if ($start == $finish)
                        {{ $start }}
                    @else
                        {{ $start }} Al {{ $finish }}
                    @endif               --}}
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
                        {{-- @if ($start != $finish)
                            {!! QrCode::size(80)->generate('Total Cobrado: Bs'.number_format($amountTotal,2, ',', '.').', Recaudado en Fecha '.date('d', strtotime($start)).' de '.strtoupper($months[intval(date('m', strtotime($start)))] ).' de '.date('Y', strtotime($start)).' al '.date('d', strtotime($finish)).' de '.strtoupper($months[intval(date('m', strtotime($finish)))] ).' de '.date('Y', strtotime($finish))); !!}
                        @else
                            {!! QrCode::size(80)->generate('Total Cobrado: Bs'.number_format($amountTotal,2, ',', '.').', Recaudado en Fecha '.date('d', strtotime($start)).' de '.strtoupper($months[intval(date('m', strtotime($start)))] ).' de '.date('Y', strtotime($start))); !!}
                        @endif --}}
                    </div>
                    <small style="font-size: 8px; font-weight: 100">Impreso por: {{ Auth::user()->name }} <br>
                        {{ date('d/m/Y H:i:s') }}</small>
                </h3>
            </td>
        </tr>
    </table>
    <br>
    @foreach ($datas->groupBy('yearDate') as $yearDate => $year)
        <table style="width: 100%; font-size: 12px" border="1" cellspacing="0" cellpadding="4">

            <thead>
                <tr>
                    <th colspan="10" style="text-align: center">GESTION {{ $yearDate }}</th>
                </tr>
                <tr>
                    <th rowspan="2" style="width:5px">N&deg;</th>
                    <th rowspan="2" style="text-align: center">MES</th>
                    <th rowspan="2"style="text-align: center">CAPITAL</th>
                    <th rowspan="2"style="text-align: center">MONTO PRESTADO + INTERES Bs.</th>

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
                    $capital = 0;
                    $pagado = 0;
                    $deuda = 0;
                    $mora = 0;
                @endphp
                @forelse ($year as $item)
                    <tr>
                        <td>{{ $count }}</td>
                        <td style="text-align: left">{{ $months[$item->monthDate] }}</td>
                        <td style="text-align: right">{{ number_format($item->capital, 2, ',', '.') }}</td>
                        <td style="text-align: right">{{ number_format($item->amountLoan, 2, ',', '.') }}</td>
                        <td style="text-align: right">{{ number_format($item->pagado, 2, ',', '.') }}</td>
                        <td style="text-align: right">{{ number_format($item->deuda, 2, ',', '.') }}</td>
                        <td style="text-align: right">{{ number_format($item->mora, 2, ',', '.') }}</td>

                        <td style="text-align: right">
                            {{ number_format(($item->pagado / $item->amountLoan) * 100, 2, ',', '.') }} %</td>
                        <td style="text-align: right">
                            {{ number_format(($item->deuda / $item->amountLoan) * 100, 2, ',', '.') }} %</td>
                        <td style="text-align: right">
                            {{ number_format(($item->mora / $item->amountLoan) * 100, 2, ',', '.') }} %</td>
                    </tr>
                    @php
                        $count++;
                        $amountLoan += $item->amountLoan;
                        $pagado += $item->pagado;
                        $deuda += $item->deuda;
                        $capital += $item->capital;
                        $mora += $item->mora;
                    @endphp
                @empty
                    <tr style="text-align: center">
                        <td colspan="10">No se encontraron registros.</td>
                    </tr>
                @endforelse
                <tr>
                    <td colspan="2" style="text-align: left">Total</td>
                    <td style="text-align: right">{{ number_format($capital, 2, ',', '.') }}</td>
                    <td style="text-align: right">{{ number_format($amountLoan, 2, ',', '.') }}</td>
                    <td style="text-align: right">{{ number_format($pagado, 2, ',', '.') }}</td>
                    <td style="text-align: right">{{ number_format($deuda, 2, ',', '.') }}</td>
                    <td style="text-align: right">{{ number_format($mora, 2, ',', '.') }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        {{-- <div id="chartContainer-{{$yearDate}}" style="height: 250px; width: 100%;"></div> --}}
        <div id="chartContainer-{{ $yearDate }}" style="height: 250px; width: 50%;"></div>


        <script>
            incomeData = @json($year);
            m = new Array("", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre",
                "Noviembre", "Diciembre");

            labels = [];
            capital = [];

            incomeData.map(item => {
                labels.push(item.monthDate);
                capital.push(parseInt(item.amountLoan));
            });

            var chart = new CanvasJS.Chart("chartContainer-" + '{{ $yearDate }}', {
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
                        name: "CAPITAL + INTERES Bs",
                        dataPoints: incomeData.map(item => ({
                            label: m[item.monthDate],
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
                            label: m[item.monthDate],
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
                            label: m[item.monthDate],
                            y: parseFloat(item.deuda)
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
        </script>



        <br>
    @endforeach


@endsection
@section('css')
    <style>
        table,
        th,
        td {
            border-collapse: collapse;
        }

        table.print-friendly tr td,
        table.print-friendly tr th {
            page-break-inside: avoid;
        }
    </style>
@stop

@section('javascript')

    {{-- <script>

        $(document).ready(function() {

            
        });
    </script> --}}
@stop
