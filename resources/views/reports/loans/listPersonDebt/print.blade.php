@extends('layouts.template-print')

@section('page_title', 'Reporte de Lista de Personas Deudoras')

@section('content')
    @php
        $months = array('', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');
    @endphp

    <table width="100%">
        <tr>
            <td style="width: 20%"><img src="{{ asset('images/icon.png') }}" alt="CAPRESI" width="70px"></td>
            <td style="text-align: center;  width:60%">
                <h3 style="margin-bottom: 0px; margin-top: 5px">
                    EMPRESA "CAPRESI"<br>
                </h3>
                <h4 style="margin-bottom: 0px; margin-top: 5px">
                    REPORTE DE LISTA DE PERSONAS DEUDORAS
                </h4>
                <small style="margin-bottom: 0px; margin-top: 5px">
                    {{ date('d') }} de {{ $months[intval(date('m'))] }} de {{ date('Y') }}
                </small>
            </td>
            <td style="text-align: right; width:20%">
                <h3 style="margin-bottom: 0px; margin-top: 5px">
                    <small style="font-size: 11px; font-weight: 100">Impreso por: {{ Auth::user()->name }} <br> {{ date('d/m/Y H:i:s') }}</small>
                </h3>
            </td>
        </tr>
    </table>
    <br>
    <table id="dataTable" style="width: 100%; font-size: 10px" border="1" cellspacing="0" cellpadding="4">
        <thead>
            <tr style="background-color: #e8e8e8;">
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
            @php $count = 1; @endphp
            @forelse ($people as $person)
                @foreach($person->loans as $loan)
                    <tr>
                        <td>{{ $count }}</td>
                        <td>{{ $person->first_name . ' ' . $person->last_name1 . ' ' . $person->last_name2 }}</td>
                        <td style="text-align: center">{{ $loan->code }}</td>
                        <td style="text-align: center" data-order="{{ \Carbon\Carbon::parse($loan->dateDelivered)->format('Y-m-d') }}">
                            {{ \Carbon\Carbon::parse($loan->dateDelivered)->format('d/m/Y') }}
                        </td>
                        <td style="text-align: center">
                            @php
                                $loanDays = $loan->loanDay->sortBy('date');
                                $lastDate = $loanDays->last()->date ?? null;
                                $today = \Carbon\Carbon::now()->format('Y-m-d');
                                
                                if ($lastDate && $today > $lastDate) {
                                    $status = 'MORA';
                                } else {
                                    $status = 'VIGENTE';
                                }
                            @endphp
                            {{ $status }}
                        </td>
                        <td style="text-align: center">{{ $loan->current_loan_route->route->name ?? 'N/A' }}</td>
                        <td style="text-align: right" data-order="{{ $loan->amountLoan }}">{{ number_format($loan->amountLoan, 2, ',','.') }}</td>
                        <td style="text-align: right" data-order="{{ $loan->amountPorcentage }}">{{ number_format($loan->amountPorcentage, 2, ',','.') }}</td>
                        <td style="text-align: right" data-order="{{ $loan->amountTotal }}">{{ number_format($loan->amountTotal, 2, ',','.') }}</td>
                        <td style="text-align: right" data-order="{{ $loan->debt }}">{{ number_format($loan->debt, 2, ',','.') }}</td>
                    </tr>
                    @php $count++; @endphp
                @endforeach
            @empty
                <tr style="text-align: center">
                    <td colspan="10">No se encontraron registros.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable({
                ordering: true,
                paging: false,
                order: [[0, 'asc']],
                language: {
                    search: "Buscar:",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    infoEmpty: "Mostrando 0 a 0 de 0 registros",
                    infoFiltered: "(filtrado de _MAX_ registros totales)",
                    lengthMenu: "Mostrar _MENU_ registros",
                    zeroRecords: "No se encontraron registros",
                    emptyTable: "No hay datos disponibles",
                    thousands: ".",
                    decimal: ",",
                    orderHeader: {
                        0: "Activar para ordenar",
                        1: "Activar para ordenar ascendente",
                        2: "Activar para ordenar descendente"
                    }
                }
            });
        });
    </script>

@endsection

@section('css')
    <style>
        table, th, td {
            border-collapse: collapse;
        }
        @media print {
            .dataTables_length,
            .dataTables_filter,
            .dataTables_info,
            .dataTables_paginate {
                display: none !important;
            }
            .dataTables_wrapper {
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>
@stop