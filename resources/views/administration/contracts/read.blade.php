@extends('voyager::master')

@section('page_title', 'Ver contrato')
@if (auth()->user()->hasPermission('read_contracts'))
@section('page_header')
    <h1 id="titleHead" class="page-title">
        <i class="fa-solid fa-file-signature"></i> Viendo Contrato &nbsp;
        <a href="{{ route('contracts.index') }}" class="btn btn-warning">
            <span class="glyphicon glyphicon-list"></span>&nbsp;
            Volver a la lista
        </a>
    </h1>
@stop


@section('content')
    <div class="page-content read container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered" style="padding-bottom:5px;">
                    <div class="panel-body">        
                        <h5 class="panel-title">Datos Personales</h5>
                        {{-- <h3 class="panel-title">Detalle de los pagos adelantados</h3> --}}
                        
                        <div class="row">
                            
                            <div class="col-md-6">
                                <div class="panel-heading" style="border-bottom:0;">
                                    <h3 class="panel-title">CI</h3>
                                </div>
                                <div class="panel-body" style="padding-top:0;">
                                    <p>{{ $contract->people->ci }}</p> 
                                </div>
                                <hr style="margin:0;">
                            </div>
                            <div class="col-md-6">
                                <div class="panel-heading" style="border-bottom:0;">
                                    <h3 class="panel-title">Beneficiario</h3>
                                </div>
                                <div class="panel-body" style="padding-top:0;">
                                    <p>{{ $contract->people->first_name }} {{ $contract->people->last_name1 }} {{ $contract->people->last_name2 }}</p> 
                                </div>
                                <hr style="margin:0;">
                            </div>
                            <div class="col-md-6">
                                <div class="panel-heading" style="border-bottom:0;">
                                    <h3 class="panel-title">Celular</h3>
                                </div>
                                <div class="panel-body" style="padding-top:0;">
                                    <p>{{ $contract->people->cell_phone }}</p> 
                                </div>
                                <hr style="margin:0;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered" style="padding-bottom:5px;">
                    <div class="panel-body">        
                        <h5 class="panel-title">Detalles del Contrato</h5>
                        
                        <div class="row">
                            
                            <div class="col-md-6">
                                <div class="panel-heading" style="border-bottom:0;">
                                    <h3 class="panel-title">Cargo</h3>
                                </div>
                                <div class="panel-body" style="padding-top:0;">
                                    <p>{{ $contract->work }}</p>
                                </div>
                                <hr style="margin:0;">
                            </div>

                            <div class="col-md-6">
                                <div class="panel-heading" style="border-bottom:0;">
                                    <h3 class="panel-title">Sueldo</h3>
                                </div>
                                <div class="panel-body" style="padding-top:0;">
                                    <p> Bs. {{ $contract->salary }}</p>
                                </div>
                                <hr style="margin:0;">
                            </div>
                            <div class="col-md-4">
                                <div class="panel-heading" style="border-bottom:0;">
                                    <h3 class="panel-title">Fecha Inicio</h3>
                                </div>
                                <div class="panel-body" style="padding-top:0;">
                                    <p>{{ date("d-m-Y", strtotime($contract->dateStart)) }}</p> 
                                </div>
                                <hr style="margin:0;">
                            </div>
                            <div class="col-md-4">
                                <div class="panel-heading" style="border-bottom:0;">
                                    <h3 class="panel-title">Fecha Finalización</h3>
                                </div>
                                <div class="panel-body" style="padding-top:0;">
                                    <p>{{ date("d-m-Y", strtotime($contract->dateFinish)) }}</p> 
                                </div>
                                <hr style="margin:0;">
                            </div>
                            <div class="col-md-4">
                                <div class="panel-heading" style="border-bottom:0;">
                                    <h3 class="panel-title">Estado</h3>
                                </div>
                                <div class="panel-body" style="padding-top:0;">
                                    @if ($contract->deleted_at != NULL)
                                        <label class="label label-danger">Eliminado</label>                            
                                    @endif  
                                    {{-- @if ($contract->advancement == 0 && $contract->status == 'activo' && $contract->deleted_at = NULL)
                                        <label class="label label-success">Sin Adelantos</label> <br>
                                    @endif --}}
                                    @if($contract->advancement > 0 && $contract->status == 'activo')
                                        <label class="label label-danger"><small>Bs.</small> {{$contract->advancement}}</label><br>
                                    @endif

                                    @if ($contract->status == 'pendiente' && $contract->deleted_at == NULL)
                                        <label class="label label-danger">PENDIENTE</label>                            
                                    @endif
                                    @if ($contract->status == 'finalizado'&& $contract->deleted_at == NULL)
                                        <label class="label label-dark">FINALIZADO</label>                            
                                    @endif
                                    @if ($contract->status == 'aprobado'&& $contract->deleted_at == NULL)
                                        <label class="label label-success">EN CURSO</label>                            
                                    @endif
                                    @if ($contract->status == 'rechazado' && $contract->deleted_at == NULL)
                                        <label class="label label-danger">RECHAZADO</label>                            
                                    @endif
                                </div>
                                <hr style="margin:0;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered" style="padding-bottom:5px;">
                    <div class="panel-body">        

                        <div class="row table-responsive">
                            <div class="col-md-7">
                                <div class="row">
                                    @if (!$cashier)
                                        <div class="alert alert-warning">
                                            <strong>Advertencia:</strong>
                                            <p>No puedes dar un adelanto debido a que no tiene una caja activa.</p>
                                        </div>
                                    @endif
                                    <div class="col-md-8">
                                        <h3 id="titleHead" class="panel-title">
                                            <i class="fa-solid fa-file-invoice"></i> Adelantos
                                        </h3>
                                    </div>  
                                    <div class="col-md-4 text-right" style="padding-top: 20px">
                                        @if (auth()->user()->hasPermission('add_contractsAdvancement') && $cashier)
                                            @if ($contract->deleted_at == NULL && $contract->status == 'aprobado')
                                                <button class="btn btn-success btn-agregar-gasto" data-toggle="modal" data-target="#agregar-advancement-modal"><i class="voyager-plus"></i> Agregar</button>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                            
                                <table id="dataStyle" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>N&deg;</th>
                                            <th>Periodo</th>
                                            <th>Fecha</th>
                                            <th>Monto</th>
                                            <th>Observaciones</th>
                                            <th>Registrado por</th>
                                            <th class="text-right">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $cont = 1;
                                        @endphp
                                        @forelse ($contract->contractAdvancement as $item)
                                            <tr >
                                                <td>{{$item->id}}</td>
                                                <td>{{$item->periodMonth}}-{{$item->periodYear}}</td>
                                                <td style="text-align: center">
                                                    <small>{{date('d/m/Y H:i:s', strtotime($item->created_at))}}<br><small>{{\Carbon\Carbon::parse($item->created_at)->diffForHumans()}}</small>
                                                </td>
                                                <td>
                                                    BS. {{$item->advancement}}
                                                </td>
                                                <td>
                                                    {{$item->observation}}     
                                                </td>
                                                <td style="text-align: center">{{$item->register_agentType}} <br> {{$item->registerUser->name}}</td>

                                                <td class="no-sort no-click bread-actions text-right">
                                                    @if(!$item->deleted_at)
                                                        {{-- <a href="{{ route('loans.payment.notification', $item->id) }}" data-phone="{{ $item->id }}" class="btn btn-success btn-notification" title="Reenviar reibo">
                                                            <i class="fa fa-paper-plane"></i>
                                                        </a> --}}
                                                        <a onclick="printAdvancementMoney({{$item->contract_id}}, {{$item->id}})" title="Imprimir"  class="btn btn-dark">
                                                            <i class="glyphicon glyphicon-print"></i>
                                                        </a>
                                                    @endif 
                        
                                                    @if ($item->deleted_at == NULL && $item->status != 'rechazado' && $item->spreadsheet==0)
                                                        <a title="Borrar" class="btn btn-sm btn-danger delete" onclick="deleteItem('{{ route('contracts-advancement.destroy', ['id' => $item->id]) }}')" data-toggle="modal" data-target="#delete-modal">
                                                            <i class="voyager-trash"></i>
                                                        </a>
                                                    @endif    
                                                    @if ($item->deleted_at)
                                                        <label class="label label-danger">ELIMINADO</label>                            
                                                    @endif                                                     
                                                </td>
                                                
                        
                                                
                                                
                            
                                            </tr>
                                        @empty
                                            <tr>
                                                <td style="text-align: center" valign="top" colspan="10" class="dataTables_empty">No hay datos disponibles en la tabla</td>
                                            </tr>
                                        @endforelse
                                        
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-5">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h3 id="titleHead" class="panel-title">
                                            <i class="fa-solid fa-money-check-dollar"></i> Pagos Mensuales
                                        </h3>
                                    </div> 
                                </div>

                            
                                <table id="dataStyle" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>N&deg;</th>
                                            <th>Periodo</th>
                                            <th>Fecha</th>
                                            <th class="text-right">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $cont = 1;
                                        @endphp
                                        @forelse ($contract->spreadsheetContract as $item)
                                            <tr >
                                                <td>{{$item->id}}</td>
                                                <td>{{$item->spreadsheet->month}}-{{$item->spreadsheet->year}}</td>
                                                <td style="text-align: center">
                                                    <small>{{date('d/m/Y H:i:s', strtotime($item->paidDate))}}</small><br><small>{{\Carbon\Carbon::parse($item->paidDate)->diffForHumans()}}</small>
                                                </td>
                                             

                                                <td class="no-sort no-click bread-actions text-right">
                                                    @if(!$item->deleted_at)
                                                        <a onclick="openPrinf({{$item->id}})" title="Imprimir"  class="btn btn-dark">
                                                            <i class="glyphicon glyphicon-print"></i>
                                                        </a>
                                                    @endif  
                                                                                                  
                                                </td>                                            
                                            </tr>
                                        @empty
                                            <tr>
                                                <td style="text-align: center" valign="top" colspan="4" class="dataTables_empty">No hay datos disponibles en la tabla</td>
                                            </tr>
                                        @endforelse
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row table-responsive">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h3 id="titleHead" class="panel-title">
                                            <i class="fa-solid fa-file"></i> Licencias
                                        </h3>
                                    </div>  
                                    <div class="col-md-4 text-right" style="padding-top: 20px">
                                        @if (auth()->user()->hasPermission('add_contractsLicense'))
                                            @if ($contract->deleted_at == NULL && $contract->status == 'aprobado')
                                                <button class="btn btn-success btn-agregar-gasto" data-toggle="modal" data-target="#license-modal"><i class="voyager-plus"></i> Agregar</button>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                <table id="dataStyle" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>N&deg;</th>
                                            <th>Fecha</th>
                                            <th>Archivo</th>
                                            <th>Descripción</th>
                                            <th>Registrado por</th>
                                            <th class="text-right">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $cont = 1;
                                        @endphp
                                        @forelse ($licenses as $item)
                                            <tr >
                                                <td>{{$item->id}}</td>
                                                <td>
                                                    <small>{{date('d/m/Y', strtotime($item->dateStart))}} - {{date('d/m/Y', strtotime($item->dateFinish))}}</small>
                                                </td>
                                                <td>

                                                </td>
                                                <td>
                                                    {{$item->description}}     
                                                </td>
                                                
                                                <td style="text-align: center">
                                                    {{$item->register_agentType}} <br> 
                                                    {{$item->registerUser->name}}
                                                </td>

                                                <td class="no-sort no-click bread-actions text-right">                        
                                                    @if ($item->deleted_at == NULL && $item->status != 'rechazado' )
                                                        <a title="Borrar" class="btn btn-sm btn-danger delete" onclick="deleteLicenseAllItem('{{ route('contracts-licenseAll.destroy', ['licence' => $item->id]) }}')" data-toggle="modal" data-target="#delete-licenseAll-modal">
                                                            <i class="voyager-trash"></i>
                                                        </a>
                                                    @endif    
                                                    @if ($item->deleted_at)
                                                        <label class="label label-danger">ELIMINADO</label>                            
                                                    @endif                                                     
                                                </td>
                            
                                            </tr>
                                        @empty
                                            <tr>
                                                <td style="text-align: center" valign="top" colspan="10" class="dataTables_empty">No hay datos disponibles en la tabla</td>
                                            </tr>
                                        @endforelse
                                        
                                    </tbody>
                                </table>
                            </div>
                            
                        </div>
                        <div class="row table-responsive">                      
                            <div class="col-md-12 ">
                                <table  border="1" class="table table-bordered table-hover" cellpadding="5" style="font-size: 12px">
                                    
                                    @php
                                            $meses=array(1=>"Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio",
                                                "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
                                            
                                            $fechaInicio = \Carbon\Carbon::parse($contract->dateStart);
                                            $mesInicio = $fechaInicio->format("n"); //para saber desde que mes empiesa la cuota                        
                                            $diaInicio = $fechaInicio->format("d"); //para saber en que dia se paga la cuota
                                            $anoInicio = $fechaInicio->format("Y"); //para saber en que año empiesa la cuota
                                            // dd($anoInicio);
                                            $inicio = $anoInicio.'-'.($mesInicio<=9?'0'.$mesInicio : ''.$mesInicio).'-'.$diaInicio;
                                            // dd($inicio);


                                            
                                            $fechaFin = \Carbon\Carbon::parse($contract->dateFinish);
                                            $mesFin = $fechaFin->format("n"); //para saber hasta que mes termina la cuota                        
                                            $diaFin = $fechaFin->format("d"); //para saber hasta que dia termina la cuota
                                            $anoFin = $fechaFin->format("Y"); //para saber hasta que año termina la cuota
                                            // dd($fechaFin);
                                            $fin = $anoFin.'-'.($mesFin<=9?'0'.$mesFin : ''.$mesFin).'-'.$diaFin;

                                            // $aux <= 9 ? '-0'.$aux : '-'.$aux
                                            // dd($fin);

                                          

                                            // $cantMeses = count($cantMes); //para la cantidad de meses que hay entre las dos fecha
                                            $cantMeses = count($cantMonth); //para la cantidad de meses que hay entre las dos fecha
                                            $mes = 0;

                                            $number=0;
                                            // $cantNumber = count($loanday);
                                            $cantNumber = count($ContractDay);

                                            $okNumber =0;
                                            // dd($cantNumber);

                                            
                                    @endphp
                                    
                                    @while ($mes < $cantMeses)
                                        
                                        <tr style="background-color: #22a7f0; color: white; font-size: 18px">
                                            <td colspan="7" style="text-align: center">{{$meses[intval($cantMonth[$mes]->mes)]}} - {{intval($cantMonth[$mes]->ano)}}</td>
                                        </tr>
                                        <tr style="background-color: #22a7f0; color: white; font-size: 18px">
                                            <td style="text-align: center; width: 15%">LUN</td>
                                            <td style="text-align: center; width: 15%">MAR</td>
                                            <td style="text-align: center; width: 15%">MIE</td>
                                            <td style="text-align: center; width: 15%">JUE</td>
                                            <td style="text-align: center; width: 15%">VIE</td>
                                            <td style="text-align: center; width: 15%">SAB</td>
                                            <td style="text-align: center; width: 10%">DOM</td>
                                        </tr>

                                        @php
                                            $primerDia = date('d', mktime(0,0,0, intval($cantMonth[$mes]->mes), 1, intval($cantMonth[$mes]->ano)));//para obtener el primer dia del mes
                                            $primerFecha = intval($cantMonth[$mes]->ano).'-'.intval($cantMonth[$mes]->mes).'-'.$primerDia; // "20XX-XX-01"concatenamos el primer dia ma sel mes y el año del la primera cuota
                                            $posicionPrimerFecha = \Carbon\Carbon::parse($primerFecha);
                                            $posicionPrimerFecha = $posicionPrimerFecha->format("N"); //obtenemos la posicion de la fecha en que dia cahe pero en numero

                                           
                                            $ultimoDia = date("d", mktime(0,0,0, intval($cantMonth[$mes]->mes)+1, 0, intval($cantMonth[$mes]->ano)));//para obtener el ultimo dia del mes
                                            $ok = false;

                                            $dia=0;
                                        @endphp
                                        
                                        @for ($x = 1; $x <= 6; $x++)
                                            <tr>
                                                @for ($i = 1; $i <= 7; $i++) 

                                                    @if ($i == $posicionPrimerFecha && !$ok)
                                                        @php
                                                            $dia++;
                                                            $ok=true;
                                                            $fecha = $cantMonth[$mes]->ano.'-'.$cantMonth[$mes]->mes.($dia<=9?'-0'.$dia:'-'.$dia);
                                                            // dd($fecha);
                                                        @endphp
                                                        <td style="text-align: center;">                                                 
                                                            @php
                                                                    $auxDay = $contract->contractDay->where('date', $fecha)->first();                                                                   
                                                                        
                                                                @endphp
                                                                <small style="font-size: 15px;">
                                                                    {{$dia}} 
                                                                </small>
                                                                @if ($auxDay)
                                                                    <small  style="font-size: 15px;">
                                                                        @if (!$auxDay->spreadsheet && $auxDay->typeLicense != 'Licencia dia completo')
                                                                            <a onclick="dayItem('{{ route('contracts-contractDay-hour.save', ['contractDay_id'=>$auxDay->id]) }}')" title="Cambio de Horario" data-toggle="modal" data-target="#hour-modal" >
                                                                                <i class="fa-solid fa-gear"></i>
                                                                            </a>
                                                                        @endif
                                                                    </small>
                                                                
                                                                    @foreach ($auxDay->contractDayAttendance as $item)
                                                                        <div class="row marc centrado-horizontal">
                                                                            <label style="font-size: 15px" for=""><i class="fa-regular fa-clock"></i> {{$item->shiftHour_id? $item->shiftHour->hourStart: $item->hours->hourStart}}  -  {{$item->shiftHour_id?$item->shiftHour->hourFinish:$item->hours->hourFinish}}</label>
                                                                            @if ($auxDay->spreadsheet && $item->license_id == null)
                                                                                <label style="font-size: 15px"  for=""><i class="fa-solid fa-fingerprint" style="{{$item->start==null || $item->finish == null?'color: red':'color: #0080FF'}}"></i> {{$item->start?$item->start:'##:##:##'}}  -  {{$item->finish?$item->finish:'##:##:##'}}</label>                                                                               
                                                                            @endif

                                                                            {{-- @if (!$auxDay->spreadsheet && $item->license_id != null) --}}
                                                                            @if ($item->license_id != null)
                                                                                <label style="font-size: 15px"><i class="fa-solid fa-file" style="color: rgb(49, 57, 82)"></i> <small>Con Licencia</small></label>                                                                               
                                                                            @endif
                                                                        </div>
                                                                    @endforeach
                                                                    
                                                                @endif 
                                                        </td>
                                                    @else
                                                        @if ($ok && $dia < $ultimoDia){{-- para que muestre hasta el ultimo dia del mes  --}}
                                                            @php
                                                                $dia++;
                                                                $fecha = $cantMonth[$mes]->ano.'-'.$cantMonth[$mes]->mes.($dia<=9?'-0'.$dia:'-'.$dia);
                                                            @endphp       
                                                            <td style="text-align: center;">
                                                    
                                                                @php
                                                                    $auxDay = $contract->contractDay->where('date', $fecha)->first();                                                                   
                                                                        
                                                                @endphp
                                                                <small style="font-size: 15px;">
                                                                    {{$dia}} 
                                                                    @if ($auxDay)
                                                                    @endif
                                                                </small>
                                                                @if ($auxDay)
                                                                    <small  style="font-size: 15px;">
                                                                        @if (!$auxDay->spreadsheet && $auxDay->typeLicense != 'Licencia dia completo')
                                                                            <a onclick="dayItem('{{ route('contracts-contractDay-hour.save', ['contractDay_id'=>$auxDay->id]) }}')" title="Cambio de Horario" data-toggle="modal" data-target="#hour-modal" >
                                                                                <i class="fa-solid fa-gear"></i>
                                                                            </a>
                                                                        @endif
                                                                    </small>
                                                                
                                                                    @foreach ($auxDay->contractDayAttendance as $item)
                                                                        <div class="row marc centrado-horizontal">
                                                                            <label style="font-size: 15px" for=""><i class="fa-regular fa-clock"></i> {{$item->shiftHour_id? $item->shiftHour->hourStart: $item->hours->hourStart}}  -  {{$item->shiftHour_id?$item->shiftHour->hourFinish:$item->hours->hourFinish}}</label>
                                                                            @if ($auxDay->spreadsheet && $item->license_id == null)
                                                                                <label style="font-size: 15px"  for=""><i class="fa-solid fa-fingerprint" style="{{$item->start==null || $item->finish == null?'color: red':'color: #0080FF'}}"></i> {{$item->start?$item->start:'##:##:##'}}  -  {{$item->finish?$item->finish:'##:##:##'}}</label>                                                                               
                                                                            @endif

                                                                            {{-- @if (!$auxDay->spreadsheet && $item->license_id != null) --}}
                                                                            @if ($item->license_id != null)
                                                                                <label style="font-size: 15px"><i class="fa-solid fa-file" style="color: rgb(49, 57, 82)"></i> <small>Con Licencia</small></label>                                                                               
                                                                            @endif
                                                                        </div>
                                                                    @endforeach
                                                                    
                                                                @endif 
                                                            </td>                                                                                                                                             
                                                        @else
                                                            <td style="height: 80px; text-align: center"></td>                                                                                           
                                                        @endif
                                                    @endif
                                                    @if ($dia == $ultimoDia)
                                                        @php
                                                            $x=10;
                                                        @endphp
                                                    @endif 
                                                @endfor  
                                            </tr>          
                                        @endfor  
                                        @php
                                            $mes++;
                                        @endphp
                                    @endwhile                                 
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

       
        
    </div>

    <form class="form-submit " id="form-agregar-gasto" action="{{ route('contracts-license-all.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="modal fade" tabindex="-1" id="license-modal" role="dialog">
            <div class="modal-dialog ">
                <div class="modal-content modal-success">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="fa-solid fa-file"></i> Licencias</h4>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="contract_id" value="{{$contract->id}}">
                        <div class="alert alert-info">
                            <strong>Información:</strong>
                            <p>Licencia de la jornada completa.</p>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-4">
                                <small>Fecha Inicio</small>
                                <input type="date" name="dateStart" class="form-control text" required>
                            </div>   
                            <div class="form-group col-md-4">
                                <small>Fecha Fin</small>
                                <input type="date" name="dateFinish" class="form-control text" required>
                            </div> 
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <small>Archivo</small>
                                <input type="file" accept="image/jpeg,image/jpg,image/png,application/pdf" name="file" id="file" class="form-control text imageLength">
                            </div>  
                        </div>
                        
                        <div class="form-group">
                            <small>Descripción</small>
                            <textarea name="description" class="form-control" rows="5" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success btn-submit">Registrar</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="modal modal-danger fade" data-backdrop="static" tabindex="-1" id="delete-licenseAll-modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="voyager-trash"></i> Desea eliminar el siguiente registro?</h4>
                </div>
                <div class="modal-footer">
                    <form action="#" id="delete_licenseAll_form" method="POST">
                        {{ method_field('DELETE') }}
                        {{ csrf_field() }}
                            <input type="hidden" name="contract_id" value="{{$contract->id}}">
                            <div class="text-center" style="text-transform:uppercase">
                                <i class="voyager-trash" style="color: red; font-size: 5em;"></i>
                                <br>
                                
                                <p><b>Desea eliminar el siguiente registro?</b></p>
                            </div>
                            <div class="row text-left">
                                <div class="form-group col-md-12">
                                    <small>Observación</small>
                                    <textarea name="observation" id="observation" class="form-control text" cols="30" rows="5"></textarea>
                                </div>                                    
                            </div>
                        <input type="submit" class="btn btn-danger pull-right delete-confirm" value="Sí, eliminar">
                    </form>
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Agregar adelantos --}}
    <form class="form-submit" id="form-agregar-gasto" action="{{ route('contracts-advancement.store') }}" method="post">
        @csrf
        <div class="modal modal-dark fade" tabindex="-1" id="agregar-advancement-modal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="voyager-dollar"></i> Agregar Adelantos</h4>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-success">
                            <strong>Advertencia:</strong>
                            <p>Solo puede dar un adelanto de un 40% del mes a pagar.</p>
                        </div>
                        <input type="hidden" name="contract_id" value="{{$contract->id}}">
                        <div class="form-group">
                            <small>Monto</small>
                            <input type="number" name="amount" class="form-control" step="0.1" min="0.1" required>
                        </div>
                        <div class="form-group">
                            <small>Descripción</small>
                            <textarea name="description" class="form-control" rows="5" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-submit">Registrar</button>
                    </div>
                </div>
            </div>
        </div>
    </form>


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
                                
                                <p><b>Desea eliminar el siguiente registro?</b></p>
                            </div>
                            <div class="row text-left">
                                <div class="form-group col-md-12">
                                    <small>Observación</small>
                                    <textarea name="observation" id="observation" class="form-control text" cols="30" rows="5"></textarea>
                                </div>                                    
                            </div>
                        <input type="submit" class="btn btn-danger pull-right delete-confirm" value="Sí, eliminar">
                    </form>
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>



    <form action="#" id="form-contractDay-hour" method="POST">
        @csrf
            <div class="modal fade" tabindex="-1" id="hour-modal" role="dialog">
                <div class="modal-dialog modal-lg modal-success">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title"><i class="fa-solid fa-clock"></i> Agregar Horarios</h4>
                        </div>
                        <div class="modal-body">
                           

                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="table-responsive">
                                        <table id="dataStyle" class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th rowspan="2" style="width: 5%; text-align: center"></th>
                                                    <th rowspan="2">Nombre</th>    
                                                    <th colspan="2" style="text-align: center">Ingreso y Salida</th>
                                                    <th colspan="2" style="text-align: center">Tolerancia y Salida Temprana</th>
                                                    <th colspan="2" style="text-align: center">Rango de Entrada</th>
                                                    <th colspan="2" style="text-align: center">Rango de Salida</th>
                                
                                                    <th rowspan="2" style="text-align: center">Tiempo</th>
                                                    <th rowspan="2">Descripción</th>
                                                    {{-- <th class="text-right">Acciones</th> --}}
                                                </tr>
                                                <tr>
                                                    <th>Hora Inicio</th>
                                                    <th>Hora Finalización</th>
                                
                                                    <th>Hora Tardia(Minutos)</th>
                                                    <th>Hora Salida Temprana(Minuto)</th>
                                
                                                    <th>Empesando en</th>
                                                    <th>Terminando en.</th>
                                
                                                    <th>Empesando en</th>
                                                    <th>Terminando Afuera</th>
                                                    
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $i = 1;
                                                @endphp
                                                @forelse ($hour as $item)
                                                <tr>
                                                    <td>
                                                        <label>
                                                            <input type="checkbox" name="hour[]" id="{{'hour-'.$i}}}"  value="{{$item->id}}">
                                                        </label>
                                                    </td>
                                                    <td>{{$item->name}}</td>
                                                    <td>{{\Carbon\Carbon::createFromFormat('H:i:s', $item->hourStart)->format('h:i A')}}</td>
                                                    <td>{{\Carbon\Carbon::createFromFormat('H:i:s', $item->hourFinish)->format('h:i A')}}</td>

                                                    <td>{{$item->minuteLate}} Minutos</td>
                                                    <td>{{$item->minuteEarly}} Minutos</td>

                                                    <td>{{\Carbon\Carbon::createFromFormat('H:i:s', $item->rangeStartInput)->format('h:i A')}}</td>
                                                    <td>{{\Carbon\Carbon::createFromFormat('H:i:s', $item->rangeStartOutput)->format('h:i A')}}</td>

                                                    <td>{{\Carbon\Carbon::createFromFormat('H:i:s', $item->rangeFinishInput)->format('h:i A')}}</td>
                                                    <td>{{\Carbon\Carbon::createFromFormat('H:i:s', $item->rangeFinishOutput)->format('h:i A')}}</td>


                                                    <td style="text-align: center">
                                                        @if ($item->day == 0.5)
                                                            <label class="label label-info">Medio Tiempo</label>                            
                                                        @else 
                                                            <label class="label label-success">Tiempo Completo</label> <br>
                                                        @endif                                
                                                    </td>
                                                    <td>{{$item->description}}</td>                                                      
                                                </tr>
                                                @empty
                                                    
                                                @endforelse        
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                            <input type="submit" class="btn btn-success btn-save-customer" value="Guardar">
                        </div>
                    </div>
                </div>
            </div>
        </form>
