@extends('voyager::master')

@section('page_title', 'Confimar cierre de caja')

@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0px">
                        <div class="col-md-8" style="padding: 0px">
                            <h1 class="page-title">
                                <i class="voyager-lock"></i> Confimar cierre de caja
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
                        <div class="col-md-6">
                            <form name="form_close" action="{{ route('cashiers.confirm_close.store', ['cashier' => $cashier->id]) }}" method="post">
                                @csrf
                                <table id="dataStyle" class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Corte</th>
                                            <th>Cantidad</th>
                                            <th>Sub Total</th>
                                        </tr>
                                    </thead>
                                    @php
                                        $cash = ['200', '100', '50', '20', '10', '5', '2', '1', '0.5', '0.2', '0.1'];
                                        $missing_amount = 0;
                                    @endphp
                                    <tbody>
                                        @foreach ($cash as $item)
                                        <tr>
                                            <td><h4 style="margin: 0px"><img src=" {{ url('images/cash/'.$item.'.jpg') }} " alt="{{ $item }} Bs." width="70px"> {{ $item }} Bs. </h4></td>
                                            <td>
                                                @php
                                                    $details = $cashier->details->where('cash_value', $item)->first();
                                                @endphp
                                                {{ $details ? $details->quantity : 0 }}
                                            </td>
                                            <td>
                                                {{ $details ? number_format($details->quantity * $item, 2, ',', '.') : 0 }}
                                                <input type="hidden" name="cash_value[]" value="{{ $item }}">
                                                <input type="hidden" name="quantity[]" value="{{ $details ? $details->quantity : 0 }}">
                                            </td>
                                            @php
                                            if($details){
                                                $missing_amount += $details->quantity * $item;
                                            }
                                            @endphp
                                        </tr>
                                        @endforeach
                                    </tbody>
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
                                                <div class="form-group">
                                                    <label for="">Bóveda</label>
                                                    <select name="vault_id" class="form-control select2">
                                                        @foreach (\App\Models\Vault::where('status', 'activa')->get() as $item)
                                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <small>Elija la bóveda en la que se va a guardar el dinero</small>
                                                </div>
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
                        <div class="col-md-6 div-details" style="padding-top: 20px">
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
                                    <p style="margin-top: 20px">Total a enviar bóveda</p>
                                </div>
                                <div class="col-md-6">
                                    <div class="panel-heading" style="border-bottom:0;">
                                        <h3 class="text-right" style="padding-right: 20px">{{ number_format($cashier->amount_real, 2, ',', '.') }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <p style="margin-top: 20px">Saldo</p>
                                </div>
                                <div class="col-md-6">
                                    <h3 class="text-right @if($cashier->balance > 0) text-success @endif @if($cashier->balance < 0) text-danger @endif }}" style="padding-right: 20px">{{ number_format($cashier->balance, 2, ',', '.') }}</h3>
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

        });
    </script>
@stop
