@extends('voyager::master')

@section('page_title', 'Cierre de caja')

@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0px">
                        <div class="col-md-8" style="padding: 0px">
                            <h1 class="page-title">
                                <i class="voyager-lock"></i> Cierre de caja
                            </h1>
                        </div>
                        <div class="col-md-4" style="margin-top: 30px">
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="page-content edit-add container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="row">
                        @php
                            $cashier_in = $cashier->movements->where('type', 'ingreso')->where('deleted_at', NULL)->where('status', 'Aceptado')->sum('amount');

                            $cashierOut = $cashier->movements->where('type', 'egreso')->where('deleted_at', NULL)->where('transferCashier_id', null)->where('status', '!=', 'Rechazado')->sum('amount');
                            $cashierOutTransfer = $cashier->movements->where('type', 'egreso')->where('deleted_at', NULL)->where('transferCashier_id', '!=', null)->where('status', '!=', 'Rechazado')->sum('amount');

                            //::::::::::::ingreso:::::::::::
                            $loan_payments = $cashier->loan_payments->where('deleted_at', NULL)->where('transaction.type', 'Efectivo')->sum('amount');
                            $loan_paymentsQr = $cashier->loan_payments->where('deleted_at', NULL)->where('transaction.type', 'Qr')->sum('amount');

                            $pawnPayments = $cashier->pawnPayment->where('deleted_at', NULL)->where('transaction.type', 'Efectivo')->sum('amount');
                            $pawnPaymentsQr = $cashier->pawnPayment->where('deleted_at', NULL)->where('transaction.type', 'Qr')->sum('amount');

                            $salaryPurchasePayments = $cashier->salaryPurchasePayment->where('deleted_at', NULL)->where('transaction.type', 'Efectivo')->sum('amount');
                            $salaryPurchasePaymentsQr = $cashier->salaryPurchasePayment->where('deleted_at', NULL)->where('transaction.type', 'Qr')->sum('amount');

                            $salePayments = $cashier->salePayment->where('deleted_at', NULL)->where('transaction.type', 'Efectivo')->sum('amount');
                            $salePaymentsQr = $cashier->salePayment->where('deleted_at', NULL)->where('transaction.type', 'QR')->sum('amount');
                            //::::::::::::::::::::::::::::::
                            
                            $loans = $cashier->loans->where('deleted_at', NULL)->sum('amountLoan');
                            $pawns = $cashier->pawn->where('deleted_at', NULL)->sum('amountTotal');
                            $salaryPurchase = $cashier->salaryPurchase->where('deleted_at', NULL)->sum('amount');

                            // Para los aumento adicionales que solictan los clientes por sus prenda
                            $pawnsMoneyAditional = $cashier->pawnMoneyAditional->where('deleted_at', NULL)->sum('amountTotal');


                            $amount_cashier = ($cashier_in + $loan_payments + $pawnPayments + $salePayments + $salaryPurchasePayments) - $cashierOut - $cashierOutTransfer  - $loans -$pawns - $pawnsMoneyAditional - $salaryPurchase;

                        @endphp
                        <div class="col-md-6" style="height: 650px; overflow-y: auto">
                            <form name="form_close" id="form-close" action="{{ route('cashiers.close.store', ['cashier' => $cashier->id]) }}" method="post">
                                @csrf
                                <input type="hidden" name="amount_cashier" value="{{ $amount_cashier }}">
                                <input type="hidden" name="amount_real">
                                <table id="dataStyle" class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Corte</th>
                                            <th>Cantidad</th>
                                            <th style="width: 200px">Sub Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="lista_cortes"></tbody>
                                </table>

                                {{-- confirm modal --}}
                                <div class="modal modal-danger fade" tabindex="-1" id="close_modal" role="dialog">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                                                <h4 class="modal-title"><i class="voyager-lock"></i> Confirme que desea cerrar la caja?</h4>
                                            </div>
                                            <div class="modal-body">
                                                <p>Esta acción cerrará la caja y no podrá realizar modificaciones posteriores</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-danger">Sí, cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-6 div-details" style="padding-top: 10px">
                            <div class="row">
                                <div class="col-md-6">
                                    <p style="margin-top: 20px">Dinero Asignado a caja por el Administrador</p>
                                </div>
                                <div class="col-md-6">
                                    <h3 class="text-right" style="padding-right: 20px">{{ number_format($cashier_in, 2, ',', '.') }}</h3>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <p style="margin-top: 20px">Ingresos por cobros en efectivo</p>
                                </div>
                                <div class="col-md-6">
                                    <h3 class="text-right" style="padding-right: 20px">{{ number_format($loan_payments + $pawnPayments + $salePayments + $salaryPurchasePayments, 2, ',', '.') }}</h3>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <p style="margin-top: 20px">Ingresos por cobros en Qr</p>
                                </div>
                                <div class="col-md-6">
                                    <h3 class="text-right" style="padding-right: 20px">{{ number_format($loan_paymentsQr + $pawnPaymentsQr + $salePaymentsQr + $salaryPurchasePaymentsQr, 2, ',', '.') }}</h3>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <p style="margin-top: 20px">Gastos</p>
                                </div>
                                <div class="col-md-6">
                                    <h3 class="text-right" style="padding-right: 20px">{{ number_format($cashierOut, 2, ',', '.') }}</h3>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <p style="margin-top: 20px">Transferencias a otras Cajas</p>
                                </div>
                                <div class="col-md-6">
                                    <h3 class="text-right" style="padding-right: 20px">{{ number_format($cashierOutTransfer, 2, ',', '.') }}</h3>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <p style="margin-top: 20px">Prestamo entregados</p>
                                </div>
                                <div class="col-md-6">
                                    <h3 class="text-right" style="padding-right: 20px">{{ number_format($loans + $pawns + $pawnsMoneyAditional + $salaryPurchase, 2, ',', '.') }}</h3>
                                </div>
                            </div>
                         
                            <div class="row">
                                <div class="col-md-6">
                                    <p style="margin-top: 20px">Pagos de sueldos</p>
                                </div>
                                <div class="col-md-6">
                                    <h3 class="text-right" style="padding-right: 20px">{{ number_format(0, 2, ',', '.') }}</h3>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <p style="margin-top: 20px">Total</p>
                                </div>
                                <div class="col-md-6">
                                    <h3 class="text-right" style="padding-right: 20px">{{ number_format($amount_cashier, 2, ',', '.') }}</h3>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <p style="margin-top: 20px">Monto en efectivo</p>
                                </div>
                                <div class="col-md-6">
                                    <div class="panel-heading" style="border-bottom:0;">
                                        <h3 class="text-right" style="padding-right: 20px" id="label-total">0,00</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <p style="margin-top: 20px">Monto sobrante</p>
                                </div>
                                <div class="col-md-6">
                                    <div class="panel-heading" style="border-bottom:0;">
                                        <h3 class="text-right" style="padding-right: 20px" id="label-plus_amount">0,00</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <p style="margin-top: 20px">Monto faltante</p>
                                </div>
                                <div class="col-md-6">
                                    <div class="panel-heading" style="border-bottom:0;">
                                        <h3 class="text-right" style="padding-right: 20px" id="label-missing_amount">0,00</h3>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-danger btn-block btn-confirm" data-toggle="modal" data-target="#close_modal">Cerrar caja <i class="voyager-lock"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .div-details .col-md-6{
            margin-bottom: 0px
        }
    </style>
