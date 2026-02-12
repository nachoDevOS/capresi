@extends('voyager::master')

@section('page_title', 'Ver Empeño de Artículo')

@section('page_header')
    <h1 class="page-title">
        <i class="fa fa-handshake"></i> Viendo Empeño de Artículo &nbsp;
        <a href="{{ route('pawn.index') }}" class="btn btn-warning">
            <span class="glyphicon glyphicon-list"></span>&nbsp;
            Volver a la lista
        </a>
        @if (($pawn->status == 'entregado' || $pawn->status == 'expiro') && $pawn->deleted_at == null && $pawn->inventory == 0)
            <a href="#" class="btn btn-success" data-toggle="modal" data-target="#moneyAditional-modal">
                <i class="fa-solid fa-plus"></i><i class="fa-solid fa-money-bills"></i>&nbsp;Adicionar Monto
            </a>
        @endif
        @if ($pawn->status == 'expiro' && $pawn->inventory == 0)
            <a href="#" class="btn btn-success" data-toggle="modal" data-target="#inventory-modal">
                <i class="fa-solid fa-shop"></i>&nbsp;Agregar a Inventario
            </a>
        @endif
        
        
    </h1>
@stop

@php
    $months = array('', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');    
@endphp

@section('content')
    <div class="page-content read container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered" style="padding-bottom:5px;">
                    <div class="row">
                        @if ($pawn->status == 'expiro')
                            <div class="col-md-12">
                                <div class="alert alert-warning">
                                    <strong>Advertencia:</strong>
                                    <p>Ha excedido el tiempo de espera.</p>
                                </div>
                            </div>
                        @endif
                        <div class="col-md-6">
                            <div class="panel-heading" style="border-bottom:0;">
                                <h3 class="panel-title">Código</h3>
                            </div>
                            <div class="panel-body" style="padding-top:0;">
                                <p>{{ $pawn->code }}  - {{$pawn->codeManual?$pawn->codeManual:'S/N'}}</p>
                                {{-- <input type="text"> --}}
                            </div>
                            <hr style="margin:0;">
                        </div>
                        <div class="col-md-6">
                            <div class="panel-heading" style="border-bottom:0;">
                                <h3 class="panel-title">Beneficiario</h3>
                            </div>
                            <div class="panel-body" style="padding-top:0;">
                                <p>{{ $pawn->person->first_name }} {{ $pawn->person->last_name1 }} {{ $pawn->person->last_name2 }}</p>
                            </div>
                            <hr style="margin:0;">
                        </div>
                        <div class="col-md-6">
                            <div class="panel-heading" style="border-bottom:0;">
                                <h3 class="panel-title">Fecha de prestamo</h3>
                            </div>
                            <div class="panel-body" style="padding-top:0;">
                                <p>{{ date('d', strtotime($pawn->date)) }} de {{ $months[intval(date('m', strtotime($pawn->date)))] }} de {{ date('Y', strtotime($pawn->date)) }}</p>
                            </div>
                            <hr style="margin:0;">
                        </div>
                        <div class="col-md-6">
                            <div class="panel-heading" style="border-bottom:0;">
                                <h3 class="panel-title">Fecha límite de devolución</h3>
                            </div>
                            <div class="panel-body" style="padding-top:0;">
                                <p>
                                    @if ($pawn->date_limit)
                                        {{ date('d', strtotime($pawn->date_limit)) }} de {{ $months[intval(date('m', strtotime($pawn->date_limit)))] }} de {{ date('Y', strtotime($pawn->date_limit)) }}
                                    @else
                                        Sin Entregar
                                    @endif
                                </p>
                            </div>
                            <hr style="margin:0;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <div >
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel-heading" style="border-bottom:0;">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h3 class="panel-title">Detalle de artículos</h3>
                                        </div>
                                        <div class="col-md-4 text-right" style="padding-top: 20px">
                                        </div>
                                    </div>
                                </div>
                                <table id="dataStyle" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>N&deg;</th>
                                            <th>Tipo de artículo</th>
                                            <th>Características</th>
                                            <th>Cantidad</th>
                                            <th>Precio</th>
                                            <th>Observaciones</th>
                                            <th class="text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $cont = 1;
                                            $total = 0;
                                        @endphp
                                        @forelse ($pawn->details as $item)
                                            <tr>
                                                <td>{{ $cont }}</td>
                                                <td>
                                                    {{ $item->type->name }} <br>
                                                    <small>{{ $item->type->category->name }}</small>
                                                </td>
                                                <td>
                                                    @php
                                                        $features_list = '';
                                                        foreach ($item->features_list as $feature) {
                                                            if ($feature->value) {
                                                                $features_list .= '<span><b>'.$feature->title.'</b>: '.$feature->value.'</span><br>';
                                                            }
                                                        }
                                                    @endphp
                                                    {!! $features_list !!}
                                                </td>
                                                <td>{{ ($item->quantity - intval($item->quantity))*100 ? $item->quantity : intval($item->quantity) }}{{ $item->type->unit }}</td>
                                                <td>{{ number_format($item->price,2, ',', '.') }}</td>
                                                <td>{{ $item->observations }}</td>
                                                <td class="text-right">{{ number_format($item->amountTotal, 2, ',', '.') }}</td>
                                                @php
                                                    $total += $item->amountTotal;
                                                @endphp
                                            </tr>
                                            @php
                                                $cont++;
                                            @endphp
                                        @empty
                                            <tr>
                                                <td colspan="7">No hay datos disponible</td>
                                            </tr>
                                        @endforelse
                                        <tr>
                                            <td class="text-right" colspan="6"><b>TOTAL</b></td>
                                            <td class="text-right"><b style="font-size: 15px">Bs. {{ number_format($total, 2, ',', '') }}</b></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered" style="padding-bottom:5px;">
                    <div class="row">
                        <div class="col-md-6">
                            <h3 class="panel-title">Dinero Adicional Por la Prenda</h3>

                            <table id="dataStyle" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th style="text-align: center; width: 35%">Fecha</th>
                                        <th>Descripción</th>
                                        <th style="text-align: center ; width: 15%">Monto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $cont = 1;
                                        $amountAditional =0;
                                    @endphp
                                    @forelse ($pawn->amountAditional as $item)
                                        <tr>
                                            <td style="text-align: center">
                                                {{ date('d', strtotime($item->created_at)) }} de {{ $months[intval(date('m', strtotime($item->created_at)))] }} de {{ date('Y h:i:s a', strtotime($item->created_at)) }} <br>
                                                {{$item->register->name}}
                                            </td>
                                            <td>
                                                {{$item->description}}
                                            </td>
                                            <td style="text-align: right"> Bs. {{ number_format($item->amountTotal, 2, ',', '.') }}</td>
                                        </tr>
                                        @php
                                            $cont++;
                                            $amountAditional+=$item->amountTotal;
                                        @endphp
                                    @empty
                                        <tr>
                                            <td style="text-align: center" colspan="6">No hay datos disponible</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="panel-heading" style="border-bottom:0;">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h3 class="panel-title">Detalle de intereses mensuales</h3>
                                    </div>
                                    <div class="col-md-4 text-right" style="padding-top: 20px">
                                    </div>
                                </div>
                            </div>
                            <table id="dataStyle" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $cont = 1;
                                    @endphp
                                    @forelse ($pawn->month as $item)
                                        <tr>
                                            <td>
                                                {{ date('d', strtotime($item->start)) }} de {{ $months[intval(date('m', strtotime($item->start)))] }} de {{ date('Y', strtotime($item->start)) }} - 
                                                {{ date('d', strtotime($item->finish)) }} de {{ $months[intval(date('m', strtotime($item->finish)))] }} de {{ date('Y', strtotime($item->finish)) }}
                                            </td>
                                            <td> Bs. {{ number_format($item->interest, 2, ',', '.') }}</td>
                                            <td>
                                                @if ($item->paid ==1)
                                                    <label class="label label-success">PAGADO</label>                            
                                                @else
                                                    <label class="label label-danger">SIN PAGAR</label>                            
                                                @endif
                                            </td>
                                        </tr>
                                        @php
                                            $cont++;
                                        @endphp
                                    @empty
                                        <tr>
                                            <td style="text-align: center" colspan="6">No hay datos disponible</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <div class="panel-heading" style="border-bottom:0;">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h3 class="panel-title">Detalles de transacciones</h3>
                                    </div>
                                    <div class="col-md-4 text-right" style="padding-top: 20px">
                                    </div>
                                </div>
                            </div>                        
                            <table id="dataStyle" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th style="text-align: center; width:12%">N&deg; Trans.</th>
                                        <th style="text-align: center">Monto</th>
                                        <th style="text-align: center">Fecha</th>
                                        <th style="text-align: center">Atendido Por</th>
                                        <th style="text-align: right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($transaction as $item)
                                        <tr>
                                            <td style="text-align: center">{{$item->transaction_id}}</td>
                                            <td style="text-align: center">
                                                @if ($item->deleted_at)
                                                    <del>BS. {{number_format($item->amount, 2, ',', '.')}} <br></del>
                                                    <label class="label label-danger">Anulado</label>
                                                @else
                                                Bs. {{number_format($item->amount, 2, ',', '.')}} <br>
                                                <label class="label label-success">Pagado Por {{$item->transaction->type}}</label>
                                                @endif
                                            </td>
                                            <td style="text-align: center">
                                                {{date('d/m/Y h:i:s a', strtotime($item->transaction->created_at))}}<br><small>{{\Carbon\Carbon::parse($item->transaction->created_at)->diffForHumans()}}
                                            </td>
                                            <td style="text-align: center">{{$item->agent->name}}</td>
                                            <td class="no-sort no-click bread-actions text-right">
                                                @if(!$item->deleted_at)
                                                    <a onclick="printTransaction({{$pawn->id}}, {{$item->transaction_id}})" title="Imprimir"  class="btn btn-danger">
                                                        <i class="glyphicon glyphicon-print"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td style="text-align: center" colspan="5">No hay datos registrados</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        @if (($pawn->status == 'entregado' || $pawn->status == 'expiro') && $pawn->deleted_at == null && $pawn->inventory == 0)
            <div id="floating-payment-button" style="position: fixed; bottom: 50px; right: 25px; z-index: 1000;">
                <a href="#" data-toggle="modal" data-target="#success-modal" title="Pagar" class="btn btn-success" style="border-radius: 50%; padding: 15px 20px;">
                    <i class="fas fa-credit-card"></i>
                </a>
            </div>
            
        @endif
        
    </div>

    <div id="popup-button">
        <div class="col-md-12" style="padding-top: 5px">
            <h4 class="text-muted">Desea imprimir el comprobante?</h4>
        </div>
        <div class="col-md-12 text-right">
            <button onclick="javascript:$('#popup-button').fadeOut('fast')" class="btn btn-default">Cerrar</button>
            <a id="btn-print" onclick="printTransactionEmerg()" title="Imprimir" class="btn btn-danger">Imprimir <i
                    class="glyphicon glyphicon-print"></i></a>
            {{-- <button type="submit" id="btn-print" title="Imprimir" class="btn btn-danger" onclick="printTransaction()" class="btn btn-primary">Imprimir <i class="glyphicon glyphicon-print"></i></button> --}}

        </div>
    </div>

    

    <form class="form-submit" action="{{ route('pawn.payment') }}" method="POST">
        {{ csrf_field() }}
        <input type="hidden" name="pawn" value="{{$pawn->id}}">
        <div class="modal modal-success fade" data-backdrop="static" tabindex="-1" id="success-modal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><i class="fa-solid fa-money-check-dollar"></i> Pagar</h4>
                    </div>
                    <div class="modal-footer">
                        <div class="row">
                            <div class="col-md-12">      
                                <table id="dataStyle" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th class="text-right"></th>
                                            <th>Detalle</th>
                                            <th style="width: 25%" class="text-center">Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $cont = 1;
                                            $total = 0;
                                        @endphp
                                    
                                        @foreach ($pawn->month->where('paid', 0) as $item)
                                            <tr>
                                                <td>
                                                    <input 
                                                        type="checkbox" 
                                                        class="payment-checkbox" 
                                                        data-index="{{ $cont }}" 
                                                        name="months[]"
                                                        value="{{$item->id}}"
                                                        id="checked-{{ $cont }}"
                                                    >
                                                </td>
                                                <td>
                                                    <label for="checked-{{ $cont }}">
                                                        {{ date('d', strtotime($item->start)) }} de {{ $months[intval(date('m', strtotime($item->start)))] }} de {{ date('Y', strtotime($item->start)) }} - 
                                                        {{ date('d', strtotime($item->finish)) }} de {{ $months[intval(date('m', strtotime($item->finish)))] }} de {{ date('Y', strtotime($item->finish)) }}
                                                    </label>
                                                    <i class="fa-solid fa-calendar-days"></i>
                                                </td>
                                                <td>
                                                    <input 
                                                        type="number" 
                                                        class="form-control input-subtotal" 
                                                        name="interest[{{ $item->id }}]" 
                                                        value="{{ number_format($item->interest, 2, '.', '') }}" 
                                                        step="0.01"
                                                        min="0"
                                                        max="{{ number_format($item->interest, 2, '.', '') }}"
                                                        style="text-align: right"
                                                        id="interest-{{ $cont }}"
                                                    >
                                                </td>                                                
                                            </tr>
                                            @php
                                                $cont++;
                                                $total += $item->interest;

                                            @endphp
                                        @endforeach
                                    
                                        <tr>
                                            <td>
                                                <input 
                                                    type="checkbox" 
                                                    class="payment-checkbox" 
                                                    data-index="{{ $cont }}" 
                                                    name="pawn_id"
                                                    value="{{$pawn->id}}"
                                                    id="checked-{{ $cont }}"  
                                                    @if ($pawn->status == 'expiro')
                                                        disabled
                                                    @endif
                                                >
                                            </td>
                                            <td>
                                                <label for="checked-{{ $cont }}">
                                                    Amortización (Opcional) o Recojo de Prenda (Bs. {{number_format($pawn->amountTotal+$amountAditional-$amortization,2, ',','')}})
                                                </label>
                                                <i class="fa-solid fa-cart-shopping"></i>
                                            </td>
                                            <td>
                                                <input 
                                                    type="number" 
                                                    class="form-control input-subtotal" 
                                                    name="amountPawn" 
                                                    {{-- name="amountLoan"  --}}
                                                    value="{{$pawn->amountTotal + $amountAditional - $amortization }}" 
                                                    step="0.01"
                                                    min="0"
                                                    max="{{$pawn->amountTotal + $amountAditional - $amortization}}"
                                                    style="text-align: right"         
                                                    {{-- readonly                                            --}}
                                                    id="amountLoan"
                                                >
                                            </td>                                                
                                        </tr>
                                        @php
                                            // $total += $amountLoan;
                                            $total += $pawn->amountTotal + $amountAditional - $amortization
                                        @endphp
                                        <tr>
                                            <td colspan="2" class="no-sort no-click bread-actions text-right">
                                                <label for="" style="font-size: 18px"><small style="color: red">Total Deuda</small></label>
                                            </th>
                                            <td>
                                                <label style="font-size: 18px">
                                                    <small>
                                                        Bs. {{ number_format($total, 2, '.', '') }}
                                                    </small>                                                    
                                                </label>
                                            </td>                                                
                                        </tr>

                                        <tr>
                                            <td colspan="3"></td>
                                        </tr>

                                        <tr>
                                            <td colspan="2" class="no-sort no-click bread-actions text-right">
                                                <label for="" style="font-size: 18px"><small>Total a Pagar</small></label>
                                            </th>
                                            <td>
                                                <label style="font-size: 18px" >
                                                    <small id="totalPayment">Bs. 0.00</small>
                                                </label>
                                            </td>                                                
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="form-group col-md-12">
                                <small>Método de pago</small>
                                <select name="payment_type" id="select-payment_type" class="form-control" required>
                                    <option value="" disabled selected>Seleccionar método de pago</option>
                                    <option value="Efectivo">Efectivo</option>
                                    <option value="Qr">Qr/Transferencia</option>
                                </select>
                            </div>

                            <div class="col-md-12">

                                <label class="checkbox-inline">
                                    <input type="checkbox" value="1" required><b><small>Confirmar Pago..!</small></b>
                                </label>
                            </div>
                            
                        </div>
                            
                        <input type="submit" id="btn-pagar" class="btn btn-success pull-right" value="Sí, pagar">
                        <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </form>


    <form action="{{ route('pawn-inventory.store', ['id' => $pawn->id]) }}" id="inventory_form" method="POST">
        {{ csrf_field() }}
        <div class="modal fade modal-success" id="inventory-modal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="inventoryModalLabel">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title" id="inventoryModalLabel">
                            <i class="fa-solid fa-shop"></i> Enviar a Inventario
                        </h4>
                    </div>
    
                    <div class="modal-footer">
                        <div class="alert alert-warning" style="text-align: left">
                            <strong>Advertencia:</strong>
                            <p>La prenda, una vez enviada a inventario, no se podrá revertir.</p>
                        </div>

                        <div class="text-center" style="text-transform:uppercase">
                            <i class="fa-solid fa-shop text-success"  style="font-size: 5em;"></i>
                            <br>
                            <p><b>Desea enviar a inventario?</b></p>
                        </div>

                        <div class="form-group">
                            <table id="dataStyle" class="table table-bordered table-hover">
                                <thead id>
                                    <th>Tipo</th>
                                    <th>Características</th>
                                    <th>Cantidad</th>
                                    <th style="width: 15%">Precio unitario</th>
                                    <th style="width: 15%">Precio de Venta</th>
                                </thead>
                                <tbody>
                                    @foreach ($pawn->details as $item)
                                        <tr>
                                            <td style="text-align: left">
                                                {{ $item->type->name }} <br>
                                                <small>{{ $item->type->category->name }}</small>
                                            </td>
                                            <td style="text-align: left">
                                                @php
                                                    $features_list = '';
                                                    foreach ($item->features_list as $feature) {
                                                        if ($feature->value) {
                                                            $features_list .= '<span><b>'.$feature->title.'</b>: '.$feature->value.'</span><br>';
                                                        }
                                                    }
                                                @endphp
                                                {!! $features_list !!}
                                            </td>
                                            <td>
                                                {{ $item->quantity }} {{ $item->type->unit }}
                                                <input type="hidden"
                                                    onkeyup="getSubtotal({{$item->id}})" onchange="getSubtotal({{$item->id}})"
                                                    id="input-quantity-{{$item->id}}" style="text-align: right" class="form-control text"
                                                    value="{{$item->quantity}}"
                                                >
                                            </td>
                                            <td>
                                                <input type="number"    
                                                    onkeyup="getSubtotal({{$item->id}})" onchange="getSubtotal({{$item->id}})"
                                                    class="form-control text" value="{{$item->price}}" 
                                                    id="input-price-{{$item->id}}"
                                                    name="price[{{ $item->id }}]" 
                                                    step="0.01"
                                                    min="0"
                                                    style="text-align: right"
                                                    required
                                                >
                                            </td>
                                            <td>
                                                <h4 class="label-subtotal" id="label-subtotal-{{$item->id}}">{{ number_format($item->amountTotal, 2, ',', '.') }}</h4>
                                                <input type="hidden" name="total[]" id="input-subtotal-{{$item->id}}" value="{{$item->amountTotal}}">
                                            </td>

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="form-group">
                            <textarea name="description" class="form-control" rows="4" placeholder="Describa el motivo de envio de estas prenda al inventario..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <input type="submit" class="btn btn-success" value="Sí, enviar">
                    </div>
    
                </div>
            </div>
        </div>
    </form>

    <form action="{{ route('pawn.amountAditional', ['id' => $pawn->id]) }}" id="moneyAditional_form" method="POST">
        {{ csrf_field() }}
        <div class="modal fade modal-success" id="moneyAditional-modal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="inventoryModalLabel">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="inventoryModalLabel">
                            <i class="fa-solid fa-plus"></i> Aumentar Monto
                        </h4>
                    </div>
    
                    <div class="modal-footer">
                        <div class="alert alert-warning" style="text-align: left">
                            <strong>Información:</strong>
                            <p>Al registar un monto adicional, el sistema actualizara los intereses cuando se genere un nuevo interes en el mes correspondiente.</p>
                        </div>

                        <div class="form-group">
                            <small>Monto</small>
                            <input type="number" step="0.01" min="1" name="amountTotal" style="text-align: right" class="form-control" placeholder="0" required>
                        </div>

                        <div class="form-group">
                            <textarea name="description" class="form-control" rows="4" placeholder="Describa el motivo de aumento de dinero por el prestamo..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <input type="submit" class="btn btn-success" value="Sí, enviar">
                    </div>
    
                </div>
            </div>
        </div>
    </form>
    
    
@stop

@section('css')
    <style>

        .form-group {
            margin-bottom: 10px !important;
        }

        .label-description {
            cursor: pointer;
        }
        #popup-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 400px;
            height: 100px;
            background-color: white;
            box-shadow: 5px 5px 15px grey;
            z-index: 1000;

            /* Mostrar/ocultar popup */
            /* @if (session('pawn_id'))
            */ animation: show-animation 1s;
            /* @else
            */ right: -500px;
            /* @endif
            */
        }

        @keyframes show-animation {
            0% {
                right: -500px;
            }

            100% {
                right: 20px;
            }
        }
    </style>