@stop

@section('css')
    <style>
        .marc {
            border: 1px solid black; /* Borde de 2px, color negro */
            padding: 15px;
            text-align: center;
        }
        .centrado-horizontal {
            width: 100%;
            height: 40%;
            margin: 0 auto; 
        }
    </style>
@stop

@section('javascript')
    <script src="{{ url('js/main.js') }}"></script>
    <script>
        moment.locale('es');
        $(document).ready(function () {
            
        });

        function dayItem(url){
                $('#form-contractDay-hour').attr('action', url);
        }


        function printAdvancementMoney(contract_id, id)
        {
            window.open("{{ url('admin/contracts/advancement/money/print') }}/"+contract_id+"/"+id, "Recibo", `width=320, height=700`)
        }
        function openPrinf(spreadsheet){
            types = 'periodo';
            // window.open("{{ url('admin/paymentSheet/print') }}/"+spreadsheet, "Recibo", `width=800, height=700`);
            window.open("{{ url('admin/paymentSheet/print') }}/"+types+"/"+spreadsheet, "Comprobante de Pago", `width=800, height=700`);


        }

        function deleteItem(url){
            $('#delete_form').attr('action', url);
        }

        function deleteLicenseAllItem(url){
            $('#delete_licenseAll_form').attr('action', url);
        }
    </script>
@stop
@endif
