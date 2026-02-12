<div class="col-md-12">
    <div class="table-responsive">
        <table id="dataStyle" class="table table-bordered table-hover">
            <thead>
                <tr>
                    @if (auth()->user()->hasRole('admin'))
                        <th>ID</th>
                    @endif
                    <th>Codigo</th>
                    <th>Nombre Cliente</th>    
                    <th style="text-align: center">Detalles</th>
                    <th style="text-align: center">Detalle del Monto Prestado</th>             
                    <th style="text-align: center">Detalle de Deuda</th>      
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $item)
                <tr>
                    @if (auth()->user()->hasRole('admin'))
                        <th>{{$item->id}}</th>
                    @endif
                    <td>
                        <small>
                            {{ $item->code }}
                        </small>
                        <br>
                        @if ($item->status=='entregado' && !auth()->user()->hasRole('cobrador'))
                            <a href="#" data-toggle="modal" data-target="#enableNotification-modal" onclick="loanNotification('{{ route('notificationAutomatic', ['loan' => $item->id]) }} ')" title="Notificación " class="btn btn-sm">
                                @if ($item->notification == 'si')
                                    <i class="fa-regular fa-bell" style="font-size: 20px; color: #000000"></i>
                                @else
                                    <i class="fa-regular fa-bell-slash" style="font-size: 20px; color: #000000"></i>
                                @endif
                            </a>
                        @endif
                    </td>
                    
                    <td>
                        <table>                                                    
                            @php
                                $image = asset('images/icono-anonimato.png');
                                if($item->people->image){
                                    $image = asset('storage/'.str_replace('.', '-cropped.', $item->people->image));
                                }
                            @endphp
                            <tr>
                                <td>
                                    <small>CI:</small> {{ $item->people->ci }} <br>
                                    <small>{{strtoupper($item->people->first_name)}} {{strtoupper($item->people->last_name1)}} {{strtoupper($item->people->last_name2)}} </small>
                                    @if ($item->manager)
                                        <br>
                                        <small class="text-primary" style="font-weight: bold">Aprobado por {{ $item->manager->name }}</small>
                                    @endif
                                </td>
                            </tr>
                            
                        </table>
                    </td>
                    <td>
                        
                        <table style="width: 100%">
                            <tr>
                                <td><b>Tipo</b></td>
                                <td class="text-right">
                                    @if ($item->typeLoan == 'diario')
                                        Diario {{ $item->payments_period_id }}
                                    @endif
                                    @if ($item->typeLoan == 'diarioespecial')
                                        Diario Especial
                                    @endif
                                    @if ($item->payments_period_id)
                                        <br>
                                        <small style="color: {{ $item->payments_period->color }}">Paga {{ $item->payments_period->name }}</small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><b>Fecha Sol.</b></td>
                                <td class="text-right">
                                    {{ date("d-m-Y", strtotime($item->date)) }}
                                </td>
                            </tr>
                            @if($item->dateDelivered)
                                <tr>
                                    <td><b>Fecha Ent.</b></td>
                                    <td class="text-right">
                                        {{ date("d-m-Y", strtotime($item->dateDelivered)) }}                                        
                                    </td>
                                </tr>
                            @endif                                    
                        </table>
                    </td>
                    <td style="text-align: right">
                        <table style="width: 100%">
                            <tr>
                                <td><b>Monto Prestado</b></td>
                                <td class="text-right">
                                    {{$item->amountLoan}}
                                </td>
                            </tr>
                            <tr>
                                <td><b>Interes</b></td>
                                <td class="text-right">
                                    {{$item->amountPorcentage}}
                                </td>
                            </tr>
                            <tr>
                                <td><b>Total a Pagar</b></td>
                                <td class="text-right">
                                    {{$item->amountTotal}}
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="text-align: right">
                        @if ($item->debt == 0)
                            <label class="label label-success">PAGADO</label>
                        @else
                            <label class="label label-danger"><small>Bs.</small> {{$item->debt}}</label>
                        @endif
                        <br>
                        @if ($item->status == 'pendiente')
                            <label class="label label-danger">PENDIENTE</label>                            
                        @endif
                        @if ($item->status == 'verificado')
                            <label class="label label-warning">VERIFICADO</label>                            
                        @endif
                        @if ($item->status == 'aprobado')
                            <label class="label label-primary">APROBADO</label>                            
                        @endif
                        @if ($item->status == 'entregado')
                            <label class="label label-success">ACTIVO</label>                            
                        @endif
                        @if ($item->status == 'rechazado')
                            <label class="label label-danger">RECHAZADO</label>                            
                        @endif       
                    </td>
                    <td class="no-sort no-click bread-actions text-right">
                        @if ($item->status == 'entregado' && $item->status != 'rechazado')
                            <a href="{{ route('loans-daily.money', ['loan' => $item->id]) }}" title="Pagar"  class="btn btn-sm btn-success">
                                <i class="fa-solid fa-calendar-days"></i> {{$item->debt == 0?'Ver':'Pagar'}}</span>
                            </a>
                        @endif


                        @if ($item->status == 'aprobado')
                            @if (auth()->user()->hasPermission('deliverMoney_loans'))
                                <a title="Entregar dinero al Beneficiario" class="btn btn-sm btn-success" onclick="deliverItem('{{ route('loans-money.deliver', ['loan' => $item->id]) }}')" data-toggle="modal" data-target="#deliver-modal" data-fechass="{{$item->date}}">
                                    <i class="fa-solid fa-money-check-dollar"></i><span class="hidden-xs hidden-sm"> Entregar</span>
                                </a>
                            @endif
                        @endif


                        @if($item->status != 'rechazado')
                            <div class="btn-group" style="margin-right: 3px">
                                <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                                    Mas <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu" role="menu" style="left: -90px !important">
                                    @if ($item->status == 'entregado' && $item->delivered == 'Si')
                                        <li><a href="{{ route('loans-list.transaction', ['loan'=>$item->id])}}" class="btn-transaction" data-toggle="modal" title="Imprimir Calendario" ><i class="fa-solid fa-money-bill-transfer"></i> Transacciones</a></li> 
                                    @endif
                                    @if ($item->status != 'pendiente' && $item->status != 'verificado' && !auth()->user()->hasRole('cobrador'))
                                        <li><a href="{{ route('loans-print.calendar', ['loan'=>$item->id])}}" data-toggle="modal" target="_blank" title="Imprimir Calendario" ><i class="fa-solid fa-print"></i> Imprimir Calendario</a></li>
                                        <li><a style="cursor: pointer" onclick="loan({{$item->id}})" data-toggle="modal" title="Imprimir Contrato" ><i class="fa-solid fa-print"></i> Imprimir Contrato</a></li>
                                        <li><a style="cursor: pointer" onclick="handlePrintClick(this, '{{ setting('servidores.print') }}',{{ json_encode($item) }}, '{{ url('admin/loans/comprobante/print') }}')" data-toggle="modal" title="Imprimir Comprobante de Entrega de Prestamos" ><i class="fa-solid fa-print"></i> Imprimir Comprobante Entrega</a></li>
                                        <li><a href="#" class="btn-payments-period" data-id="{{ $item->id }}" data-toggle="modal" data-target="#payments-period-modal" title="Cambiar periodo de pago" ><i class="voyager-calendar"></i> Cambiar periodo de pago</a></li>
                                    @endif                      
                                </ul>
                            </div>
                        @endif

                        @if(!auth()->user()->hasRole('cobrador') && !auth()->user()->hasRole('cajeros'))
                            <a href="{{ route('loan-routeOld.index', ['loan' => $item->id]) }}" title="Rutas del Prestamo" class="btn btn-sm btn-dark">
                                <i class="fa-solid fa-route"></i><span class="hidden-xs hidden-sm"></span>
                            </a>
                        @endif

                        @if($item->status != 'rechazado' && !auth()->user()->hasRole('cobrador'))
                            <a href="{{ route('loans-requirement-daily.create', ['loan' => $item->id]) }}" title="Requisitos" class="btn btn-sm btn-warning">
                                <i class="fa-solid fa-file"></i><span class="hidden-xs hidden-sm"></span>
                            </a>
                        @endif
                        
                        @if ($item->status=='verificado' && auth()->user()->hasPermission('successLoan_loans') && $item->status != 'rechazado')
                            <a title="Aprobar prestamo" class="btn btn-sm btn-dark" onclick="approveItem('{{ route('loans.approve', ['loan' => $item->id]) }}')" data-toggle="modal" data-target="#approve-modal">
                                <i class="fa-solid fa-money-check-dollar"></i><span class="hidden-xs hidden-sm"> Aprobar Prestamos</span>
                            </a>
                        @endif
                        @if (auth()->user()->hasPermission('delete_loans'))
                            @if ($item->status != 'rechazado' && $item->status != 'entregado')
                                <button title="Rechazar" class="btn btn-sm btn-dark" onclick="declineItem('{{ route('loans.decline', ['loan' => $item->id]) }}')" data-toggle="modal" data-target="#decline-modal">
                                    <i class="fa-solid fa-thumbs-down"></i> <span class="hidden-xs hidden-sm">Rechazar</span>
                                </button>
                                <button title="Borrar" class="btn btn-sm btn-danger delete" onclick="deleteItem('{{ route('loans.destroy', ['loan' => $item->id]) }}')" data-toggle="modal" data-target="#modal-delete">
                                    <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm">Borrar</span>
                                </button>
                            @endif
                        @endif
                    </td>
                </tr>
                @empty
                    <tr>
                        <td style="text-align: center" valign="top" colspan="6" class="dataTables_empty">No hay datos disponibles en la tabla</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="col-md-12">
    <div class="col-md-4" style="overflow-x:auto">
        @if(count($data)>0)
            <p class="text-muted">Mostrando del {{$data->firstItem()}} al {{$data->lastItem()}} de {{$data->total()}} registros.</p>
        @endif
    </div>
    <div class="col-md-8" style="overflow-x:auto">
        <nav class="text-right">
            {{ $data->links() }}
        </nav>
    </div>
</div>

<script>
   
   var page = "{{ request('page') }}";
    $(document).ready(function(){
        $('.page-link').click(function(e){
            e.preventDefault();
            let link = $(this).attr('href');
            if(link){
                page = link.split('=')[1];
                list(page);
            }
        });

        $('.btn-payments-period').click(function(){
            let id = $(this).data('id');
            $('#form-payments-period input[name="id"]').val(id);
        });
    });
</script>