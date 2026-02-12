@extends('voyager::master')

@section('content')
    @php
        $meses = array('', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');       
    @endphp
    <div class="page-content browse container-fluid">
        @include('voyager::alerts')
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h2 id="h2">Hola, {{ Auth::user()->name }}</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('gerente') || auth()->user()->hasRole('administrador'))
            <div class="row">
                @php
                    $data = App\Models\Loan::where('deleted_at', NULL)->get();
                    $date = date('Y-m-d');
                @endphp
                <div class="col-md-12">
                    <h2 id="h2">Cartera Prestamos Diarios</h2>
                </div>
                <div class="col-md-3">
                    <div class="panel panel-bordered" style="border-left: 5px solid #5f6662">
                        <div class="panel-body" style="height: 100px;padding: 15px 20px">
                            <div class="col-md-9">
                                <h5>Total de Prestamos Diarios</h5>
                                <h2>{{count($data->where('status', 'entregado'))}}</h2>
                            </div>
                            <div class="col-md-3 text-right">
                                <i class="icon fa-solid fa-money-bill" style="color: #5f6662"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="panel panel-bordered" style="border-left: 5px solid #52BE80">
                        <div class="panel-body" style="height: 100px;padding: 15px 20px">
                            <div class="col-md-9">
                                <h5>Prestamos Diarios Pagados</h5>
                                <h2>{{count($data->where('status', 'entregado')->where('debt', 0))}}</h2>
                            </div>
                            <div class="col-md-3 text-right">
                                <i class="icon fa-solid fa-money-bill" style="color: #52BE80"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="panel panel-bordered" style="border-left: 5px solid #55a0f7">
                        <div class="panel-body" style="height: 100px;padding: 15px 20px">
                            <div class="col-md-9">
                                <h5>Prestamos en pagos</h5>
                                <h2>{{count($data->where('status', 'entregado')->where('debt', '!=', 0))}}</h2>
                            </div>
                            <div class="col-md-3 text-right">
                                <i class="icon fa-solid fa-money-bill" style="color: #55a0f7"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="panel panel-bordered" style="border-left: 5px solid #E67E22">
                        <div class="panel-body" style="height: 100px;padding: 15px 20px">
                            <div class="col-md-9">
                                <h5>Prestamos por entregar al cliente</h5>
                                <h2>{{count($data->where('status', 'aprobado'))}}</h2>
                            </div>
                            <div class="col-md-3 text-right">
                                <i class="icon voyager-calendar" style="color: #E67E22"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    @php
                        $incomePerson = App\Models\LoanDayAgent::with(['agent'])
                            ->where('deleted_at', null)->whereDate('created_at', $date)
                            ->select('agent_id', DB::raw('SUM(amount) as amount'))
                            ->groupBy('agent_id')
                            ->get();
                    @endphp
                    <div class="panel">
                        <div class="panel-body" style="height: 350px">
                            <canvas id="income-chart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="panel">
                        <div class="panel-body" style="height: 350px">
                            <small><h4>Egreso & Ingreso del Día (Bs.)</h4></small>
                            @php
                                $date = date('Y-m-d');
                                $moneyLoan = App\Models\Loan::where('deleted_at', NULL)->where('dateDelivered', $date)->get();
                                $moneyLoan = $moneyLoan->SUM('amountLoan');
                                $moneyRecaudado = App\Models\LoanDayAgent::where('deleted_at', NULL)->whereDate('created_at', $date)->get();
                                $moneyRecaudado = $moneyRecaudado->SUM('amount');
                                // dd($moneyRecaudado);
                            @endphp
                            <canvas id="doughnut-chart"></canvas>
                        </div>
                    </div>
                </div>
                {{-- ::::::::::::::::::::::::::::::::::: PARA PRENDARIOS :::::::::::::::::::::::::::::::::: --}}
                <div class="col-md-12">
                    <h2 id="h2">Cartera Prendario</h2>
                </div>
            </div>
        @endif
    
        @php

            
            $cashier = \App\Models\Cashier::with(['movements' => function($q){
                $q->where('deleted_at', NULL);
            },'vault_details.cash' => function($q){
                $q->where('deleted_at', NULL);
            }])
            ->where('user_id', Auth::user()->id)
            ->where('status', '<>', 'cerrada')
            ->where('deleted_at', NULL)->first();

            
        @endphp
        @if ($cashier)
            @if ($cashier->status == 'abierta' || $cashier->status == 'apertura pendiente')
                
                @if ($cashier->status == 'abierta')
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-bordered">
                                <div class="panel-body">
                                    @php
                                        $transferIncome = \App\Models\CashierMovement::with(['cashier.user'])
                                                            ->where('deleted_at', null)
                                                            ->where('status', 'Pendiente')
                                                            ->where('transferCashier_id', $global_cashier['cashier']->id)
                                                            ->get();
                                    @endphp
                                    @if (count($transferIncome)>0)
                                        <div class="row">                                        
                                            <div class="col-md-12">
                                                <div class="alert alert-success">
                                                    <strong>Transferencia Por Recibir:</strong>
                                                    <p>Usted cuenta con transferencia de dinero pendiente por recibir de las siguientes personas.</p>
                                                </div>
                                                <table  id="dataStyle" class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th style="text-align: center">Nro</th>
                                                            <th style="text-align: center">Nombre</th>
                                                            <th style="text-align: center">Detalle</th>
                                                            <th style="text-align: center; width: 18%">Fecha</th>
                                                            <th style="text-align: center; width: 10%">Monto</th>
                                                            <th style="text-align: center; width: 15%">Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php
                                                            $count=1;
                                                        @endphp
                                                        @foreach ($transferIncome as $item)
                                                            <tr>
                                                                <td>
                                                                    {{$count}}
                                                                </td>
                                                                <td>
                                                                    {{$item->cashier->user->name}}
                                                                </td>
                                                                <td>
                                                                    {{$item->description}}
                                                                </td>
                                                                <td style="text-align: center">
                                                                    {{date('d/m/Y H:i:s a', strtotime($item->created_at))}}<br><small>{{\Carbon\Carbon::parse($item->created_at)->diffForHumans()}}
                                                                </td>
                                                                <td style="text-align: right">
                                                                    {{number_format($item->amount, 2, '.', '')}}
                                                                </td>
                                                                <td style="text-align: right">
                                                                    <button type="button" data-toggle="modal" data-target="#declineTransaction-modal" onclick="declineItem('{{ route('cashiers-transfer.decline', ['cashier_id'=>$global_cashier['cashier']->id,'transfer_id'=>$item['id']]) }}')" class="btn btn-dark">Rechazar</button>
                                                                    
                                                                    <button type="button" data-toggle="modal" data-target="#successTransaction-modal" onclick="successItem('{{ route('cashiers-transfer.success', ['cashier_id'=>$global_cashier['cashier']->id,'transfer_id'=>$item['id']]) }}')" class="btn btn-success">Aceptar</button>
                                                            
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                                    
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h2 id="h2"><i class="fa-solid fa-wallet"></i> {{ $cashier->title }}</h2>
                                        </div>
                                        @if ($cashier->status == 'abierta')
                                            <div class="col-md-6 text-right">
                                                <a  href="#" data-toggle="modal" data-target="#agregar-gasto-modal" title="Agregar Gastos" class="btn btn-success">Gastos <i class="fa-solid fa-money-bill-transfer"></i></a>
                                                <a  href="#" data-toggle="modal" data-target="#modal_transfer_moneyCashier" title="Transferir Dinero" class="btn btn-success">Traspaso <i class="fa-solid fa-money-bill-transfer"></i></a>

                                                <a href="{{ route('cashiers.close', ['cashier' => $cashier->id]) }}" class="btn btn-danger">Cerrar <i class="voyager-lock"></i></a>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6" style="margin-top: 50px">
                                            <table width="100%" cellpadding="20">
                                                <tr>
                                                    <td><small>Dinero Asignado a Caja</small></td>
                                                    <td class="text-right"><h3>{{ number_format($global_cashier['cashierIn'], 2, '.', '') }} <small>Bs.</small></h3></td>
                                                </tr>
                                                <tr>
                                                    <td><small>Dinero disponible en Caja</small></td>
                                                    <td class="text-right"><h3>{{ number_format($global_cashier['amountCashier'], 2, ',', '.') }} <small>Bs.</small></h3></td>
                                                </tr>
                                            </table>
                                            <hr>
                                            <table width="100%" cellpadding="20">
                                                <tr>
                                                    <td><small>Cobros Diarios, Prendario y Ventas "Efectivo"</small></td>
                                                    <td class="text-right"><h3>{{ number_format($global_cashier['amountEfectivo'], 2, ',', '.') }} <small>Bs.</small></h3></td>
                                                </tr>
                                                <tr>
                                                    <td><small>Prestamos Diario y Prendario Entregados</small></td>
                                                    <td class="text-right"><h3>{{ number_format($global_cashier['amountEgres'], 2, '.', '') }} <small>Bs.</small></h3></td>
                                                </tr>
                                                <tr>
                                                    <td><small>Gastos Realizados</small></td>
                                                    <td class="text-right"><h3>{{ number_format($global_cashier['cashierOut'], 2, ',', '.') }} <small>Bs.</small></h3></td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <canvas id="myChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form class="form-submit" action="{{ route('cashiers-amount-transfer.store') }}" method="POST">
                        {{ csrf_field() }}
                        <div class="modal modal-success fade" data-backdrop="static" id="modal_transfer_moneyCashier" role="dialog">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                                        <h4 class="modal-title"><i class="fa-solid fa-money-bill-transfer"></i> Transferir Dinero</h4>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="form-group col-md-12">
                                                <small>Cajero</small>
                                                <select name="transferCashier_id" id="transferCashier_id" class="form-control select2" required>
                                                    <option value="" disabled selected>--Seleccione una opción--</option>
                                                    @foreach (App\Models\Cashier::with(['user'])->where('status', 'abierta')->where('deleted_at', null)->get() as $item)
                                                        <option value="{{$item->id}}">{{$item->user->name}}</option>                                                        
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group col-md-12">
                                                <small>Monto</small>
                                                <input type="number" style="text-align: right" value="" step="0.01" min="1" max="{{$global_cashier['amountCashier']}}" class="form-control" name="transferCashier" id="transferCashier">
                                            </div>   
                                            <div class="form-group col-md-12">
                                                <small>Descripción</small>
                                                <textarea name="description" id="description" class="form-control" cols="30" rows="3" required></textarea>
                                            </div>             
                                            <div class="col-md-12">                
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" value="1" required><b><small>Confirmar Transferencia..!</small></b>
                                                </label>
                                            </div>                                            
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <input type="submit" class="btn btn-success pull-right delete-confirm" value="Sí, transferir">
                                        <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>


                    @if ($global_cashier['cashier'])    
                        <div class="row">                            
                           {{-- Para prestanos diarios --}}
                            @if (count($global_cashier['cashier']->loans) || count($global_cashier['cashier']->loan_payments))
                                <div class="col-md-12">
                                    <div class="panel panel-bordered">
                                        <div class="panel-body">
                                            <h3 id="h3" style="text-align: center">Prestamos Diarios</h3>
                                            @if (count($global_cashier['cashier']->loans))
                                                <h3 id="h4">Prestamos Entregados <label class="label label-danger">Egresos</label></h3>
                                                <div class="table-responsive">
                                                    <table id="dataStyle" class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Id</th>
                                                                <th>Código</th>
                                                                <th>Fecha de Entrega</th>
                                                                <th>Nombre Cliente</th>                    
                                                                <th>Tipo de Préstamos</th>                    
                                                                <th style="text-align: center">Entregado por</th>                    
                                                                <th>Monto Prestado</th>       
                                                                <th>Interes a Cobrar</th>       
                                                                <th>Monto Prestado + Interes a Cobrar</th>       
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @php
                                                                $amountLoans = 0;
                                                                $amountPorcentages = 0;
                                                                $amountLoanTotal = 0;
                                                            @endphp
                                                            @forelse ($global_cashier['cashier']->loans as $item)
                                                                <tr>
                                                                    <td>{{ $item->id }}</td>
                                                                    <td>{{ $item->code }}</td>
                                                                    <td>{{ date("d-m-Y", strtotime($item->dateDelivered)) }}</td>
                                                                    <td>
                                                                        <small>CI:</small> {{$item->ci?$item->ci:'No definido'}} <br>
                                                                        {{$item->people->first_name}} {{$item->people->last_name1}} {{$item->people->last_name2}}
                                                                    </td>
                                                                    <td>{{$item->typeLoan}}</td>
                                                                    <td style="text-align: center"><small>{{ $item->delivered_agentType }}</small> <br> {{ $item->agentDelivered->name }}</td>
                                                                    <td style="text-align: right"> <small>Bs.</small> {{ $item->amountLoan }}</td>      
                                                                    <td style="text-align: right"> <small>Bs.</small> {{ $item->amountPorcentage }}</td>      
                                                                    <td style="text-align: right"> <small>Bs.</small> {{ $item->amountTotal }}</td>      
                                                                </tr>
                                                                @php
                                                                    $amountLoans+= $item->amountLoan;
                                                                    $amountPorcentages+= $item->amountPorcentage;
                                                                    $amountLoanTotal+= $item->amountTotal;
                                                                @endphp
                                                            @empty
                                                                <tr>
                                                                    <td style="text-align: center" valign="top" colspan="9" class="dataTables_empty">No hay datos disponibles en la tabla</td>
                                                                </tr>
                                                            @endforelse
                                                            @if ($amountLoanTotal != 0)
                                                                <tr>
                                                                    <td colspan="6" style="text-align: left"><b>TOTAL</b></td>
                                                                    <td style="text-align: right"> <small>Bs.</small> <b>{{ number_format($amountLoans, 2, '.', '') }}</b></td>     
                                                                    <td style="text-align: right"> <small>Bs.</small> <b>{{ number_format($amountPorcentages, 2, '.', '') }}</b></td>
                                                                    <td style="text-align: right"> <small>Bs.</small> <b>{{ number_format($amountLoanTotal, 2, '.', '') }}</b></td>
                                                                </tr>
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @endif

                                            @if (count($global_cashier['cashier']->loan_payments))
                                                @php
                                                    $loanPayments = $global_cashier['cashier']->loan_payments
                                                    ->where('deleted_at', NULL)
                                                    ->groupBy('transaction_id')
                                                    ->map(function($group){
                                                        return[
                                                            'id' => $group->first()->id,
                                                            'code'=> $group->first()->loanDay->loan->code,
                                                            'created_at' => $group->first()->transaction->created_at, 
                                                            'transaction_id'=> $group->first()->transaction_id,
                                                            'transaction_type'=>$group->first()->transaction->type,
                                                            'register'=> $group->first()->agent->name, 
                                                            'ci' => $group->first()->loanDay->loan->people->ci,
                                                            'full_name' => $group->first()->loanDay->loan->people->first_name.' '.$group->first()->loanDay->loan->people->last_name1.' '.$group->first()->loanDay->loan->people->last_name2,

                                                            'total_amount' => $group->sum('amount')
                                                        ];
                                                    });
                                                @endphp         
                                                <h3 id="h4">Cobros Realizados <label class="label label-success">Ingresos</label></h3>
                                                <div class="table-responsive">
                                                    <table id="dataStyle" class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th style="text-align: center; width:5%">N&deg;</th>                                                    
                                                                <th style="text-align: center; width:5%">N&deg; Transacción</th>                                                    
                                                                <th style="text-align: center">Código</th>
                                                                <th style="text-align: center">Cliente</th>
                                                                <th style="text-align: center">Cobrado Por</th>
                                                                <th style="text-align: center">Total</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @php
                                                                $cont = 1;
                                                                $total_movements = 0;
                                                                $loanPaymentEfectivo=0;
                                                                $loanPaymentQr=0;
                                                            @endphp
                                                            @forelse ($loanPayments as $transaction => $item)
                                                                <tr>
                                                                    <td style="text-align: center">
                                                                        {{$cont}}
                                                                    </td>
                                                                    <td style="text-align: center">
                                                                        {{$item['transaction_id']}}
                                                                    </td>
                                                                    <td style="text-align: center">
                                                                        {{$item['code']}} <br>
                                                                        @if ($item['transaction_type'] != 'Efectivo')
                                                                            <label class="label label-primary">Qr/Transferencia</label>  
                                                                        @else
                                                                            <label class="label label-success">Efectivo</label>  
                                                                        @endif
                                                                    </td>                                                                    
                                                                    <td>
                                                                        <small>CI:</small> {{$item['ci']?$item['ci']:'No definido'}} <br>
                                                                        {{$item['full_name']}}
                                                                    </td>
                                                                    <td style="text-align: center">
                                                                        {{$item['register']}} <br>
                                                                        {{date('d/m/Y h:i:s a', strtotime($item['created_at']))}}<br><small>{{\Carbon\Carbon::parse($item['created_at'])->diffForHumans()}}
                                                                    </td>
                                                                    <td style="text-align: right">{{number_format($item['total_amount'], 2, ',', '.')}}</td>
                                                                </tr>
                                                                @php
                                                                    $total_movements+= $item['total_amount'];
                                                                    if($item['transaction_type'] == 'Efectivo')
                                                                    {
                                                                        $loanPaymentEfectivo+=$item['total_amount'];
                                                                    }
                                                                    else {
                                                                        $loanPaymentQr+=$item['total_amount'];
                                                                    }
                                                                    $cont++;
                                                                @endphp
                                                            @empty
                                                                <tr>
                                                                    <td style="text-align: center" valign="top" colspan="6" class="dataTables_empty">No hay datos disponibles en la tabla</td>
                                                                </tr>
                                                            @endforelse
                                                       
                                                            <tr>
                                                                <td colspan="5" style="text-align: right"><b>TOTAL COBRADO</b></td>
                                                                <td style="text-align: right"> <small>Bs.</small> <b>{{ number_format($total_movements, 2, ',', '.') }}</b></td>     
                                                            </tr>
                                                            <tr>
                                                                <td colspan="5" style="text-align: right"><b>TOTAL QR</b></td>
                                                                <td style="text-align: right"> <small>Bs.</small> <b>{{ number_format($loanPaymentQr, 2, ',', '.') }}</b></td>     
                                                            </tr>
                                                            <tr>
                                                                <td colspan="5  " style="text-align: right"><b>TOTAL EFECTIVO</b></td>
                                                                <td style="text-align: right"> <small>Bs.</small> <b>{{ number_format($loanPaymentEfectivo, 2, ',', '.') }}</b></td>     
                                                            </tr>
                                                    
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Para Prendario --}}
                            @if (count($global_cashier['cashier']->pawn) || count($global_cashier['cashier']->pawnPayment))
                                <div class="col-md-12">
                                    <div class="panel panel-bordered">
                                        <div class="panel-body">
                                            <h3 id="h3" style="text-align: center">Prendario</h3>
                                            @if (count($global_cashier['cashier']->pawn))
                                                <h3 id="h4">Prestamos Entregados <label class="label label-danger">Egresos</label></h3>
                                                <div class="table-responsive">                                                    
                                                    <table id="dataStyle" class="table table-bordered table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th width="2%">N&deg;</th>
                                                                <th style="width: 5%">Codigo</th>
                                                                <th>Nombre Completo</th>
                                                                <th>Actículos</th>
                                                                <th>Fechas</th>
                                                                <th>Detalles</th>
                                                                <th style="text-align: center">Registrado por</th>
                                                                <th style="text-align: center">Total</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @php
                                                                $cont = 1;
                                                                $pawnTotal=0;
                                                                $subtotal = 0;
                                                                $subtotalDollar=0;                        
                                                            @endphp
                                                            @foreach ($global_cashier['cashier']->pawn->where('deleted_at', NULL) as $item)
                                                                <tr>
                                                                    <td>{{ $cont }}</td>
                                                                    <td>
                                                                        {{ $item->code }}
                                                                    </td>
                                                                    <td>
                                                                        <small>CI:</small> {{ $item->person->ci}} <br>
                                                                        <p>{{ $item->person->first_name}} {{ $item->person->last_name1}} {{ $item->person->last_name2}}</p>
                                                                        
                                                                    </td>
                                                                    <td>
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
                                                                                <li style="font-size: 12px">{{ floatval($detail->quantity) == intval($detail->quantity) ? intval($detail->quantity) : $detail->quantity }}
                                                                                    {{ $detail->type->unit }} {{ $detail->type->name }} a {{ floatval($detail->price) == intval($detail->price) ? intval($detail->price) : $detail->price }}
                                                                                    <span style="font-size: 10px">Bs.</span>
                                                                                </li>
                                                                                @php
                                                                                    $subtotal += $detail->amountTotal;
                                                                                    $subtotalDollar += $detail->dollarTotal;
                                                                                @endphp
                                                                            @endforeach
                                                                        </ul>
                                                                    </td>
                                                                    <td style="width: 150px">
                                                                        <table style="width: 100%">
                                                                            <tr>
                                                                                <td><b>Solicitud</b></td>
                                                                                <td class="text-right">{{ date('d', strtotime($item->date)).'/'.$meses[intval(date('m', strtotime($item->date)))].'/'.date('Y', strtotime($item->date)) }}</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><b>Entrega</b></td>
                                                                                <td class="text-right">
                                                                                    @if ($item->dateDelivered)
                                                                                        {{ date('d', strtotime($item->dateDelivered)).'/'.$meses[intval(date('m', strtotime($item->dateDelivered)))].'/'.date('Y', strtotime($item->dateDelivered)) }}
                                                                                    @endif
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><b>Devolución</b></td>
                                                                                <td class="text-right">
                                                                                    @if ($item->date_limit && ($item->status == 'entregado' || $item->status == 'recogida'))
                                                                                        {{ date('d', strtotime($item->date_limit)).'/'.$meses[intval(date('m', strtotime($item->date_limit)))].'/'.date('Y', strtotime($item->date_limit)) }}
                                                                                    @endif
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                    @php
                                                                        $interest_rate = $subtotal * ($item->interest_rate /100);
                                                                        $payment = $item->payments->sum('amount');
                                                                        $debt = $subtotal + $interest_rate - $payment;
                                                                    @endphp
                                                                    <td style="width: 150px">
                                                                        <table style="width: 100%">
                                                                            <tr>
                                                                                <td colspan="2" class="text-center"><i class="fa-solid fa-dollar-sign"></i> {{ $subtotalDollar }}<span style="font-size: 10px"></span></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><b>Prestamos</b></td>
                                                                                <td class="text-right">{{ $subtotal }}<span style="font-size: 10px">Bs.</span></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><b>Interes</b></td>
                                                                                <td class="text-right">{{ $interest_rate }}<span style="font-size: 10px">Bs.</span></td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                    
                                                                    <td style="text-align: center">
                                                                        {{ $item->user ? $item->user->name : '' }} <br>
                                                                        {{ date('d/', strtotime($item->created_at)).$meses[intval(date('m', strtotime($item->created_at)))].date('/Y h:i:s a', strtotime($item->created_at)) }} <br>
                                                                        <small>{{ \Carbon\Carbon::parse($item->created_at)->diffForHumans() }}</small>
                                                                    </td>
                                                                    <td style="text-align: right">
                                                                        @if ($item->deleted_at)
                                                                           <del style="color: red">{{ number_format($subtotal, 2, '.', '') }}</del>
                                                                        @else
                                                                           {{ number_format($subtotal, 2, '.', '') }}
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                                @php
                                                                    $cont++;
                                                                    if (!$item->deleted_at) {
                                                                        $pawnTotal = $pawnTotal + $subtotal;
                                                                    }
                                                                @endphp
                                                            @endforeach
                                                            <tr>
                                                                <td colspan="7" style="text-align: left"><b>TOTAL</b></td>
                                                                <td style="text-align: right"><b>{{ number_format($pawnTotal, 2, '.', '') }}</b></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @endif
                                            
                                            @if (count($global_cashier['cashier']->pawnPayment))
                                                @php
                                                    $pawnPayments = $global_cashier['cashier']->pawnPayment
                                                    ->where('deleted_at', null)
                                                    ->groupBy('transaction_id')
                                                    ->map(function ($group) {
                                                        return [
                                                            'id' => $group->first()->id, 
                                                            'code'=> $group->first()->pawnRegister->code,
                                                            'codeManual'=>$group->first()->pawnRegister->codeManual,
                                                            'created_at' => $group->first()->transaction->created_at,
                                                            'deleted_at' => $group->first()->transaction->deleted_at,
                                                            'transaction_id'=> $group->first()->transaction->id,
                                                            'transaction_type'=>$group->first()->transaction->type,
                                                            'register'=> $group->first()->agent->name, 
                                                            'ci' => $group->first()->pawnRegister->person->ci, 
                                                            'full_name' => $group->first()->pawnRegister->person->first_name.' '.$group->first()->pawnRegister->person->last_name1.' '.$group->first()->pawnRegister->person->last_name2, // Obtener created_at de la transacción
                                                            'total_amount' => $group->sum('amount')
                                                        ];
                                                    });
                                                @endphp                 
                                                <h3 id="h4">Cobros Realizados <label class="label label-success">Ingresos</label></h3>
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
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @php
                                                                $cont = 1;
                                                                $total_movementsPawn = 0;
                                                            @endphp
                                                            @forelse ($pawnPayments as $transaction_id=>$item)
                                                                <tr>
                                                                    <td>{{ $cont }}</td>
                                                                    <td style="text-align: center">{{$item['transaction_id']}}</td>
                                                                    <td style="text-align: center">{{$item['code']}} <br>
                                                                        @if ($item['transaction_type'] != 'Efectivo')
                                                                            <label class="label label-primary">Qr/Transferencia</label>  
                                                                        @else
                                                                            <label class="label label-success">Efectivo</label> 
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
                                                                </tr>
                                                                @php
                                                                    $cont++;
                                                                    $total_movementsPawn += $item['total_amount']
                                                                @endphp
                                                            @empty
                                                                <tr>
                                                                    <td style="text-align: center" valign="top" colspan="6" class="dataTables_empty">No hay datos disponibles en la tabla</td>
                                                                </tr>
                                                            @endforelse
                                                            <tr>
                                                                <td colspan="5" class="text-left">TOTAL COBROS</td>
                                                                <td style="text-align: right"><b>{{ number_format($total_movementsPawn, 2, ',', '.') }}</b></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Para Ventas --}}
                            @if (count($global_cashier['cashier']->salePayment))
                                <div class="col-md-12">
                                    <div class="panel panel-bordered">
                                        <div class="panel-body">
                                            <h3 id="h3" style="text-align: center">Ventas al Credito/Contado</h3>
                                                <h3 id="h4">Ventas Realizadas <label class="label label-success">Ingresos</label></h3>
                                                <div class="table-responsive">                                                    
                                                    <table id="dataStyle" class="table table-bordered table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>N&deg;</th>
                                                                <th style="text-align: center; width:10%">N&deg; Transacción</th>                                                    
                                                                <th style="text-align: center">Código</th>
                                                                <th style="text-align: center">Fecha Pago</th>
                                                                <th style="text-align: center">Cliente</th>
                                                                <th style="text-align: center">Atendido Por</th>
                                                                <th style="text-align: center; width:8%">Monto Cobrado</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @php
                                                                $cont = 1;
                                                                $total_movementsSale = 0;
                                                                $total_movementsSale_qr = 0;
                                                                $total_movementsSale_deleted = 0;
                                                            @endphp

                                                            @foreach ($global_cashier['cashier']->salePayment->where('deleted_at', NULL) as $item)
                                                                <tr>
                                                                    <td>{{ $cont }}</td>
                                                                    <td style="text-align: center">
                                                                        {{$item->transaction->transaction}} <br>
                                                                        {{$item->sale->typeSale=='credito'?'Venta al Credito':'Venta al Contado'}}
                                                                    </td>
                                                                    <td style="text-align: center">{{$item->sale->code}} <br>
                                                                        @if ($item->transaction->deleted_at)
                                                                            <label class="label label-danger">Transaccion eliminada</label>                                                        
                                                                        @endif
                                                                        @if ($item->transaction->type != 'Efectivo')
                                                                            <label class="label label-primary">Qr/Transferencia</label>  
                                                                        @else
                                                                            <label class="label label-success">Efectivo</label> 
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
                                                                        @if ($item->deleted_at)
                                                                        <del style="color: red">{{ number_format($item->amount, 2, '.', '') }}</del>
                                                                        @else
                                                                        {{ number_format($item->amount, 2, '.', '') }}
                                                                        @endif
                                                                    </td>
                                                                </tr>

                                                                @php
                                                                    $cont++;
                                                                    if(!$item->deleted_at){
                                                                        if($item->transaction->type == 'Efectivo'){
                                                                            $total_movementsSale += $item->amount;
                                                                        }else{
                                                                            $total_movementsSale_qr += $item->amount;
                                                                        }
                                                                    }else{
                                                                        $total_movementsSale_deleted += $item->amount;
                                                                    }
                                                                @endphp
                                                            @endforeach
                                                            <tr>
                                                                <td colspan="6" style="text-align: left"><b>TOTAL</b></td>
                                                                <td style="text-align: right"><b>{{ number_format($total_movementsSale, 2, ',', '.') }}</b></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Para gastos adicionales --}}
                            @if (count($global_cashier['cashier']->movements->where('type', 'ingreso')))
                                <div class="col-md-12">
                                    <div class="panel panel-bordered">
                                        <div class="panel-body">
                                            <div class="table-responsive">
                                                <div class="col-md-12">
                                                    <h3 id="h4">Dinero Asignado <label class="label label-success">Ingresos</label></h3>
                                                </div>
                                                <table id="dataStyle" class="table table-bordered table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Id</th>
                                                            <th>Fecha y Hora de Registro</th>
                                                            <th>Registrado Por</th>
                                                            <th>Detalle</th>
                                                            <th style="text-align: center">Monto</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php
                                                            $gastos_totales = 0;
                                                        @endphp
                                                        @forelse ($global_cashier['cashier']->movements->where('type', 'ingreso')->where('deleted_at', NULL) as $item)
                                                            <tr>
                                                                <td>{{ $item->id }}</td>
                                                                <td>{{ date('d/m/Y h:i:s a', strtotime($item->created_at))}}<br><small>{{\Carbon\Carbon::parse($item->created_at)->diffForHumans() }}</td>
                                                                <td>{{ $item->user->name }}</td>
                                                                <td>{{ $item->description }} <br>
                                                                    @if ($item->transferCashier_id)
                                                                        <label class="label label-success">Trasferencia de Caja</label>
                                                                    @endif
                                                                </td>
                                                                <td style="text-align: right"> {{ number_format($item->amount, 2, ',', '.') }}</td>      
                                                            </tr>
                                                            @php
                                                                $gastos_totales += $item->amount;
                                                            @endphp
                                                        @empty
                                                            <tr>
                                                                <td class="text-center" valign="top" colspan="9" class="dataTables_empty">No hay datos disponibles en la tabla</td>
                                                            </tr>
                                                        @endforelse
                                                        @if ($gastos_totales > 0)
                                                            <tr>
                                                                <td colspan="4"><b>TOTAL</b></td>
                                                                <td class="text-right"> <small>Bs.</small> <b>{{ number_format($gastos_totales, 2, ',', '.') }}</b></td>     
                                                            </tr>
                                                        @endif
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- @if (count($global_cashier['cashier']->movements->where('type', 'egreso'))) --}}
                                <div class="col-md-12">
                                    <div class="panel panel-bordered">
                                        <div class="panel-body">
                                            <div class="table-responsive">
                                                <div class="col-md-12">
                                                    <h3 id="h4">Traspaso de Caja <label class="label label-danger">Egresos</label></h3>
                                                </div>
                                                <table id="dataStyle" class="table table-bordered table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Id</th>
                                                            <th>Fecha y Hora de Registro</th>
                                                            <th>Registrado Por</th>
                                                            <th>Detalle</th>
                                                            <th style="text-align: center">Estado</th>
                                                            <th style="text-align: center">Monto</th>
                                                            <th style="text-align: center">Acciones</th>

                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php
                                                            $transfer_totales = 0;
                                                        @endphp
                                                        @forelse ($global_cashier['cashier']->movements->where('type', 'egreso')->where('description', '!=', 'Pagos de sueldo')->where('deleted_at', NULL)->where('transferCashier_id', '!=', null) as $item)
                                                            <tr>
                                                                <td>{{ $item->id }}</td>
                                                                <td>{{ date('d/m/Y h:i:s a', strtotime($item->created_at))}}<br><small>{{\Carbon\Carbon::parse($item->created_at)->diffForHumans() }}</td>
                                                                <td>{{ $item->user->name }}</td>
                                                                <td>{{ $item->description }}</td>
                                                                <td style="text-align: center">
                                                                    @if ($item->status == 'Aceptado')
                                                                        <label class="label label-success">Aceptado</label>  
                                                                    @endif
                                                                    @if ($item->status == 'Pendiente')
                                                                        <label class="label label-primary">Pendiente</label>  
                                                                    @endif
                                                                    @if ($item->status == 'Rechazado')
                                                                        <label class="label label-dark">Rechazado</label>  
                                                                    @endif
                                                                </td>
                                                                <td style="text-align: right"> {{ number_format($item->amount, 2, '.', '') }}</td>      
                                                                <td style="text-align: right">
                                                                    @if ($item->status == 'Pendiente')
                                                                        <button title="Eliminar transacción" class="btn btn-sm btn-danger delete" 
                                                                            onclick="deleteItem('{{ route('cashiers-amount-transfer.delete', ['cashier_id'=>$item['cashier_id'],'transfer_id'=>$item['id']]) }}')" data-toggle="modal" data-target="#delete-modal">
                                                                                <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm"></span>
                                                                        </button>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            @php
                                                                $transfer_totales += $item->amount;
                                                                // dump($item);
                                                            @endphp
                                                        @empty
                                                            <tr>
                                                                <td class="text-center" valign="top" colspan="7" class="dataTables_empty">No hay datos disponibles en la tabla</td>
                                                            </tr>
                                                        @endforelse
                                                        <tr>
                                                            <td colspan="5" style="text-align: right">TOTAL</td>
                                                            <td style="text-align: right"><b>{{ number_format($transfer_totales, 2, ',', '.') }}</b></td>
                                                            <td></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="panel panel-bordered">
                                        <div class="panel-body">
                                            <div class="table-responsive">
                                                <div class="col-md-12">
                                                    <h3 id="h4">Gastos Realizados <label class="label label-danger">Egresos</label></h3>
                                                </div>
                                                <table id="dataStyle" class="table table-bordered table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Id</th>
                                                            <th>Fecha y Hora de Registro</th>
                                                            <th>Registrado Por</th>
                                                            <th>Detalle</th>
                                                            <th style="text-align: center">Monto</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php
                                                            $gastos_totales = 0;
                                                        @endphp
                                                        @forelse ($global_cashier['cashier']->movements->where('type', 'egreso')->where('description', '!=', 'Pagos de sueldo')->where('deleted_at', NULL)->where('transferCashier_id', null) as $item)
                                                            <tr>
                                                                <td>{{ $item->id }}</td>
                                                                <td>{{ date('d/m/Y h:i:s a', strtotime($item->created_at))}}<br><small>{{\Carbon\Carbon::parse($item->created_at)->diffForHumans() }}</td>
                                                                <td>{{ $item->user->name }}</td>
                                                                <td>{{ $item->description }}</td>
                                                                <td style="text-align: right"> {{ number_format($item->amount, 2, ',', '.') }}</td>      
                                                            </tr>
                                                            @php
                                                                $gastos_totales += $item->amount;
                                                            @endphp
                                                        @empty
                                                            <tr>
                                                                <td class="text-center" valign="top" colspan="9" class="dataTables_empty">No hay datos disponibles en la tabla</td>
                                                            </tr>
                                                        @endforelse
                                                        @if ($gastos_totales > 0)
                                                            <tr>
                                                                <td colspan="4"><b>TOTAL</b></td>
                                                                <td class="text-right"> <small>Bs.</small> <b>{{ number_format($gastos_totales, 2, ',', '.') }}</b></td>     
                                                            </tr>
                                                        @endif
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            {{-- @endif --}}
                        </div>  
                    @endif                  
                @else
                    <div class="row" id="rowCashierOpen">
                        <div class="col-md-12">
                            <div class="panel panel-bordered">
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h2 id="h2"><i class="fa-solid fa-wallet"></i> {{ $cashier->title }}</h2>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6" style="margin-top: 50px">
                                            <table class="table table-hover" id="dataStyle">
                                                <thead>
                                                    <tr>
                                                        <th>Corte</th>
                                                        <th>Cantidad</th>
                                                        <th>Sub Total</th>
                                                    </tr>
                                                </thead>
                                                @php
                                                    $cash = ['200', '100', '50', '20', '10', '5', '2', '1', '0.5', '0.2', '0.1'];
                                                    $total = 0;
                                                @endphp
                                                <tbody>
                                                    @foreach ($cash as $item)
                                                    <tr>
                                                        <td><h4 style="margin: 0px"><img src=" {{ url('images/cash/'.$item.'.jpg') }} " alt="{{ $item }} Bs." width="70px"> {{ $item }} Bs. </h4></td>
                                                        <td>
                                                            @php
                                                                $details = null;
                                                                if($cashier->vault_details){
                                                                    $details = $cashier->vault_details->cash->where('cash_value', $item)->first();
                                                                }
                                                            @endphp
                                                            {{ $details ? $details->quantity : 0 }}
                                                        </td>
                                                        <td>
                                                            {{ $details ? number_format($details->quantity * $item, 2, '.', '') : 0 }}
                                                            <input type="hidden" name="cash_value[]" value="{{ $item }}">
                                                            <input type="hidden" name="quantity[]" value="{{ $details ? $details->quantity : 0 }}">
                                                        </td>
                                                        @php
                                                        if($details){
                                                            $total += $details->quantity * $item;
                                                        }
                                                        @endphp
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <br>
                                            <div class="alert alert-info">
                                                <strong>Información:</strong>
                                                <p>Si la cantidad de de cortes de billetes coincide con la cantidad entregada por parte del administrador(a) de vóbeda, acepta la apertura de caja, caso contrario puedes rechazar la apertura.</p>
                                            </div>
                                            <br>
                                            <h2 id="h3" class="text-right">Total en caja: Bs. {{ number_format($total, 2, '.', '') }} </h2>
                                            <br>
                                            <div class="text-right">
                                                <button type="button" data-toggle="modal" data-target="#refuse_cashier-modal" class="btn btn-danger">Rechazar apertura <i class="voyager-x"></i></button>
                                                <button type="button" data-toggle="modal" data-target="#open_cashier-modal" class="btn btn-success">Aceptar apertura <i class="voyager-key"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Aceptar apertura de caja --}}
                    <form action="{{ route('cashiers.change.status', ['cashier' => $cashier->id]) }}" method="post">
                        @csrf
                        <input type="hidden" name="status" value="abierta">
                        <div class="modal fade" tabindex="-1" id="open_cashier-modal" role="dialog">
                            <div class="modal-dialog modal-success">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                                        <h4 class="modal-title"><i class="fa-solid fa-wallet"></i> Aceptar apertura de caja</h4>
                                    </div>
                                    <div class="modal-body">
                                        <p class="text-muted"></p>
                                        <small>Esta a punto de aceptar que posee todos los cortes de billetes descritos en la lista, ¿Desea continuar?</small>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-success">Si, aceptar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    {{-- Rechazar apertura de caja --}}
                    <form action="{{ route('cashiers.change.status', ['cashier' => $cashier->id]) }}" method="post">
                        @csrf
                        <input type="hidden" name="status" value="cerrada">
                        <div class="modal modal-danger fade" tabindex="-1" id="refuse_cashier-modal" role="dialog">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                                        <h4 class="modal-title"><i class="fa-solid fa-wallet"></i> Rechazar apertura de caja</h4>
                                    </div>
                                    <div class="modal-body">
                                        <small>Esta a punto de rechazar la apertura de caja, ¿Desea continuar?</small>
                                        <p class="text-muted"></p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-danger">Si, rechazar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                @endif
            @else
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-bordered">
                            <div class="panel-body text-center">
                                <h2>Tienes una caja esperando por confimación de cierre</h2>
                                <a href="#" style="margin: 0px" data-toggle="modal" data-target="#cashier-revert-modal" class="btn btn-success"><i class="voyager-key"></i> Reabrir caja</a>
                                <a href="{{ route('cashiers.print', $cashier->id) }}" style="margin: 0px" class="btn btn-danger" target="_blank"><i class="fa fa-print"></i> Imprimir</a>
                            </div>
                        </div>
                    </div>
                </div>
                <form action="{{ route('cashiers.close.revert', ['cashier' => $cashier->id]) }}" method="post">
                    @csrf
                    <div class="modal fade" tabindex="-1" id="cashier-revert-modal" role="dialog">
                        <div class="modal-dialog modal-success">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title"><i class="voyager-key"></i> Reabrir Caja</h4>
                                </div>
                                <div class="modal-body">
                                    <p class="text-muted">Si reabre la caja deberá realizar el arqueo nuevamente, ¿Desea continuar?</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-success">Si, reabrir</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            @endif
        @else
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-bordered">
                        <div class="panel-body">
                            <h1 class="text-center">No tienes caja abierta</h1>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="modal modal-danger fade" data-backdrop="static" tabindex="-1" id="delete-modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="voyager-trash"></i> Desea eliminar el siguiente registro?</h4>
                </div>
                <div class="modal-footer">
                    <form action="#" id="delete_form" method="POST">
                        {{ method_field('DELETE') }}
                        {{ csrf_field() }}    
                            <div class="text-center" style="text-transform:uppercase">
                                <i class="voyager-trash" style="color: red; font-size: 5em;"></i>
                                <br>
                                
                                <p><b>Desea eliminar el siguiente horario?</b></p>
                            </div>
                        <input type="submit" class="btn btn-danger pull-right delete-confirm" value="Sí, eliminar">
                    </form>
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal modal-success fade" data-backdrop="static" tabindex="-1" id="successTransaction-modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="voyager-trash"></i> Desea aceptar la transferencia?</h4>
                </div>
                <div class="modal-footer">
                    <form action="#" id="successTransaction_form" method="GET">
                        {{ csrf_field() }}    
                            <div class="text-center" style="text-transform:uppercase">                                
                                <p><b>Deseea aceptar el dinero?</b></p>
                            </div>
                        <input type="submit" class="btn btn-success pull-right" value="Sí, aceptar">
                    </form>
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal modal-dark fade" data-backdrop="static" tabindex="-1" id="declineTransaction-modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="voyager-trash"></i> Desea rechazarla transferencia?</h4>
                </div>
                <div class="modal-footer">
                    <form action="#" id="declineTransaction_form" method="GET">
                        {{ csrf_field() }}    
                            <div class="text-center" style="text-transform:uppercase">                                
                                <p><b>Deseea rechazar el dinero?</b></p>
                            </div>
                        <input type="submit" class="btn btn-dark pull-right" value="Sí, rechazar">
                    </form>
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    @include('cashier.partials.agregar-gasto-modal')
@stop
@section('css')
    <style>
        .icon{
            font-size: 35px
        }
    </style>
