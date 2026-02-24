@extends('layouts.template-print-horizontal')

@section('page_title', 'Reporte de Lista de Personas Deudoras')

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
            <td style="text-align: center; width:50%">
                <h3 style="margin-bottom: 0px; margin-top: 5px">EMPRESA "CAPRESI"<br></h3>
                <h4 style="margin-bottom: 0px; margin-top: 5px">REPORTE DE LISTA DE PERSONAS DEUDORAS</h4>
                <small style="margin-bottom: 0px; margin-top: 5px">{{ date('d') }} de {{ $months[intval(date('m'))] }} de {{ date('Y') }}</small>
            </td>
            <td style="text-align: right; width:30%">
                <h3 style="margin-bottom: 0px; margin-top: 5px">
                    <small style="font-size: 8px; font-weight: 100">Impreso por: {{ Auth::user()->name }} <br>{{ date('d/m/Y H:i:s') }}</small>
                </h3>
            </td>
        </tr>
    </table>
    <br>

    <table style="width:100%" class="table table-bordered table-striped table-sm">
        <thead>
            <tr>
                <th style="width:5px">N&deg;</th>
                <th style="text-align: center">CLIENTE</th>
                <th style="text-align: center">C&Oacute;DIGO PR&Eacute;STAMO</th>
                <th style="text-align: center">FECHA ENTREGA</th>
                <th style="text-align: center">ESTADO</th>
                <th style="text-align: center">RUTA</th>
                <th style="text-align: center">CAPITAL</th>
                <th style="text-align: center">INTER&Eacute;S</th>
                <th style="text-align: center">TOTAL</th>
                <th style="text-align: center">DEUDA</th>
            </tr>
        </thead>
        <tbody>
            @php
                $count = 1;
                $totalCapital = 0;
                $totalInteres = 0;
                $total = 0;
                $totalDeuda = 0;
                $personTotalCapital = 0;
                $personTotalInteres = 0;
                $personTotal = 0;
                $personTotalDeuda = 0;
            @endphp
            @forelse ($people as $person)
                @php
                    $personTotalCapital = 0;
                    $personTotalInteres = 0;
                    $personTotal = 0;
                    $personTotalDeuda = 0;
                @endphp
                @foreach($person->loans as $loan)
                    <tr>
                        <td>{{ $count }}</td>
                        <td>{{ $loop->first ? $person->first_name . ' ' . $person->last_name1 . ' ' . $person->last_name2 : '' }}</td>
                        <td style="text-align: center">{{ $loan->code }}</td>
                        <td style="text-align: center">{{ \Carbon\Carbon::parse($loan->dateDelivered)->format('d/m/Y') }}</td>
                        <td style="text-align: center">
                            @php
                                $loanDays = $loan->loanDay->sortBy('date');
                                $lastDate = $loanDays->last()->date ?? null;
                                $today = \Carbon\Carbon::now()->format('Y-m-d');
                                
                                if ($lastDate && $today > $lastDate) {
                                    $status = 'MORA';
                                    $badgeClass = 'danger';
                                } else {
                                    $status = 'VIGENTE';
                                    $badgeClass = 'success';
                                }
                            @endphp
                            {{ $status }}
                        </td>
                        <td style="text-align: center">{{ $loan->current_loan_route->route->name ?? 'N/A' }}</td>
                        <td style="text-align: right">{{ number_format($loan->amountLoan, 2, ',','.') }}</td>
                        <td style="text-align: right">{{ number_format($loan->amountPorcentage, 2, ',','.') }}</td>
                        <td style="text-align: right">{{ number_format($loan->amountTotal, 2, ',','.') }}</td>
                        <td style="text-align: right">{{ number_format($loan->debt, 2, ',','.') }}</td>
                    </tr>
                    @php
                        $count++;
                        $totalCapital += $loan->amountLoan;
                        $totalInteres += $loan->amountPorcentage;
                        $total += $loan->amountTotal;
                        $totalDeuda += $loan->debt;
                        
                        $personTotalCapital += $loan->amountLoan;
                        $personTotalInteres += $loan->amountPorcentage;
                        $personTotal += $loan->amountTotal;
                        $personTotalDeuda += $loan->debt;
                    @endphp
                @endforeach
                <tr style="background-color: #f9f9f9;">
                    <td colspan="6" style="text-align: right"><strong>Total {{ $person->first_name }} {{ $person->last_name1 }}</strong></td>
                    <td style="text-align: right"><strong>{{ number_format($personTotalCapital, 2, ',','.') }}</strong></td>
                    <td style="text-align: right"><strong>{{ number_format($personTotalInteres, 2, ',','.') }}</strong></td>
                    <td style="text-align: right"><strong>{{ number_format($personTotal, 2, ',','.') }}</strong></td>
                    <td style="text-align: right"><strong>{{ number_format($personTotalDeuda, 2, ',','.') }}</strong></td>
                </tr>
            @empty
                <tr style="text-align: center">
                    <td colspan="10">No se encontraron registros.</td>
                </tr>
            @endforelse
            <tr style="background-color: #e8e8e8;">
                <td colspan="6" style="text-align: left"><strong>TOTAL GENERAL</strong></td>
                <td style="text-align: right"><strong>{{ number_format($totalCapital, 2, ',','.') }}</strong></td>
                <td style="text-align: right"><strong>{{ number_format($totalInteres, 2, ',','.') }}</strong></td>
                <td style="text-align: right"><strong>{{ number_format($total, 2, ',','.') }}</strong></td>
                <td style="text-align: right"><strong>{{ number_format($totalDeuda, 2, ',','.') }}</strong></td>
            </tr>
        </tbody>
    </table>
@endsection
