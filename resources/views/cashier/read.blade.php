@extends('voyager::master')

@section('page_title', 'Ver Caja')

@if (auth()->user()->hasPermission('read_cashiers'))
@section('page_header')
    <h1 class="page-title">
        <i class="voyager-dollar"></i> Viendo Caja 
        <a href="{{ route('cashiers.index') }}" class="btn btn-warning">
            <span class="glyphicon glyphicon-list"></span>&nbsp;
            Volver a la lista
        </a>
        @if ($cashier->status == "cierre pendiente")
            <a href="{{route('cashiers.confirm_close',['cashier' => $cashier->id])}}" title="Ver" class="btn btn-sm btn-info">
                <i class="voyager-lock"></i> <span class="hidden-xs hidden-sm">Confirmar Cierre de Caja</span>
            </a>
        @endif
        @if ($cashier->status == "cerrada")
            <a href="{{ route('cashiers.print', $cashier->id) }}" title="Imprimir" target="_blank" class="btn btn-sm btn-danger">
                <i class="fa fa-print"></i> <span class="hidden-xs hidden-sm">Imprimir</span>
            </a>
        @endif
        {{-- <div class="btn-group">
            <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown">
                <span class="glyphicon glyphicon-print"></span> Impresión <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" role="menu">
                <li><a href="{{ route('print.open', ['cashier' => $cashier->id]) }}" target="_blank">Apertura</a></li>
                @if ($cashier->status == 'cerrada')
                <li><a href="{{ route('print.close', ['cashier' => $cashier->id]) }}" target="_blank">Cierre</a></li>
                @endif
            </ul>
        </div> --}}
    </h1>
@stop