@stop

@section('javascript')
    <script>
        const APP_URL = '{{ url('') }}';
    </script>
    <script src="{{ asset('js/cash_value.js') }}"></script>
    <script>
        $(document).ready(function() {
            window.addEventListener("keypress", function(event){
                if (event.keyCode == 13){
                    event.preventDefault();
                }
            }, false);


            
            $('.input-corte').keyup(function(){
                getMissingAmount()
            });
            $('.input-corte').change(function(){
                getMissingAmount()
            });
        });

        function getMissingAmount(){
            let total = parseFloat("{{ $amount_cashier }}");
            let total_cashier = parseFloat($('#label-total').text());
            let missing_amount = total - total_cashier;
            let plus_amount = total_cashier - total;

            $('#form-close input[name="amount_real"]').val(total_cashier);

            $('#label-missing_amount').text(missing_amount > 0 ? missing_amount.toFixed(2) : 0);
            $('#label-plus_amount').text(plus_amount > 0 ? plus_amount.toFixed(2) : 0);
            if(missing_amount > 0){
                $('#label-missing_amount').addClass('text-danger');
            }else{
                $('#label-missing_amount').removeClass('text-danger');                
            }
            // if(total == total_cashier){
            //     $('.btn-confirm').removeAttr('disabled');
            // }else{
            //     $('.btn-confirm').attr('disabled', 'disabled');
            // }
            plus_amount > 0 ? $('#label-plus_amount').addClass('text-success') : $('#label-plus_amount').removeClass('text-success');
        }
    </script>
@stop