@stop

@section('javascript')
    <script>
   
        let pawn_id1 = 0;
        let transaction_id1 = 0;
        $(document).ready(function() {
            // alert('si')
            @if (session('pawn_id'))
                pawn_id1 = "{{ session('pawn_id') }}";

                transaction_id1 = "{{ session('transaction_id') }}";

            @endif

            // Ocultar popup de impresión
            setTimeout(() => {
                $('#popup-button').fadeOut('fast');
            }, 8000);
        });

        function printTransaction(pawn_id, transaction_id)
        {
            window.open("{{ url('admin/pawn/transaction/print') }}/"+pawn_id+"/"+transaction_id, "Recibo", `width=600, height=700`)
        }
        function printTransactionEmerg()
        {
            window.open("{{ url('admin/pawn/transaction/print') }}/"+pawn_id1+"/"+transaction_id1, "Recibo", `width=600, height=700`)
        }

        
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('.form-submit');
            const btnPagar = document.getElementById('btn-pagar');
            form.addEventListener('submit', function () {
                btnPagar.disabled = true;
                btnPagar.value = 'Procesando...';
            });
        });

    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkboxes = document.querySelectorAll('.payment-checkbox');
            const inputs = document.querySelectorAll('.input-subtotal');

            function updateTotal() {
                let total = 0;

                // Sumar los intereses seleccionados
                checkboxes.forEach((checkbox, index) => {
                    if (checkbox.checked && checkbox.name === 'months[]') {
                        const input = document.getElementById(`interest-${index + 1}`);
                        if (input) {
                            total += parseFloat(input.value) || 0;
                        }
                    }
                });

                // Sumar el recojo de prenda si está seleccionado
                const amountLoanInput = document.getElementById('amountLoan');
                const pawnCheckbox = document.getElementById(`checked-${checkboxes.length}`);
                if (amountLoanInput && pawnCheckbox && pawnCheckbox.checked) {
                    total += parseFloat(amountLoanInput.value) || 0;
                }

                // Actualizar el total a pagar
                document.getElementById('totalPayment').innerText = 'Bs. ' + total.toFixed(2);
            }

            // Escuchar cambios en los checkboxes
            checkboxes.forEach((checkbox) => {
                checkbox.addEventListener('change', function () {
                    const currentIndex = parseInt(this.getAttribute('data-index'));    
                    if (this.checked) {
                        // Marcar todos los checkboxes anteriores
                        checkboxes.forEach((cb) => {
                            const index = parseInt(cb.getAttribute('data-index'));
                            if (index <= currentIndex) {
                                cb.checked = true;
                            }
                        });
                    } else {
                        // Desmarcar todos los checkboxes posteriores
                        checkboxes.forEach((cb) => {
                            const index = parseInt(cb.getAttribute('data-index'));
                            if (index > currentIndex) {
                                cb.checked = false;
                            }
                        });
                    }
                    updateTotal();
                });
            });

            // Escuchar cambios en los inputs de montos
            inputs.forEach((input) => {
                input.addEventListener('input', updateTotal); // Usamos 'input' en lugar de 'change'
            });
        });

    
        function getSubtotal(id){
            let price = $(`#input-price-${id}`).val() ? parseFloat($(`#input-price-${id}`).val()) : 0;
            let quantity = $(`#input-quantity-${id}`).val() ? parseFloat($(`#input-quantity-${id}`).val()) : 0;
            $(`#label-subtotal-${id}`).text((price * quantity).toFixed(2));
            $(`#input-subtotal-${id}`).val((price * quantity).toFixed(2));
        }
    </script>
    
@stop