@section('content')
    <div class="page-content read container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered" style="padding-bottom:5px;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="panel-heading" style="border-bottom:0;">
                                <h3 class="panel-title">Descripción</h3>
                            </div>
                            <div class="panel-body" style="padding-top:0;">
                                <p>{{ $cashier->title }}</p>
                            </div>
                            <hr style="margin:0;">
                        </div>
                        <div class="col-md-6">
                            <div class="panel-heading" style="border-bottom:0;">
                                <h3 class="panel-title">Cajero</h3>
                            </div>
                            <div class="panel-body" style="padding-top:0;">
                                <p>{{ $cashier->user->name }}</p>
                            </div>
                            <hr style="margin:0;">
                        </div>
                        <div class="col-md-12">
                            <div class="panel-heading" style="border-bottom:0;">
                                <h3 class="panel-title">Observaciones</h3>
                            </div>
                            <div class="panel-body" style="padding-top:0;">
                                <p>{{ $cashier->observations ?? 'Ninguna' }}</p>
                            </div>
                            <hr style="margin:0;">
                        </div>
                    </div>
                </div>

                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <h3 id="h4">Dinero abonado <label class="label label-success">Ingreso</label></h3>
                        <div class="table-responsive">                            
                            <table id="dataStyle" class="table table-bordered table-bordered">
                                <thead>
                                    <tr>
                                        <th>N&deg;</th>
                                        <th style="text-align: center; width:15%">Fecha y Hora de Registro</th>
                                        <th style="text-align: center; width:30%">Registrado Por</th>
                                        <th>Detalle</th>
                                        <th style="text-align: center">Monto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $count = 1;
                                        $cashierInput = 0;
                                    @endphp
                                    @forelse ($cashier->movements->where('type', 'ingreso')->where('deleted_at', null) as $item)
                                        <tr>
                                            <td>
                                                {{ $count }}
                                                @if (auth()->user()->hasRole('admin'))
                                                    <br>
                                                    ID={{$item->id}}
                                                @endif
                                            </td>
                                            <td style="text-align: center">{{ date('d/m/Y h:i:s a', strtotime($item->created_at))}}<br><small>{{\Carbon\Carbon::parse($item->created_at)->diffForHumans() }}</small></td>
                                            <td>{{ $item->user->name }}</td>
                                            <td>{{ $item->description }} <br>
                                                @if ($item->transferCashier_id)
                                                    <label class="label label-success">Trasferencia de Caja</label>
                                                @endif
                                            </td>
                                            <td style="text-align: right">{{ $item->amount }}</td>
                                        </tr>
                                        @php
                                            $cashierInput += $item->amount;
                                            $count++;
                                        @endphp
                                    @empty
                                        <tr>
                                            <td class="text-center" valign="top" colspan="5" class="dataTables_empty">No hay datos disponibles en la tabla</td>
                                        </tr>
                                    @endforelse
                                    <tr>
                                        <td colspan="4" style="text-align: right"><b>TOTAL</b></td>
                                        <td style="text-align: right">{{ number_format($cashierInput, 2, '.', '') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <h3 id="h3" style="text-align: center">Prestamos Diarios</h3>
                        <h3 id="h4">Cobros Realizados <label class="label label-success">Ingreso</label></h3>

                        <div class="table-responsive">
                            <table id="dataStyle" class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>N&deg;</th>
                                        <th style="text-align: center; width:5%">N&deg; Transacción</th>                                                    
                                        <th style="text-align: center">Código</th>
                                        <th style="text-align: center">Fecha Pago</th>
                                        <th style="text-align: center">Cliente</th>
                                        <th style="text-align: center">Monto Prestado</th>
                                        <th style="text-align: center">Monto Prestado + Interes</th>
                                        <th style="text-align: center">Monto Cobrado</th>
                                        <th style="text-align: center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $cont = 1;

                                        $loanPayments = $cashier->loan_payments
                                                        ->groupBy('transaction_id')
                                                        ->map(function ($group) {
                                                            return [
                                                                'id' => $group->first()->id, 
                                                                'code'=> $group->first()->loanDay->loan->code,
                                                                'amountLoan'=>$group->first()->loanDay->loan->amountLoan,
                                                                'amountTotal'=>$group->first()->loanDay->loan->amountTotal,
                                                                'created_at' => $group->first()->transaction->created_at,
                                                                'deleted_at' => $group->first()->transaction->deleted_at,
                                                                'deleteObservation' => $group->first()->deleteObservation,
                                                                'transaction_id'=> $group->first()->transaction->id,
                                                                'transaction_type'=>$group->first()->transaction->type,
                                                                'latitude'=>$group->first()->transaction->latitude,
                                                                'longitude'=>$group->first()->transaction->longitude,
                                                                'precision'=>$group->first()->transaction->DescriptionPrecision,
                                                                'register'=> $group->first()->agent->name, 
                                                                'ci' => $group->first()->loanDay->loan->people->ci, 
                                                                'full_name' => $group->first()->loanDay->loan->people->first_name.' '.$group->first()->loanDay->loan->people->last_name1.' '.$group->first()->loanDay->loan->people->last_name2, // Obtener created_at de la transacción
                                                                'total_amount' => $group->sum('amount')
                                                            ];
                                                        });


                                        $loanPaymentCash = 0;
                                        $loanPaymentQr = 0;
                                        $loanPaymentDelete = 0;
                                    @endphp
                                    @forelse ($loanPayments as $item)
                                        <tr>
                                            <td>
                                                {{ $cont }}
                                                @if (auth()->user()->hasRole('admin'))
                                                    <br>
                                                    ID={{$item['transaction_id']}}
                                                @endif
                                            </td>
                                            <td style="text-align: center">
                                                {{$item['transaction_id']}} <br>
                                                @if ($item['transaction_type'] != 'Efectivo')
                                                    <label class="label label-primary">Qr/Transferencia</label>  
                                                @else
                                                    <label class="label label-success">Efectivo</label> 
                                                @endif
                                                @if ($item['deleted_at'])
                                                    <br>
                                                    <label class="label label-danger">Transaccion eliminada</label>                 
                                                @endif
                                            </td>
                                            <td style="text-align: center">{{$item['code']}} <br>                                                
                                            </td>                                            
                                            <td>
                                                <small>CI:</small> {{$item['ci']?$item['ci']:'No definido'}} <br>
                                                {{$item['full_name']}}
                                            </td>
                                            <td style="text-align: center">
                                                {{$item['register']}} <br>
                                                {{date('d/m/Y h:i:s a', strtotime($item['created_at']))}}<br>
                                                <small>{{\Carbon\Carbon::parse($item['created_at'])->diffForHumans()}}</small> <br>
                                                <i class="fa-solid fa-location-dot"></i> {{$item['precision']}}

                                            </td>
                                            <td style="text-align: right">
                                                {{ number_format($item['amountLoan'], 2, ',', '.') }}
                                            </td>
                                            <td style="text-align: right">
                                                {{ number_format($item['amountTotal'], 2, ',', '.') }}
                                            </td>


                                            <td style="text-align: right">
                                                @if ($item['deleted_at'])
                                                    <del style="color: red">{{ number_format($item['total_amount'], 2, ',', '.') }}</del>
                                                @else
                                                {{ number_format($item['total_amount'], 2, ',', '.') }}
                                                @endif
                                            </td>

                                            <td style="text-align: center">
                                                @if ($item['latitude'] && $item['longitude'])
                                                    <a href="#" class="btn btn-sm btn-primary view-location" 
                                                        data-lat="{{ $item['latitude'] }}" 
                                                        data-lng="{{ $item['longitude'] }}">
                                                        <i class="fa-solid fa-location-dot"></i> <br>
                                                        {{-- {{$item['precision']}} --}}
                                                    </a>
                                                @endif
                                                @if (!$item['deleted_at'])
                                                    <button title="Eliminar transacción" class="btn btn-sm btn-danger delete" onclick="deleteItem('{{ route('cashiers-loan.transaction.delete', ['cashier'=>$cashier->id, 'transaction' => $item['transaction_id']]) }}')" data-toggle="modal" data-target="#modal-delete">
                                                        <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm"></span>
                                                    </button>
                                                @else
                                                    <span class="text-danger">{{ $item['deleteObservation'] }}</span>
                                                @endif
                                            </td>                                                
                                        </tr>
                                        @php
                                            $cont++;
                                            if(!$item['deleted_at']){
                                                if($item['transaction_type'] == 'Efectivo'){
                                                    $loanPaymentCash += $item['total_amount'];
                                                }else{
                                                    $loanPaymentQr += $item['total_amount'];
                                                }
                                            }else{
                                                $loanPaymentDelete += $item['total_amount'];
                                            }
                                        @endphp
                                    @empty
                                        <tr>
                                            <td style="text-align: center" valign="top" colspan="9" class="dataTables_empty">No hay datos disponibles en la tabla</td>
                                        </tr>
                                    @endforelse
                                    <tr>
                                        <td colspan="7" style="text-align: right"><span class="text-danger">TOTAL ANULADO</span></td>
                                        <td style="text-align: right"><b class="text-danger">{{ number_format($loanPaymentDelete, 2, ',', '.') }}</b></td>
                                        <td></td>
                                    </tr>
                                    
                                    <tr>
                                        <td colspan="7" style="text-align: right">TOTAL COBROS</td>
                                        <td style="text-align: right"><b>{{ number_format($loanPaymentCash + $loanPaymentQr, 2, ',', '.') }}</b></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" style="text-align: right">TOTAL QR/TRANSFERENCIA</td>
                                        <td style="text-align: right"><b>{{ number_format($loanPaymentQr, 2, ',', '.') }}</b></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" style="text-align: right">TOTAL EFECTIVO</td>
                                        <td style="text-align: right"><b>{{ number_format($loanPaymentCash, 2, ',', '.') }}</b></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                 
                        <h3 id="h4">Prestamos Entregados <label class="label label-danger">Egreso</label></h3>
                        <div class="table-responsive">                            
                            <table id="dataStyle" class="table table-bordered table-bordered">
                                <thead>
                                    <tr>
                                        <th>N&deg;</th>
                                        <th style="text-align: center">Codigo</th>
                                        <th style="text-align: center">Fecha Solicitud</th>
                                        <th style="text-align: center">Fecha Entrega</th>
                                        <th style="text-align: center">Nombre Completo</th>
                                        <th style="text-align: center">Interes a Cobrar</th>
                                        <th style="text-align: center">Total Prestado</th>
                                        <th style="text-align: center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $cont = 1;
                                        $loanCash = 0;
                                        $loanDelete = 0;
                                    @endphp
                                    @foreach ($cashier->loans as $item)
                                        <tr>
                                            <td style="text-align: center">{{ $cont }}</td>
                                            <td style="text-align: center">
                                                {{ $item->code }}<br>
                                                @if ($item->deleted_at)
                                                    <label class="label label-danger">Transaccion eliminada</label>    
                                                @else
                                                    @if ($item->amountTotal == $item->debt)
                                                        <label class="label label-primary">No cuenta con pagos</label><br>
                                                    @else
                                                        <label class="label label-success">Cuenta con dias pagados</label><br>
                                                    @endif
                                                @endif
                                            </td>
                                            <td style="text-align: center">{{ $item->date}}</td>
                                            <td style="text-align: center">{{ $item->dateDelivered}}</td>
                                            <td>
                                                <small>CI:</small> {{ $item->people->ci}} <br>
                                                <p>{{ $item->people->first_name}} {{ $item->people->last_name1}} {{ $item->people->last_name2}}</p>
                                                
                                            </td>
                                            
                                            <td style="text-align: right">
                                                @if ($item->deleted_at)
                                                   <del>{{ number_format($item->amountPorcentage, 2, ',', '.') }}</del>
                                                @else
                                                   {{ number_format($item->amountPorcentage, 2, ',', '.') }}
                                                @endif
                                            </td>
                                            <td style="text-align: right">
                                                @if ($item->deleted_at)
                                                   <del>{{ number_format($item->amountLoan, 2, ',', '.') }}</del>
                                                @else
                                                   {{ number_format($item->amountLoan, 2, ',', '.') }}
                                                @endif
                                            </td>
                                            <td style="text-align: center">
                                                @if (!$item['deleted_at'])
                                                    <button title="Eliminar transacción" class="btn btn-sm btn-danger delete" onclick="deleteItem('{{ route('cashiers-loan.delete', ['cashier'=>$cashier->id, 'loan' => $item->id]) }}')" data-toggle="modal" data-target="#modal-delete">
                                                        <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm"></span>
                                                    </button>
                                                @else
                                                    <span class="text-danger">{{ $item['deleteObservation'] }}</span>
                                                @endif
                                            </td>   
                                        </tr>
                                        @php
                                            $cont++;
                                            if (!$item->deleted_at) {
                                                $loanCash = $loanCash + $item->amountLoan;
                                            }
                                            else {
                                                $loanDelete+=$item->amountLoan;
                                            }
                                        @endphp
                                    @endforeach

                                    <tr>
                                        <td colspan="6" style="text-align: right"><span class="text-danger">TOTAL ANULADO</span></td>
                                        <td style="text-align: right"><b class="text-danger">{{ number_format($loanDelete, 2, ',', '.') }}</b></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" style="text-align: right">TOTAL PRESTADO</td>
                                        <td style="text-align: right"><b>{{ number_format($loanCash, 2, ',', '.') }}</b></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <h3 id="h3" style="text-align: center">Prestamos de Sueldos </h3>
                        <h3 id="h4">Cobros Realizados <label class="label label-success">Ingreso</label></h3>

                        <div class="table-responsive">
                            <table id="dataStyle" class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>N&deg;</th>
                                        <th style="text-align: center; width:5%">N&deg; Transacción</th>                                                    
                                        <th style="text-align: center">Código</th>
                                        <th style="text-align: center">Cliente</th>
                                        <th style="text-align: center">Atendido Por</th>
                                        <th style="text-align: center">Monto Cobrado</th>
                                        <th style="text-align: center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $cont = 1;
                                        $salaryPurchasePayments = $cashier->salaryPurchasePayment
                                                        // ->where('deleted_at', null)
                                                        ->groupBy('transaction_id')
                                                        ->map(function ($group) {
                                                            return [
                                                                'id' => $group->first()->id, 
                                                                'code'=> $group->first()->salaryPurchase->code,
                                                                'created_at' => $group->first()->transaction->created_at,
                                                                'deleted_at' => $group->first()->transaction->deleted_at,
                                                                'deleteObservation' => $group->first()->deleteObservation,
                                                                'transaction_id'=> $group->first()->transaction->id,
                                                                'transaction_type'=>$group->first()->transaction->type,
                                                                'register'=> $group->first()->agent->name, 
                                                                'ci' => $group->first()->salaryPurchase->person->ci, 
                                                                'full_name' => $group->first()->salaryPurchase->person->first_name.' '.$group->first()->salaryPurchase->person->last_name1.' '.$group->first()->salaryPurchase->person->last_name2, // Obtener created_at de la transacción
                                                                'total_amount' => $group->sum('amount')
                                                            ];
                                                        });

                                        $salaryPurchasePaymentCash = 0;
                                        $salaryPurchasePaymentQr = 0;
                                        $salaryPurchasePaymentDelete = 0;
                                    @endphp
                                    @forelse ($salaryPurchasePayments as $item)
                                        <tr>
                                            <td>{{ $cont }}
                                                @if (auth()->user()->hasRole('admin'))
                                                    <br>
                                                    ID={{$item['transaction_id']}}
                                                @endif
                                            </td>
                                            <td style="text-align: center">{{$item['transaction_id']}}</td>

                                            <td style="text-align: center">{{$item['code']}} <br>
                                                @if ($item['transaction_type'] != 'Efectivo')
                                                    <label class="label label-primary">Qr/Transferencia</label>  
                                                @else
                                                    <label class="label label-success">Efectivo</label> 
                                                @endif
                                                @if ($item['deleted_at'])
                                                    <br>
                                                    <label class="label label-danger">Transaccion eliminada</label>                 
                                                @endif
                                            </td>                                            
                                            <td>
                                                <small>CI:</small> {{$item['ci']?$item['ci']:'No definido'}} <br>
                                                {{$item['full_name']}}
                                            </td>
                                            <td style="text-align: center">
                                                {{$item['register']}} <br>
                                                {{date('d/m/Y h:i:s a', strtotime($item['created_at']))}}<br><small>{{\Carbon\Carbon::parse($item['created_at'])->diffForHumans()}}</small>
                                            </td>
                                            <td style="text-align: right">
                                                @if ($item['deleted_at'])
                                                    <del style="color: red">{{ number_format($item['total_amount'], 2, ',', '.') }}</del>
                                                @else
                                                {{ number_format($item['total_amount'], 2, ',', '.') }}
                                                @endif
                                            </td>
                                            <td style="text-align: right">
                                                @if (!$item['deleted_at'] )
                                                    <button title="Eliminar transacción" class="btn btn-sm btn-danger delete" onclick="deleteItem('{{ route('cashiers-salaryPurchase-transaction.delete', ['cashier'=>$cashier->id, 'transaction' => $item['transaction_id']]) }}')" data-toggle="modal" data-target="#modal-delete">
                                                        <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm"></span>
                                                    </button>
                                                @else
                                                    <span class="text-danger">{{ $item['deleteObservation'] }}</span>
                                                @endif                                              
                                            </td>
                                        </tr>
                                        @php
                                            $cont++;
                                            if(!$item['deleted_at']){
                                                if($item['transaction_type'] == 'Efectivo'){
                                                    $salaryPurchasePaymentCash += $item['total_amount'];
                                                }else{
                                                    $salaryPurchasePaymentQr += $item['total_amount'];
                                                }
                                            }else{
                                                $salaryPurchasePaymentDelete += $item['total_amount'];
                                            }
                                        @endphp
                                    @empty
                                        <tr>
                                            <td style="text-align: center" valign="top" colspan="7" class="dataTables_empty">No hay datos disponibles en la tabla</td>
                                        </tr>
                                    @endforelse
                                    <tr>
                                        <td colspan="5" style="text-align: right"><span class="text-danger">TOTAL ANULADO</span></td>
                                        <td style="text-align: right"><b class="text-danger">{{ number_format($salaryPurchasePaymentDelete, 2, ',', '.') }}</b></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" style="text-align: right">TOTAL COBROS</td>
                                        <td style="text-align: right"><b>{{ number_format($salaryPurchasePaymentCash + $salaryPurchasePaymentQr, 2, ',', '.') }}</b></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" style="text-align: right">TOTAL QR/TRANSFERENCIA</td>
                                        <td style="text-align: right"><b>{{ number_format($salaryPurchasePaymentQr, 2, ',', '.') }}</b></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" style="text-align: right">TOTAL EFECTIVO</td>
                                        <td style="text-align: right"><b>{{ number_format($salaryPurchasePaymentCash, 2, ',', '.') }}</b></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                 
                        <h3 id="h4">Prestamos Entregados <label class="label label-danger">Egreso</label></h3>
                        <div class="table-responsive">                            
                            <table id="dataStyle" class="table table-bordered table-bordered">
                                <thead>
                                    <tr>
                                        <th>N&deg;</th>
                                        <th style="text-align: center">Codigo</th>
                                        <th style="text-align: center">Fecha Solicitud</th>
                                        <th style="text-align: center">Fecha Entrega</th>
                                        <th style="text-align: center">Nombre Completo</th>
                                        <th style="text-align: center">Interes a Cobrar</th>
                                        <th style="text-align: center">Total Prestado</th>
                                        <th style="text-align: center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $cont = 1;
                                        $salaryPurchaseCash = 0;
                                        $salaryPurchaseDelete = 0;
                                    @endphp
                                    @foreach ($cashier->salaryPurchase as $item)
                                        <tr>
                                            <td style="text-align: center">{{ $cont }}</td>
                                            <td style="text-align: center">
                                                {{ $item->code }}
                                            </td>
                                            <td style="text-align: center">{{ $item->date}}</td>
                                            <td style="text-align: center">{{ $item->dateDelivered}}</td>
                                            <td>
                                                <small>CI:</small> {{ $item->person->ci}} <br>
                                                <p>{{ $item->person->first_name}} {{ $item->person->last_name1}} {{ $item->person->last_name2}}</p>
                                                
                                            </td>
                                            
                                            <td style="text-align: right">
                                                @if ($item->deleted_at)
                                                   <del>{{ number_format($item->interest_rate, 2, ',', '.') }}</del>
                                                @else
                                                   {{ number_format($item->interest_rate, 2, ',', '.') }}
                                                @endif
                                            </td>
                                            <td style="text-align: right">
                                                @if ($item->deleted_at)
                                                   <del>{{ number_format($item->amount, 2, ',', '.') }}</del>
                                                @else
                                                   {{ number_format($item->amount, 2, ',', '.') }}
                                                @endif
                                            </td>
                                            <td style="text-align: center">
                                                @if (!$item['deleted_at'])
                                                    <button title="Eliminar transacción" class="btn btn-sm btn-danger delete" onclick="deleteItem('{{ route('cashiers-salaryPurchase.delete', ['cashier'=>$cashier->id, 'salary' => $item->id]) }}')" data-toggle="modal" data-target="#modal-delete">
                                                        <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm"></span>
                                                    </button>
                                                @else
                                                    <span class="text-danger">{{ $item->deleteObservation }}</span>
                                                @endif
                                            </td>   
                                        </tr>
                                        @php
                                            $cont++;
                                            if (!$item->deleted_at) {
                                                $salaryPurchaseCash += $item->amount;
                                            }
                                            else {
                                                $salaryPurchaseDelete+=$item->amount;
                                            }
                                        @endphp
                                    @endforeach

                                    <tr>
                                        <td colspan="6" style="text-align: right"><span class="text-danger">TOTAL ANULADO</span></td>
                                        <td style="text-align: right"><b class="text-danger">{{ number_format($salaryPurchaseDelete, 2, ',', '.') }}</b></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" style="text-align: right">TOTAL PRESTADO</td>
                                        <td style="text-align: right"><b>{{ number_format($salaryPurchaseCash, 2, ',', '.') }}</b></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <h3 id="h3" style="text-align: center">Prendario</h3>
                        <h3 id="h4">Cobros Realizados <label class="label label-success">Ingreso</label></h3>
                        <div class="table-responsive">
                            <table id="dataStyle" class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>N&deg;</th>
                                        <th style="text-align: center; width:5%">N&deg; Transacción</th>                                                    
                                        <th style="text-align: center">Código</th>
                                        <th style="text-align: center">Cliente</th>
                                        <th style="text-align: center">Atendido Por</th>
                                        <th style="text-align: center">Monto Cobrado</th>
                                        <th style="text-align: center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $cont = 1;
                                        $pawnPayments = $cashier->pawnPayment
                                                        // ->where('deleted_at', null)
                                                        ->groupBy('transaction_id')
                                                        ->map(function ($group) {
                                                            return [
                                                                'id' => $group->first()->id, 
                                                                'code'=> $group->first()->pawnRegister->code,
                                                                'codeManual'=>$group->first()->pawnRegister->codeManual,
                                                                'created_at' => $group->first()->transaction->created_at,
                                                                'deleted_at' => $group->first()->transaction->deleted_at,
                                                                'deleteObservation' => $group->first()->deleteObservation,
                                                                'transaction_id'=> $group->first()->transaction->id,
                                                                'transaction_type'=>$group->first()->transaction->type,
                                                                'register'=> $group->first()->agent->name, 
                                                                'ci' => $group->first()->pawnRegister->person->ci, 
                                                                'full_name' => $group->first()->pawnRegister->person->first_name.' '.$group->first()->pawnRegister->person->last_name1.' '.$group->first()->pawnRegister->person->last_name2, // Obtener created_at de la transacción
                                                                'total_amount' => $group->sum('amount')
                                                            ];
                                                        });

                                        $pawnPaymentCash = 0;
                                        $pawnPaymentQr = 0;
                                        $pawnPaymentDelete = 0;
                                    @endphp
                                    @forelse ($pawnPayments as $item)
                                        <tr>
                                            <td>{{ $cont }}
                                                @if (auth()->user()->hasRole('admin'))
                                                    <br>
                                                    ID={{$item['transaction_id']}}
                                                @endif
                                            </td>
                                            <td style="text-align: center">{{$item['transaction_id']}}</td>

                                            <td style="text-align: center">{{$item['code']}} <br>
                                                @if ($item['transaction_type'] != 'Efectivo')
                                                    <label class="label label-primary">Qr/Transferencia</label>  
                                                @else
                                                    <label class="label label-success">Efectivo</label> 
                                                @endif
                                                @if ($item['deleted_at'])
                                                    <br>
                                                    <label class="label label-danger">Transaccion eliminada</label>                 
                                                @endif
                                            </td>                                            
                                            <td>
                                                <small>CI:</small> {{$item['ci']?$item['ci']:'No definido'}} <br>
                                                {{$item['full_name']}}
                                            </td>
                                            <td style="text-align: center">
                                                {{$item['register']}} <br>
                                                {{date('d/m/Y h:i:s a', strtotime($item['created_at']))}}<br><small>{{\Carbon\Carbon::parse($item['created_at'])->diffForHumans()}}</small>
                                            </td>
                                            <td style="text-align: right">
                                                @if ($item['deleted_at'])
                                                    <del style="color: red">{{ number_format($item['total_amount'], 2, ',', '.') }}</del>
                                                @else
                                                {{ number_format($item['total_amount'], 2, ',', '.') }}
                                                @endif
                                            </td>
                                            <td style="text-align: right">
                                                @if (!$item['deleted_at'] )
                                                    <button title="Eliminar transacción" class="btn btn-sm btn-danger delete" onclick="deleteItem('{{ route('cashiers-pawn-transaction.delete', ['cashier'=>$cashier->id, 'transaction' => $item['transaction_id']]) }}')" data-toggle="modal" data-target="#modal-delete">
                                                        <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm"></span>
                                                    </button>
                                                @else
                                                    <span class="text-danger">{{ $item['deleteObservation'] }}</span>
                                                @endif                                              
                                            </td>
                                        </tr>
                                        @php
                                            $cont++;
                                            if(!$item['deleted_at']){
                                                if($item['transaction_type'] == 'Efectivo'){
                                                    $pawnPaymentCash += $item['total_amount'];
                                                }else{
                                                    $pawnPaymentQr += $item['total_amount'];
                                                }
                                            }else{
                                                $pawnPaymentDelete += $item['total_amount'];
                                            }
                                        @endphp
                                    @empty
                                        <tr>
                                            <td style="text-align: center" valign="top" colspan="7" class="dataTables_empty">No hay datos disponibles en la tabla</td>
                                        </tr>
                                    @endforelse
                                    <tr>
                                        <td colspan="5" style="text-align: right"><span class="text-danger">TOTAL ANULADO</span></td>
                                        <td style="text-align: right"><b class="text-danger">{{ number_format($pawnPaymentDelete, 2, ',', '.') }}</b></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" style="text-align: right">TOTAL COBROS</td>
                                        <td style="text-align: right"><b>{{ number_format($pawnPaymentCash + $pawnPaymentQr, 2, ',', '.') }}</b></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" style="text-align: right">TOTAL QR/TRANSFERENCIA</td>
                                        <td style="text-align: right"><b>{{ number_format($pawnPaymentQr, 2, ',', '.') }}</b></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" style="text-align: right">TOTAL EFECTIVO</td>
                                        <td style="text-align: right"><b>{{ number_format($pawnPaymentCash, 2, ',', '.') }}</b></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                 
                        <h3 id="h4">Prestamos Entregados <label class="label label-danger">Egreso</label></h3>
                        <div class="table-responsive">                            
                            <table id="dataStyle" class="table table-bordered table-bordered">
                                <thead>
                                    <tr>
                                        <th width="2%">N&deg;</th>
                                        <th style="width: 10%">Codigo</th>
                                        <th style="text-align: center">Fecha</th>
                                        <th>Nombre Completo</th>
                                        <th style="text-align: center">Actículos</th>
                                        <th width="15%">Detalles</th>
                                        <th style="text-align: center">Total</th>
                                        <th style="text-align: center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $cont = 1;
                                        $pawnCash=0;
                                        $pawnDelete = 0;
                                    @endphp
                                    @foreach ($cashier->pawn as $item)
                                        <tr>
                                            <td>
                                                {{ $cont }}
                                                @if (auth()->user()->hasRole('admin'))
                                                    <br>
                                                    ID={{$item->id}}
                                                @endif
                                            </td>
                                            <td>
                                                {{ $item->code }} {{$item->codeManual?'-'.$item->codeManual:''}}
                                            </td>
                                            <td style="text-align: center">{{ $item->date}}</td>
                                            <td>
                                                <small>CI:</small> {{ $item->person->ci}} <br>
                                                <p>{{ $item->person->first_name}} {{ $item->person->last_name1}} {{ $item->person->last_name2}}</p>
                                                
                                            </td>
                                            <td style="text-align:left">
                                                <ul>
                                                    @foreach ($item->details as $detail)
                                                        @php
                                                            $features_list = '';
                                                            foreach ($detail->features_list as $feature) {
                                                                if ($feature->value) {
                                                                    $features_list .= '<span><b>'.$feature->title.'</b>: '.$feature->value.'</span><br>';
                                                                }
                                                            }
                                                        @endphp
                                                        <li style="font-size: 14px">
                                                            {{ floatval($detail->quantity) == intval($detail->quantity) ? intval($detail->quantity) : $detail->quantity }}
                                                            {{ $detail->type->unit }} {{ $detail->type->name }} a {{ floatval($detail->price) == intval($detail->price) ? intval($detail->price) : $detail->price }}
                                                            <span style="font-size: 10px">Bs.</span> <br>
                                                            {!! $features_list !!}
                                                        </li>
                                                    @endforeach
                                                </ul>

                                            </td>
                                            <td>
                                                <table style="width: 100%">
                                                    <tr>
                                                        <td><b>Prestamos</b></td>
                                                        <td style="text-align: right"><small>Bs. </small>{{ number_format($item->amountTotal, 2, ',', '.') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Interes</b></td>
                                                        <td style="text-align: right"> <small>% </small>{{ number_format($item->interest_rate, 2, ',', '.') }}</td>
                                                    </tr>
                                                  
                                                </table>
                                            </td>
                                            <td style="text-align: right">
                                                @if ($item->deleted_at)
                                                   <del style="color: red">{{ number_format($item->amountTotal, 2, ',', '.') }}</del>
                                                @else
                                                   {{ number_format($item->amountTotal, 2, ',', '.') }}
                                                @endif
                                            </td>
                                            <td style="text-align: right">
                                                @if (!$item['deleted_at'] )
                                                    <button title="Eliminar prestamo" class="btn btn-sm btn-danger delete" onclick="deleteItem('{{ route('cashier-pawn.delete', ['cashier'=>$cashier->id, 'pawn' => $item->id]) }}')" data-toggle="modal" data-target="#modal-delete">
                                                    <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm"></span>
                                                    </button>
                                                @else
                                                    <span class="text-danger">{{ $item->deleteObservation }}</span>
                                                @endif                                             
                                            </td>
                                        </tr>
                                        @php

                                            $cont++;
                                            if(!$item->deleted_at){
                                                $pawnCash+= $item->amountTotal;
                                            }else{
                                                $pawnDelete+= $item->amountTotal;
                                            }
                                        @endphp
                                    @endforeach

                                    <tr>
                                        <td colspan="6" style="text-align: right"><span class="text-danger">TOTAL ANULADO</span></td>
                                        <td style="text-align: right"><b class="text-danger">{{ number_format($pawnDelete, 2, ',', '.') }}</b></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" style="text-align: right">TOTAL ENTREGADO</td>
                                        <td style="text-align: right"><b>{{ number_format($pawnCash, 2, ',', '.') }}</b></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h3 id="h4">Dinero Adicional Entregados <label class="label label-danger">Egreso</label></h3>
                        <div class="table-responsive">                            
                            <table id="dataStyle" class="table table-bordered table-bordered">
                                <thead>
                                    <tr>
                                        <th width="2%">N&deg;</th>
                                        <th style="width: 10%">Codigo del Prestamo</th>
                                        <th style="text-align: center">Fecha</th>
                                        <th style="text-align: center">Nombre Completo</th>
                                        <th style="text-align: center">Descripción</th>
                                        <th style="text-align: center">Total</th>
                                        <th style="text-align: center">Acciones</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $cont = 1;
                                        $amountAditionalCash=0;
                                        $amountAditionalDelete = 0;
                                    @endphp
                                    @foreach ($cashier->pawnMoneyAditional as $item)
                                        <tr>
                                            <td>
                                                {{ $cont }}
                                                @if (auth()->user()->hasRole('admin'))
                                                    <br>
                                                    ID={{$item->id}}
                                                @endif
                                            </td>
                                            <td style="text-align: center">
                                                {{ $item->pawnRegister->code }} {{$item->pawnRegister->codeManual?'-'.$item->pawnRegister->codeManual:''}}
                                            </td>
                                            <td style="text-align: center">
                                                {{date('d/m/Y h:i:s a', strtotime($item->created_at))}}<br><small>{{\Carbon\Carbon::parse($item->created_at)->diffForHumans()}}</small>
                                            </td>
                                            <td>
                                                <small>CI:</small> {{ $item->pawnRegister->person->ci}} <br>
                                                <p>{{ $item->pawnRegister->person->first_name}} {{ $item->pawnRegister->person->last_name1}} {{ $item->pawnRegister->person->last_name2}}</p>
                                                
                                            </td>
                                            <td>
                                                {{$item->description}}
                                            </td>
                                            <td style="text-align: right">
                                                @if ($item->deleted_at)
                                                   <del style="color: red">{{ number_format($item->amountTotal, 2, ',', '.') }}</del>
                                                @else
                                                   {{ number_format($item->amountTotal, 2, ',', '.') }}
                                                @endif
                                            </td>
                                            <td style="text-align: right">
                                                @if (!$item->deleted_at )
                                                    <button title="Eliminar el monto adicional" class="btn btn-sm btn-danger delete" onclick="deleteItem('{{ route('cashier-pawn-aditional.delete', ['cashier'=>$cashier->id, 'aditional' => $item->id]) }}')" data-toggle="modal" data-target="#modal-delete">
                                                    <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm"></span>
                                                    </button>
                                                @else
                                                    <span class="text-danger">{{ $item->deletedObservation }}</span>
                                                @endif                                             
                                            </td>
                                        </tr>
                                        @php
                                            $cont++;
                                            if (!$item->deleted_at) {
                                                $amountAditionalCash+=$item->amountTotal;                                                
                                            } else {
                                                $amountAditionalDelete+=$item->amountTotal;                                                
                                            }
                                            
                                        @endphp
                                    @endforeach
                                    <tr>
                                        <td colspan="5" style="text-align: right"><span class="text-danger">TOTAL ANULADO</span></td>
                                        <td style="text-align: right"><b class="text-danger">{{ number_format($amountAditionalDelete, 2, ',', '.') }}</b></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" style="text-align: right"><b>TOTAL</b></td>
                                        <td style="text-align: right"><b>{{ number_format($amountAditionalCash, 2, ',', '.') }}</b></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <h3 id="h3" style="text-align: center">Ventas</h3>
                        <h3 id="h4">Cobros Realizados <label class="label label-success">Ingreso</label></h3>
                        <div class="table-responsive">
                            <table id="dataStyle" class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>N&deg;</th>
                                        <th style="text-align: center; width:10%">N&deg; Transacción</th>                                                    
                                        <th style="text-align: center">Código Venta</th>
                                        <th style="text-align: center">Fecha Pago</th>
                                        <th style="text-align: center">Cliente</th>
                                        <th style="text-align: center">Atendido Por</th>
                                        <th style="text-align: center; width:8%">Monto Cobrado</th>
                                        <th style="text-align: center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $cont = 1;
                                        $salePaymentCash = 0;
                                        $salePaymentQr = 0;
                                        $salePaymentDelete = 0;
                                    @endphp
                                    @forelse ($cashier->salePayment as $item)
                                        <tr>
                                            <td>{{ $cont }}</td>
                                            <td style="text-align: center">
                                                {{$item->transaction->transaction}} <br>
                                                {{$item->sale->typeSale=='Credito'?'Venta al Credito':'Venta al Contado'}}
                                            </td>
                                            <td style="text-align: center">{{$item->sale->code}} <br>
                                                @if ($item->transaction->deleted_at)
                                                    <label class="label label-danger">Transaccion eliminada</label>                                                        
                                                @endif
                                                @if ($item->transaction->type != 'Efectivo')
                                                    <label class="label label-primary">Qr/Transferencia</label>  
                                                @endif
                                            </td>
                                            <td style="text-align: center">
                                                {{date('d/m/Y h:i:s a', strtotime($item->transaction->created_at))}}<br><small>{{\Carbon\Carbon::parse($item->transaction->created_at)->diffForHumans()}}</small>
                                            </td>
                                            <td>
                                                <small>CI:</small> {{$item->sale->person_id?$item->sale->person->ci:'No definido'}} <br>
                                                @if ($item->sale->person_id)
                                                    {{$item->sale->person->first_name}} {{$item->sale->person->last_name1}} {{$item->sale->person->last_name2}}
                                                @endif
                                            </td>

                                            <td style="text-align: center">
                                                {{ $item->register->name }} <br> {{$item->agentType}}
                                            </td>
                                            <td style="text-align: right">
                                                {{-- {{ $item->amount }} --}}
                                                @if ($item->deleted_at)
                                                   <del style="color: red">{{ number_format($item->amount, 2, ',', '.') }}</del>
                                                @else
                                                   {{ number_format($item->amount, 2, ',', '.') }}
                                                @endif
                                            </td>
                                            
                                            <td style="text-align: right">
                                                @if (!$item->deleted_at )
                                                    <button title="Eliminar transacción" class="btn btn-sm btn-danger delete" onclick="deleteItem('{{ route('cashier-sale.delete', ['cashier'=>$cashier->id, 'saleAgent' => $item->id]) }}')" data-toggle="modal" data-target="#modal-delete">
                                                    <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm"></span>
                                                    </button>
                                                @else
                                                    {{-- <span class="text-danger">{{ $item->deletedObservation }}</span> --}}
                                                @endif                                             
                                            </td>
                                        </tr>
                                        @php
                                            $cont++;
                                            if(!$item->deleted_at){
                                                if($item->transaction->type == 'Efectivo'){
                                                    $salePaymentCash += $item->amount;
                                                }else{
                                                    $salePaymentQr += $item->amount;
                                                }
                                            }else{
                                                $salePaymentDelete += $item->amount;
                                            }
                                        @endphp
                                    @empty
                                        <tr>
                                            <td style="text-align: center" valign="top" colspan="8" class="dataTables_empty">No hay datos disponibles en la tabla</td>
                                        </tr>
                                    @endforelse
                                    <tr>
                                        <td colspan="6" style="text-align: right"><span class="text-danger">TOTAL ANULADO</span></td>
                                        <td style="text-align: right"><b class="text-danger">{{ number_format($salePaymentDelete, 2, ',', '.') }}</b></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" style="text-align: right">TOTAL COBROS</td>
                                        <td style="text-align: right"><b>{{ number_format($salePaymentCash + $salePaymentQr, 2, ',', '.') }}</b></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" style="text-align: right">TOTAL QR/TRANSFERENCIA</td>
                                        <td style="text-align: right"><b>{{ number_format($salePaymentQr, 2, ',', '.') }}</b></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" style="text-align: right">TOTAL EFECTIVO</td>
                                        <td style="text-align: right"><b>{{ number_format($salePaymentCash, 2, ',', '.') }}</b></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <h3 id="h4">Traspaso de Caja <label class="label label-danger">egreso</label></h3>
                        <div class="table-responsive">                            
                            <table id="dataStyle" class="table table-bordered table-bordered">
                                <thead>
                                    <tr>
                                        <th>N&deg;</th>
                                        <th style="text-align: center">Fecha y Hora de Registro</th>
                                        <th style="text-align: center">Registrado Por</th>
                                        <th style="text-align: center">Detalle</th>
                                        <th style="text-align: center">Estado</th>
                                        <th style="text-align: center">Monto</th>
                                        <th style="text-align: center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $count = 1;
                                        $transferAmountCash = 0;
                                        $transferAmountDelete = 0;
                                    @endphp
                                    @forelse ($cashier->movements->where('type', 'egreso')->where('transferCashier_id', '!=', null) as $item)
                                        <tr>
                                            <td>
                                                {{ $count }}
                                                @if (auth()->user()->hasRole('admin'))
                                                    <br>
                                                    ID={{$item->id}}
                                                @endif
                                            </td>
                                            <td style="text-align: center">{{ date('d/m/Y h:i:s a', strtotime($item->created_at))}}<br><small>{{\Carbon\Carbon::parse($item->created_at)->diffForHumans() }}</small></td>
                                            <td style="text-align: center">{{ $item->user->name }}</td>
                                            <td>{{ $item->description }}</td>
                                            <td style="text-align: center">
                                                @if ($item->deleted_at)
                                                    <label class="label label-danger">Eliminado</label>        
                                                @else
                                                    @if ($item->status == 'Aceptado')
                                                        <label class="label label-success">Aceptado</label>  
                                                    @endif
                                                    @if ($item->status == 'Pendiente')
                                                        <label class="label label-primary">Pendiente</label>  
                                                    @endif
                                                    @if ($item->status == 'Rechazado')
                                                        <label class="label label-dark">Rechazado</label>  
                                                    @endif
                                                @endif
                                            </td>
                                            <td style="text-align: right">
                                                @if ($item->deleted_at || $item->status == 'Rechazado')
                                                   <del style="color: red">{{ number_format($item->amount, 2, ',', '.') }}</del>
                                                @else
                                                   {{ number_format($item->amount, 2, ',', '.') }}
                                                @endif
                                            </td>
                                            <td style="text-align: right">
                                                @if (!$item->deleted_at && $item->status=='Pendiente')
                                                    <button title="Eliminar transacción" class="btn btn-sm btn-danger delete" 
                                                        onclick="deleteItem('{{ route('cashiers-amount-transfer.delete', ['cashier_id'=>$cashier->id,'transfer_id'=>$item->id]) }}')" data-toggle="modal" data-target="#modal-delete">
                                                        <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm"></span>
                                                    </button>
                                                @endif                                             
                                            </td>
                                        </tr>
                                        @php
                                            if (!$item->deleted_at && $item->status != 'Rechazado') {
                                                $transferAmountCash += $item->amount;

                                            } else {
                                                $transferAmountDelete += $item->amount;
                                            }
                                            $count++;
                                        @endphp
                                    @empty
                                        <tr>
                                            <td class="text-center" valign="top" colspan="7" class="dataTables_empty">No hay datos disponibles en la tabla</td>
                                        </tr>
                                    @endforelse
                                    <tr>
                                        <td colspan="5" style="text-align: right"><span class="text-danger">TOTAL ANULADO Y RECHAZADO</span></td>
                                        <td style="text-align: right"><b class="text-danger">{{ number_format($transferAmountDelete, 2, ',', '.') }}</b></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" style="text-align: right"><b>TOTAL</b></td>
                                        <td style="text-align: right"><b>{{ number_format($transferAmountCash, 2, ',', '.') }}</b></td>
                                       <td></td> 
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <h3 id="h4">Gastos Realizados <label class="label label-danger">egreso</label></h3>
                        <div class="table-responsive">                            
                            <table id="dataStyle" class="table table-bordered table-bordered">
                                <thead>
                                    <tr>
                                        <th>N&deg;</th>
                                        <th style="text-align: center">Fecha y Hora de Registro</th>
                                        <th style="text-align: center">Registrado Por</th>
                                        <th style="text-align: center">Detalle</th>
                                        <th style="text-align: center">Monto</th>
                                        <th style="text-align: center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $count=1;
                                        $extraExpenseCash = 0;
                                        $extraExpenseDelete =0;
                                    @endphp
                                    @forelse ($cashier->movements->where('type', 'egreso')->where('transferCashier_id', null)->where('status', 'Aceptado') as $item)
                                        <tr>
                                            <td>
                                                {{ $count }}
                                                @if (auth()->user()->hasRole('admin'))
                                                    <br>
                                                    ID={{$item->id}}
                                                @endif
                                            </td>
                                            <td style="text-align: center">{{ date('d/m/Y h:i:s a', strtotime($item->created_at))}}<br><small>{{\Carbon\Carbon::parse($item->created_at)->diffForHumans() }}</small></td>
                                            <td style="text-align: center">{{ $item->user->name }}</td>
                                            <td>{{ $item->description }}</td>
                                            <td style="text-align: right">
                                                @if ($item->deleted_at)
                                                   <del style="color: red">{{ number_format($item->amount, 2, ',', '.') }}</del>
                                                @else
                                                   {{ number_format($item->amount, 2, ',', '.') }}
                                                @endif
                                            </td>
                                            <td style="text-align: right">
                                                @if (!$item['deleted_at'] )
                                                    <button title="Eliminar prestamo" class="btn btn-sm btn-danger delete" onclick="deleteItem('{{ route('cashiers-expense.delete', ['cashier'=>$cashier->id, 'expense' => $item->id]) }}')" data-toggle="modal" data-target="#modal-delete">
                                                    <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm"></span>
                                                    </button>
                                                @endif   
                                            </td>
                                        </tr>
                                        @php
                                            if (!$item->deleted_at) {
                                                $extraExpenseCash += $item->amount;                                                
                                            } else {
                                                $extraExpenseDelete += $item->amount;                                                
                                            }                                            
                                            $count++;
                                        @endphp
                                    @empty
                                        <tr>
                                            <td class="text-center" valign="top" colspan="6" class="dataTables_empty">No hay datos disponibles en la tabla</td>
                                        </tr>
                                    @endforelse
                                    <tr>
                                        <td colspan="4" style="text-align: right"><span class="text-danger">TOTAL ANULADO</span></td>
                                        <td style="text-align: right"><b class="text-danger">{{ number_format($extraExpenseDelete, 2, ',', '.') }}</b></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" style="text-align: right"><b>TOTAL</b></td>
                                        <td style="text-align: right"><b>{{ number_format($extraExpenseCash, 2, ',', '.') }}</b></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="panel panel-bordered" style="padding-bottom:5px;">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-hover">
                                <tr>
                                    <td><h3>Dinero abonado</h3></td>
                                    <td style="text-align: right"><h2>{{ number_format($cashierInput, 2, ',', '.') }}</h2></td>
                                </tr>
                                <tr>
                                    <td><h3>Cobros realizados en efectivo</h3></td>
                                    <td style="text-align: right"><h2>{{ number_format($loanPaymentCash+$pawnPaymentCash+$salePaymentCash +$salaryPurchasePaymentCash, 2, ',', '.') }}</h2></td>
                                </tr>
                                <tr>
                                    <td><h3>Cobros realizados mediante QR</h3></td>
                                    <td style="text-align: right"><h2>{{ number_format($loanPaymentQr+$pawnPaymentQr+$salePaymentQr + $salaryPurchasePaymentQr, 2, ',', '.') }}</h2></td>
                                </tr>
                                <tr>
                                    <td><h3>Prestamos entregados</h3></td>
                                    <td style="text-align: right"><h2>{{ number_format($loanCash+$pawnCash+$amountAditionalCash+$salaryPurchaseCash, 2, ',', '.') }}</h2></td>
                                </tr>
                                <tr>
                                    <td><h3>Gastos realizados</h3></td>
                                    <td style="text-align: right"><h2>{{ number_format($extraExpenseCash + $transferAmountCash, 2, ',', '.') }}</h2></td>
                                </tr>
                                <tr style="background-color: #E5E8E8">
                                    <td><h3>Dinero en efectivo disponible</h3></td>
                                    <td style="text-align: right"><h2>{{ number_format($cashierInput + $loanPaymentCash +$pawnPaymentCash + $salePaymentCash + $salaryPurchasePaymentCash - $loanCash - $extraExpenseCash - $transferAmountCash - $pawnCash - $amountAditionalCash - $salaryPurchaseCash, 2, ',', '.') }}</h2></td>
                                </tr>
                                @if ($cashier->amount)
                                    <tr style="background-color: #E5E8E8">
                                        <td><h3>Dinero en efectivo al cerrar caja</h3></td>
                                        <td style="text-align: right"><h2>{{ number_format($cashier->amount_real, 2, ',', '.') }}</h2></td>
                                    </tr>
                                    <tr style="background-color: #E5E8E8">
                                        <td><h3>Saldo</h3></td>
                                        <td style="text-align: right"><h2 class="@if($cashier->balance > 0) text-success @endif @if($cashier->balance < 0) text-danger @endif">{{ number_format($cashier->balance, 2, ',', '.') }}</h2></td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- <div class="modal modal-danger fade" data-backdrop="static" tabindex="-1" id="delete-transacction-modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="voyager-trash"></i> Desea eliminar la transacción?</h4>
                </div>
                <div class="modal-body">
                    <form action="#" id="delete_form" method="POST">
                        {{ csrf_field() }}
                    <div class="form-group">
                        <label for="observation">Motivo</label>
                        <textarea name="observations" class="form-control" rows="5" placeholder="Describa el motivo de la anulación del pago" required></textarea>
                    </div>
                    <label class="checkbox-inline"><input type="checkbox" value="1" required>Confirmar anulación</label>
                </div>
                <div class="modal-footer">
                        <input type="submit" class="btn btn-danger pull-right delete-confirm" value="Sí, eliminar">
                    </form>
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div> --}}

    {{-- <form id="form-delete" action="{{ route('cashiers-loan.delete') }}" method="POST">
        @csrf
        <div class="modal modal-danger fade" tabindex="-1" id="delete_payment-modal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="voyager-trash"></i> Desea anular el siguiente prestamos?</h4>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="cashier_id" value="{{ $cashier->id }}">
                        <input type="hidden" name="loan_id" id="loan_id">

                        <div class="form-group">
                            <label for="observation">Motivo</label>
                            <textarea name="observations" class="form-control" rows="5" placeholder="Describa el motivo de la anulación del pago" required></textarea>
                        </div>
                        <label class="checkbox-inline"><input type="checkbox" value="1" required>Confirmar anulación</label>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <input type="submit" class="btn btn-danger" value="Sí, ¡anúlalo!">
                    </div>
                </div>
            </div>
        </div>
    </form> --}}

    @include('partials.modal-delete')
    @include('partials.modal-mapsView')



@stop

@section('javascript')
    <script>

        function deleteItem(url){
            $('#delete_form').attr('action', url);
        }

        $(document).ready(function () {
            $('.btn-delete').click(function(){
                let loan_id = $(this).data('id');
                $(`#form-delete input[name="loan_id"]`).val(loan_id);
            });
        });

        $(document).ready(function(){                
                // Inicializar el mapa cuando se hace clic en el botón
                $('.view-location').click(function(e) {
                    e.preventDefault();
                    var lat = $(this).data('lat');
                    var lng = $(this).data('lng');
                    
                    if(!lat || !lng) {
                        toastr.warning('No hay coordenadas disponibles para esta transacción');
                        return;
                    }
                    
                    $('#mapModal').modal('show');
                    
                    // Esperar a que el modal se muestre completamente
                    $('#mapModal').on('shown.bs.modal', function() {
                        initMap(lat, lng);
                    });
                });
                
                // Función para inicializar el mapa
                function initMap(lat, lng) {
                    // Si ya existe un mapa, lo eliminamos
                    if(window.mapInstance) {
                        window.mapInstance = null;
                        $('#map').empty();
                    }
                    
                    var mapOptions = {
                        center: { lat: parseFloat(lat), lng: parseFloat(lng) },
                        zoom: 15
                    };
                    
                    window.mapInstance = new google.maps.Map(document.getElementById('map'), mapOptions);
                    
                    new google.maps.Marker({
                        position: { lat: parseFloat(lat), lng: parseFloat(lng) },
                        map: window.mapInstance,
                        title: 'Ubicación de la transacción'
                    });
                }
                
                // Cargar la API de Google Maps
                function loadGoogleMaps() {
                    if(typeof google === 'undefined' || typeof google.maps === 'undefined') {
                        var script = document.createElement('script');
                        script.src = 'https://maps.googleapis.com/maps/api/js?key={{ env("GOOGLE_MAPS_API_KEY") }}&callback=initMap';
                        script.defer = true;
                        script.async = true;
                        document.head.appendChild(script);
                    }
                }
                
                // Cargar la API cuando la página esté lista
                loadGoogleMaps();
            });

    </script>
@stop
@else
    @section('content')
        <h1>No tienes permiso</h1>
    @stop
@endif
