@extends('layouts.template-notification', ['title' => 'Comprobante de Pago'])

@php
    $months = array('', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');    
    $payment = $transaction->payments->first();
@endphp

@section('body')
    <p class="msj">Pago Recibido Exitosamente</p>
    <p class="money">
        <span>Bs.</span> {{ number_format($transaction->payments->sum('amount'), 2, ',', '.') }}
    </p>

    <div style="width: 100%; text-align: left; margin-bottom: 20px;">
        <p style="font-size: 14px; color: #6c757d; margin-bottom: 5px;">
            <strong>Nro. Transacción:</strong> {{ str_pad($transaction->id, 8, "0", STR_PAD_LEFT) }}
        </p>
        <p style="font-size: 14px; color: #6c757d;">
            <strong>Fecha y Hora:</strong> {{ date('d/m/Y H:i', strtotime($transaction->created_at)) }}
        </p>
    </div>

    <h4 style="text-align: left; font-size: 16px; font-weight: 500; margin-bottom: 10px; width: 100%;">Detalle del Pago</h4>
    <table class="table-details" style="width: 100%;">
        <thead>
            <tr>
                <th style="text-align: center;">Cuota</th>
                <th>Dia Pagado</th>
                <th style="text-align: center;">Estado</th>
                <th style="text-align: right;">Monto Pagado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transaction->payments as $item)
                <tr>
                    <td style="text-align: center;">{{ $item->loanDay->number }}</td>
                    <td>{{ date('d/m/Y', strtotime($item->loanDay->date)) }}</td>
                    <td style="text-align: center;">
                        @if($item->loanDay->late)
                            <span style="color: #dc3545; font-weight: bold;">Atraso</span>
                        @else
                            <span style="color: #28a745; font-weight: bold;">Puntual</span>
                        @endif
                    </td>
                    <td style="text-align: right;">{{ number_format($item->amount, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold;">
                <td colspan="2" style="text-align: right;">Total Pagado</td>
                <td style="text-align: right;">{{ number_format($transaction->payments->sum('amount'), 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
@endsection

@section('info')
    <div class="group-table">
        <p class="account">Titular del préstamo</p>
        <p class="name">{{ $payment->loanDay->loan->people->first_name }} {{ $payment->loanDay->loan->people->last_name1 }} {{ $payment->loanDay->loan->people->last_name2 }}</p>
        <p class="number-account">
            <strong>CI:</strong> {{ $payment->loanDay->loan->people->ci ?? 'No definido' }} <br>
            <strong>Cód. Préstamo:</strong> {{ $payment->loanDay->loan->code }}
        </p>
    </div>
    <div class="group-table">
        <p class="account">Atendido por</p>
        <p class="name">{{ $payment->agent->name }}</p>
        <p class="number-account">{{ $payment->agentType }}</p>
    </div>
@endsection

@section('footer')
    <div style="margin-bottom: 15px;">
        {!! QrCode::size(100)->generate(Request::url()) !!}
    </div>
    <p>Escanee el código QR para verificar la transacción.</p>
    <p style="margin-top: 5px;"><strong>{{ setting('admin.title') }}</strong> le agradece por su pago.</p>
@endsection