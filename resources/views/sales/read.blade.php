@extends('voyager::master')

@section('page_title', 'Ver Ventas')

@section('page_header')
    <h1 class="page-title">
        <i class="fa fa-handshake"></i> Viendo Ventas &nbsp;
        <a href="{{ route('sales.index') }}" class="btn btn-warning">
            <span class="glyphicon glyphicon-list"></span>&nbsp;
            Volver a la lista
        </a> 
        <a href="{{ route('sales.prinf', ['id' => $sale->id]) }}" title="Imprimir" target="_blank" class="btn btn-sm btn-primary">
            <i class="fa-solid fa-print"></i> Imprimir Factura
        </a>
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
                        <div class="col-md-6">
                            <div class="panel-heading" style="border-bottom:0;">
                                <h3 class="panel-title">Código</h3>
                            </div>
                            <div class="panel-body" style="padding-top:0;">
                                <p>{{ $sale->code }}</p>
                            </div>
                            <hr style="margin:0;">
                        </div>
                        <div class="col-md-6">
                            <div class="panel-heading" style="border-bottom:0;">
                                <h3 class="panel-title">Cliente</h3>
                            </div>
                            <div class="panel-body" style="padding-top:0;">
                                @if ($sale->person_id)
                                    <p>{{ $sale->person->first_name }} {{ $sale->person->last_name1 }} {{ $sale->person->last_name2 }}</p>
                                @else
                                    'S/N'
                                @endif
                            </div>
                            <hr style="margin:0;">
                        </div>
                        <div class="col-md-3">
                            <div class="panel-heading" style="border-bottom:0;">
                                <h3 class="panel-title">Tipo de Venta</h3>
                            </div>
                            <div class="panel-body" style="padding-top:0;">
                                <p>
                                    {{$sale->typeSale=='credito'?'Venta al Credito':'Venta al Contado'}}
                                </p>
                            </div>
                            <hr style="margin:0;">
                        </div>
                        <div class="col-md-3">
                            <div class="panel-heading" style="border-bottom:0;">
                                <h3 class="panel-title">Fecha de Venta</h3>
                            </div>
                            <div class="panel-body" style="padding-top:0;">
                                <p>{{ date('d', strtotime($sale->saleDate)) }} de {{ $months[intval(date('m', strtotime($sale->saleDate)))] }} de {{ date('Y H:i', strtotime($sale->saleDate)) }}</p>
                            </div>
                            <hr style="margin:0;">
                        </div>
                        @if ($sale->typeSale == 'credito')
                            <div class="col-md-3">
                                <div class="panel-heading" style="border-bottom:0;">
                                    <h3 class="panel-title">Próximo pago</h3>
                                </div>
                                <div class="panel-body" style="padding-top:0;">
                                    @if ($sale->debt > 0)
                                        <p>{{ date('d', strtotime($sale->datePayment)) }} de {{ $months[intval(date('m', strtotime($sale->datePayment)))] }} de {{ date('Y', strtotime($sale->datePayment)) }}</p>
                                    @else
                                        <small style="font-size: 13px">Pagado</small>
                                    @endif

                                </div>
                                <hr style="margin:0;">
                            </div>
                        @endif
                        <div class=" {{$sale->typeSale == 'credito'? 'col-md-3': 'col-md-6'}}">
                            <div class="panel-heading" style="border-bottom:0;">
                                <h3 class="panel-title">Vendido Por</h3>
                            </div>
                            <div class="panel-body" style="padding-top:0;">
                                <p>
                                    {{$sale->register->name}} - {{$sale->registerRole}}
                                </p>
                            </div>
                            <hr style="margin:0;">
                        </div>
                        <div class="col-md-3">
                            <div class="panel-heading" style="border-bottom:0;">
                                <h3 class="panel-title">Monto Total</h3>
                            </div>
                            <div class="panel-body" style="padding-top:0;">
                                <small> Bs. {{ number_format($sale->amount, 2, '.', '') }}</small>
                            </div>
                            <hr style="margin:0;">
                        </div> 

                        <div class="col-md-3">
                            <div class="panel-heading" style="border-bottom:0;">
                                <h3 class="panel-title">Monto de Descuento</h3>
                            </div>
                            <div class="panel-body" style="padding-top:0;">
                                <small> Bs. {{ number_format($sale->discount, 2, '.', '') }}</small>
                            </div>
                            <hr style="margin:0;">
                        </div>
                        <div class="col-md-3">
                            <div class="panel-heading" style="border-bottom:0;">
                                <h3 class="panel-title">Total a Pagar (Bs)</h3>
                            </div>
                            <div class="panel-body" style="padding-top:0;">
                                <small> Bs. {{ number_format($sale->amountTotal, 2, '.', '') }}</small>
                            </div>
                            <hr style="margin:0;">
                        </div>
                        <div class="col-md-3">
                            <div class="panel-heading" style="border-bottom:0;">
                                <h3 class="panel-title">Total a Pagar ($)</h3>
                            </div>
                            <div class="panel-body" style="padding-top:0;">
                                <small> $ {{ number_format($sale->dollarTotal, 2, '.', '') }}</small>
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
                                            {{-- <th>Observaciones</th> --}}
                                            <th class="text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $cont = 1;
                                            $total = 0;
                                        @endphp
                                        @forelse ($sale->saleDetails as $detail)
                                            <tr>
                                                <td>{{ $cont }}</td>
                                                <td>
                                                    {{ $detail->inventory->item->name }} <br>
                                                    <small>{{ $detail->inventory->item->category->name }}</small>
                                                </td>
                                                <td>
                                                    @php
                                                        $features_list = '';
                                                        foreach ($detail->inventory->features as $feature) {
                                                            if ($feature->value) {
                                                                $features_list .= '<span><b>'.$feature->title.'</b>: '.$feature->value.'</span><br>';
                                                            }
                                                        }
                                                    @endphp
                                                    {!! $features_list !!}
                                                </td>
                                                <td>{{ ($detail->quantity - intval($detail->quantity))*100 ? $detail->quantity : intval($detail->quantity) }}{{ $detail->inventory->item->unit }}</td>
                                                <td>{{ $detail->price }}</td>
                                                <td class="text-right">{{ number_format($detail->amountTotal, 2, ',', '.') }}</td>
                                                @php
                                                    $total += $detail->amountTotal;
                                                @endphp
                                            </tr>
                                            @php
                                                $cont++;
                                            @endphp
                                        @empty
                                            <tr>
                                                <td colspan="6">No hay datos disponible</td>
                                            </tr>
                                        @endforelse
                                        <tr>
                                            <td class="text-right" colspan="5"><b>TOTAL</b></td>
                                            <td class="text-right"><b style="font-size: 15px">{{ number_format($total, 2, ',', '') }}</b></td>
                                        </tr>
                                        <tr>
                                            <td class="text-right" colspan="5"><b>TOTAL DESCUENTO</b></td>
                                            <td class="text-right"><b style="font-size: 15px">{{ number_format($sale->discount, 2, ',', '') }}</b></td>
                                        </tr>
                                        <tr>
                                            <td class="text-right" colspan="5"><b>
                                                <small>TOTAL A PAGAR POR LA VENTA</small></b>
                                            </td>
                                            <td class="text-right"><b style="font-size: 18px">
                                                <small style="color: #198754">{{ number_format($sale->amountTotal, 2, ',', '') }}</small></b>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-right" colspan="5"><b>
                                                <small>MONTO PAGADO</small></b>
                                            </td>
                                            <td class="text-right"><b style="font-size: 18px">
                                                <small style="color: #0d6efd">{{ number_format($sale->amountTotal-$sale->debt, 2, ',', '') }}</small></b>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-right" colspan="5"><b>
                                                <small>MONTO PENDIENTE</small></b>
                                            </td>
                                            <td class="text-right"><b style="font-size: 18px">
                                                <small style="color: red">{{ number_format($sale->debt, 2, ',', '') }}</small></b>
                                            </td>
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
                        <div class="col-md-6"></div>
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
                                    @forelse ($sale->saleAgents as $item)
                                        <tr>
                                            <td style="text-align: center">{{$item->transaction->transaction}}</td>
                                            <td style="text-align: center">
                                                @if ($item->deleted_at)
                                                    <del>BS. {{$item->amount}} <br></del>
                                                    <label class="label label-danger">Anulado</label>
                                                @else
                                                Bs. {{$item->amount}} <br>
                                                <label class="label label-success">Pagado Por {{$item->transaction->type}}</label>
                                                @endif
                                            </td>
                                            <td style="text-align: center">
                                                {{date('d/m/Y H:i:s', strtotime($item->transaction->created_at))}}<br><small>{{\Carbon\Carbon::parse($item->transaction->created_at)->diffForHumans()}}
                                            </td>
                                            <td style="text-align: center">{{$item->register->name}} <br> {{$item->agentType}}</td>
                                            <td class="no-sort no-click bread-actions text-right">
                                                @if(!$item->deleted_at)
                                                    <a onclick="printTransaction({{$sale->id}}, {{$item->transaction->id}})" title="Imprimir"  class="btn btn-danger">
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
        @if ($sale->debt != 0)
            <div id="floating-payment-button" style="position: fixed; bottom: 50px; right: 25px; z-index: 1000;">
                <a href="" data-toggle="modal" data-target="#success-modal" title="Pagar" class="btn btn-success" style="border-radius: 50%; padding: 15px 20px;">
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
            <a id="btn-print" onclick="printTransaction(null, null)" title="Imprimir" class="btn btn-danger">Imprimir <i
                    class="glyphicon glyphicon-print"></i></a>
        </div>
    </div>


    <form class="form-submit" action="{{ route('sales.payment', ['id'=>$sale->id]) }}" method="POST">
        {{ csrf_field() }}
        <div class="modal modal-success fade" data-backdrop="static" tabindex="-1" id="success-modal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="fa-solid fa-money-check-dollar"></i> Pagar</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-md-12 mb-3">
                                <label for="total-to-pay"><strong>Total a Pagar:</strong></label>
                                <input type="text" class="form-control" id="total-to-pay" style="text-align: right" value="{{number_format($sale->amountTotal, 2, ',', '')}}" disabled>
                            </div>
                            <div class="form-group col-md-12 mb-3">
                                <label for="total-received"><strong>Total Recibido:</strong></label>
                                <input type="text" class="form-control" id="total-received" style="text-align: right" value="{{number_format($sale->amountTotal-$sale->debt, 2, ',', '')}}" disabled>
                            </div>
    
                            <div class="form-group col-md-12 mb-3">
                                <label for="total-debt"><strong>Deuda Total:</strong></label>
                                <input type="text" class="form-control" id="total-debt" style="text-align: right" value="{{number_format($sale->debt, 2, ',', '')}}" disabled>
                            </div>

                            <div class="form-group col-md-12 mb-3">
                                <label for="payment_amount"><strong><small>Monto a Pagar:</small></strong></label>
                                <input type="number" class="form-control" style="text-align: right" name="payment_amount" value="{{$sale->debt}}" id="payment_amount" step="0.01" max="{{$sale->debt}}" placeholder="Ingrese monto" required>
                            </div>
    
                            <div class="form-group col-md-12 mb-3">
                                <label for="next_payment_date"><strong><small>Fecha de Próximo Pago:</small></strong></label>
                                <input type="date" class="form-control" min="<?php echo date('Y-m-d'); ?>" name="next_payment_date">
                                {{-- <input type="text" class="form-control datepicker" name="next_payment_date" placeholder="Seleccione fecha"> --}}
                            </div>

                            <div class="form-group col-md-12">
                                <label for="payment_type">Método de pago</label>
                                <select name="payment_type" id="select-payment_type" class="form-control" required>
                                    <option value="" disabled selected>Seleccionar método de pago</option>
                                    <option value="Efectivo">Efectivo</option>
                                    <option value="Qr">Qr/Transferencia</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="checkbox-inline">
                                    <input type="checkbox" value="1" required><b><small>Confirmar Pago..!</small></b>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="submit" class="btn btn-success btn-lg" value="Sí, pagar">
                        <button type="button" class="btn btn-secondary btn-lg" data-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    
    
@stop

@section('css')
    <style>
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
            /* @if (session('sale_id'))
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
   
        let sale_id1 = 0;
        let transaction_id1 = 0;
        $(document).ready(function() {
            @if (session('sale_id'))

                sale_id1 = "{{ session('sale_id') }}";

                transaction_id1 = "{{ session('transaction_id') }}";
            @endif

            // Ocultar popup de impresión
            setTimeout(() => {
                $('#popup-button').fadeOut('fast');
            }, 8000);
        });

        function printTransaction(id, transaction_id)
        {
            id=id?id:sale_id1;
            transaction_id=transaction_id?transaction_id:transaction_id1;
            
            window.open("{{ url('admin/sales/transaction/print') }}/"+id+"/"+transaction_id, "Recibo", `width=600, height=700`)
        }

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
