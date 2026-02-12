@extends('layouts.template-notification', ['title' => 'RECIBO DE PAGO'])

@php
    $months = array('', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');    
    $payment = $transaction->payments[0];
@endphp

@section('body')
    <p class="msj">Pago Exitoso!</p>
    <p class="money"><span>Bs</span> {{ $transaction->payments->sum('amount') }}</p>
    <table class="table-details">
        <thead>
            <tr>
                <th>N&deg;</th>
                <th>Fecha</th>
                <th>Atraso</th>
                <th>Monto</th>
            </tr>
        </thead>
        <tbody>
            @php
                $cont = 1;
            @endphp
            @foreach ($transaction->payments as $item)
                <tr>
                    <td>{{ $cont }}</td>
                    <td>{{ date('d', strtotime($item->loanDay->date)) }}/{{ Str::upper(substr($months[intval(date('m', strtotime($item->loanDay->date)))], 0, 3)) }}/{{ date('Y', strtotime($item->loanDay->date)) }}</td>
                    <td style="text-align: center">{{ $item->late == 1 ? ' SI' : ' NO' }}</td>
                    <td style="text-align: right">{{ $item->amount }}</td>
                </tr>
                @php
                    $cont++;
                @endphp
            @endforeach
        </tbody>
    </table>
    <br>
    <p class="name-empresa">{{ setting('admin.title') }}</p>
    <p class="datetime">{{ date('d', strtotime($transaction->created_at)) }} DE {{ Str::upper($months[intval(date('m', strtotime($transaction->created_at)))]) }}, {{ date('Y H:i', strtotime($transaction->created_at)) }}</p>
@endsection

@section('info')
    <div class="group-table">
        <p class="account">Titular del préstamo</p>
        <p class="name">{{ $payment->loanDay->loan->people->first_name }} {{ $payment->loanDay->loan->people->last_name1 }} {{ $payment->loanDay->loan->people->last_name2 }}</p>
        <p class="number-account">
            <span>CI: </span> {{ $payment->loanDay->loan->people->ci ?? 'No definido' }}
        </p>
    </div>
    <div class="group-table">
        <p class="account">Atendido por</p>
        <p class="name">{{ $payment->agent->name }} - {{ $payment->agentType }}</p>
    </div>
@endsection

@section('footer')
    <p>RECIBO N° {{ str_pad($transaction->transaction, 6, "0", STR_PAD_LEFT) }}</p>
@endsection