@endsection

@section('javascript')
    <script>
        function deleteItem(url){
            $('#delete_form').attr('action', url);
        }

        function successItem(url){
            $('#successTransaction_form').attr('action', url);
        }
        function declineItem(url){
            $('#declineTransaction_form').attr('action', url);
        }
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.1.4/Chart.bundle.min.js"></script>
        @if ($cashier)
            @if ($cashier->status == 'abierta')
                <script>
                    $(document).ready(function(){

                        // $('#select-cashier_movement_category_id').select2({dropdownParent: $('#agregar-gasto-modal')});

                        const data = {
                            labels: [
                                'Dinero Prestado',
                                'Gastos Adicionales',
                                'Dinero Disponible en Caja',
                            ],
                            datasets: [{
                                label: 'My First Dataset',
                                data: ["{{ $global_cashier['amountEgres'] }}","{{ $global_cashier['cashierOut'] }}", "{{ $global_cashier['cashierIn'] }}"],
                                backgroundColor: [
                                'rgb(12, 55, 101)',
                                'rgb(255, 0, 0)',
                                'rgb(50, 205, 50)',
                                ],                                
                                hoverOffset: 4
                            }]
                        };
                        const config = {
                            type: 'pie',
                            data: data,
                        };
                        var myChart = new Chart(
                            document.getElementById('myChart'),
                            config
                        );

                        $('.btn-agregar-gasto').click(function(){
                            let cashier_id = $(this).data('cashier_id');
                            $('#form-agregar-gasto input[name="cashier_id"]').val(cashier_id);
                        });
                    });
                </script>
            @endif
        @endif

    @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('gerente') || auth()->user()->hasRole('administrador'))
        <script src="{{ asset('js/plugins/chart.min.js') }}"></script>
        <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
        <script>
            $(document).ready(function(){
                // ==============================================
                let incomePerson = @json($incomePerson);
                labels = [];
                values = [];
                color = [];

                incomePerson.map(item => {
                    labels.push(item.agent.name);
                    values.push(parseInt(item.amount));

                    color.push(colorRGB());
                });

                var data = {
                    labels,
                    datasets: [{
                        label: 'Recolección Diaria',
                        data: values,
                        backgroundColor: color,
                        hoverOffset: 4
                    }]
                };
                var config = {
                    type: 'doughnut',
                    data
                };
                var myChart = new Chart(
                    document.getElementById('income-chart'),
                    config
                );

                //================================================================

                var data = {
                    labels: [
                                'Dinero Prestado',
                                'Dinero Cobrado',
                            ],
                    datasets: [{
                        label: 'Productos más vendidos',
                        data: ["{{$moneyLoan}}", "{{$moneyRecaudado}}"],
                        backgroundColor: [
                            'rgba(231,76,60)',
                            'rgba(39, 174, 96, 1)',
                        ],
                        hoverOffset: 4
                    }]
                };
                var config = {
                    type: 'doughnut',
                    data
                };
                var myChart = new Chart(
                    document.getElementById('doughnut-chart'),
                    config
                );
            });

            function generarNumero(numero){
                return (Math.random()*numero).toFixed(0);
            }

            function colorRGB(){
                var coolor = "("+generarNumero(255)+"," + generarNumero(255) + "," + generarNumero(255) +")";
                return "rgb" + coolor;
            }
        </script>
    @endif
@stop
