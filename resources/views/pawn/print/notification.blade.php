@extends('layouts.template-notification', ['title' => 'RECIBO DE PAGO'])

@php
    $months = array('', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');    
@endphp

@section('body')
    <p class="msj">Pago Exitoso!</p>
    <p class="money"><span>Bs</span> {{ $payment->amount }}</p>
    <p class="name-empresa">{{ setting('admin.title') }}</p>
    <p class="datetime">{{ date('d', strtotime($payment->created_at)) }} DE {{ Str::upper($months[intval(date('m', strtotime($payment->created_at)))]) }}, {{ date('Y H:i', strtotime($payment->created_at)) }}</p>
@endsection

@section('info')
    <div class="group-table">
        <p class="account">Titular del préstamo</p>
        <p class="name">{{ $payment->pawn->person->first_name }} {{ $payment->pawn->person->last_name1 }} {{ $payment->pawn->person->last_name2 }}</p>
        <p class="number-account">
            <span>CI: </span> {{ $payment->pawn->person->ci ?? 'No definido' }}
        </p>
    </div>
    <div class="group-table">
        <p class="account">Atendido por</p>
        <p class="name">{{ $payment->user->name }}</p>
    </div>
@endsection

@section('footer')
    <p>RECIBO N° {{ str_pad($payment->id, 6, "0", STR_PAD_LEFT) }}</p>
@endsection