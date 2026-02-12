<div class="col-md-12">
    <div class="table-responsive">
        <table id="dataStyle" class="table table-hover">
            <thead>
                <tr>
                    <th style="text-align: center">Id</th>
                    <th style="text-align: center">Usuario</th>
                    <th style="text-align: center">Nombre</th>
                    <th style="text-align: center">Estado</th>
                    <th style="text-align: center">Apertura</th>
                    <th style="text-align: center">Cierre</th>
                    <th style="text-align: center">Detalles de cierre</th>
                    <th style="text-align: right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($cashier as $item)
                    <tr>
                        <td>{{$item->id}}</td>
                        <td style="width: 200pt; text-align: center">{{strtoupper($item->user->name)}}</td>
                        <td style="text-align: center">{{strtoupper($item->title)}}</td>
                        <td style="text-align: center">
                            @if ($item->status == 'abierta')
                                <label class="label label-success">Abierta</label>
                            @endif
                            @if ($item->status == 'cerrada')
                                <label class="label label-danger">Cerrada</label>
                            @endif

                            @if ($item->status == 'cierre pendiente')
                                <label class="label label-primary">Cierre Pendiente</label>
                            @endif

                            @if ($item->status == 'apertura pendiente')
                                <label class="label label-warning">Apertura Pendiente</label>
                            @endif
                            {{-- <label class="label label-success">{{$item->status}}</label> --}}

                        </td>
                        <td style="text-align: center">{{date('d/m/Y H:i:s', strtotime($item->created_at))}}<br><small>{{\Carbon\Carbon::parse($item->created_at)->diffForHumans()}}</small></td>
                        <td style="text-align: center">@if($item->closed_at){{date('d/m/Y H:i:s', strtotime($item->closed_at))}}<br><small>{{\Carbon\Carbon::parse($item->closed_at)->diffForHumans()}}@endif </small></td>
                        <td>
                            @if ($item->amount)
                                <b>Monto de cierre: </b> {{ $item->amount_real }}<br>
                                <b>Saldo: </b> <span class="@if($item->balance > 0) text-success @endif @if($item->balance < 0) text-danger @endif">{{ $item->balance }}</span>
                            @endif
                        </td>
                        <td style="text-align: right">
                            <div class="btn-group" style="margin-right: 3px">
                                <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                                    Mas <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu" role="menu" style="left: -90px !important" >
                                    @php
                                        $x=0;
                                    @endphp
                                    @foreach ($item->vault_detail as $aux)
                                        @php
                                            $x++;
                                        @endphp
                                        <li><a href="#" onclick="openWindow({{$aux->id}})" style="color: blue"  data-toggle="modal" title="Imprimir Comprobante" ><i class="fa-solid fa-print"></i> {{$x==1?'Imprimir Comporbante de Apertura':'Imprimir Comporbante de Abono #'.$x}}</a></li>
                                    @endforeach
                                    @if ($item->status == 'abierta')
                                        <li><a href="#" class="btn-agregar-gasto" data-cashier_id="{{ $item->id }}" data-toggle="modal" data-target="#agregar-gasto-modal" title="Agregar gasto" ><i class="voyager-dollar"></i> Agregar gasto</a></li>
                                    @endif
                                    @if ($item->status == 'cerrada')
                                        <li><a href="#" onclick="closeWindow({{$item->id}})" style="color: red" data-toggle="modal" title="Imprimir Comprobante de Cierre" ><i class="fa-solid fa-print"></i> Imprimir Comprobante de Cierre</a></li>
                                    @endif
                                </ul>
                            </div>
                            @if ($item->status == 'abierta')
                                <a href="{{route('cashiers.amount', ['cashier'=>$item->id])}}" title="Editar" class="btn btn-sm btn-success">
                                    <i class="voyager-dollar"></i> <span class="hidden-xs hidden-sm">Abonar</span>
                                </a>
                            @endif
                            @if (auth()->user()->hasPermission('read_cashiers'))
                                <a href="{{route('cashiers.show', ['cashier'=>$item->id])}}" title="Editar" class="btn btn-sm btn-warning">
                                    <i class="voyager-eye"></i> <span class="hidden-xs hidden-sm">Ver</span>
                                </a>
                            @endif
                            {{-- @if ($item->status == "cierre pendiente")
                                <a href="{{route('cashiers.confirm_close',['cashier' => $item->id])}}" title="Ver" class="btn btn-sm btn-dark">
                                    <i class="voyager-lock"></i> <span class="hidden-xs hidden-sm">Confirmar Cierre de Caja</span>
                                </a>
                            @endif --}}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center">Sin Datos</td>
                    </tr>
                @endforelse                                   
            </tbody>
        </table>
    </div>
</div>

<div class="col-md-12">
    <div class="col-md-4" style="overflow-x:auto">
        @if(count($cashier)>0)
            <p class="text-muted">Mostrando del {{$cashier->firstItem()}} al {{$cashier->lastItem()}} de {{$cashier->total()}} registros.</p>
        @endif
    </div>
    <div class="col-md-8" style="overflow-x:auto">
        <nav class="text-right">
            {{ $cashier->links() }}
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

        $('.btn-agregar-gasto').click(function(){
            let cashier_id = $(this).data('cashier_id');
            $('#form-agregar-gasto input[name="cashier_id"]').val(cashier_id);
        });
    });
</script>