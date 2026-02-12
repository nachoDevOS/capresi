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
                    <th>Detalle</th>
                    <th>Detalles</th>        
                    <th>Deuda</th>   
                    {{-- <th class="text-right">Acciones</th> --}}
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
                        @if ($item->notification == 'si')
                            <i class="fa-regular fa-bell" style="font-size: 20px; color: #000000"></i>
                        @else
                            <i class="fa-regular fa-bell-slash" style="font-size: 20px; color: #000000"></i>
                        @endif
                        {{-- @if ($item->status == 'entregado' && $item->status != 'rechazado' && (auth()->user()->hasRole('cobrador') || auth()->user()->hasRole('admin')))
                            <a href="{{ route('loans-daily.money', ['loan' => $item->id, 'cashier_id'=>$cashier_id]) }}" title="Abonar Pago"  class="btn btn-sm btn-success">
                                <i class="fa-solid fa-money-check-dollar"></i>
                            </a>
                        @endif --}}
                    </td>
                    <td>
                        <table>                                                    
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
                    {{-- <td>
                        {{ date("d-m-Y", strtotime($item->date)) }}
                    </td> --}}
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
                            <tr>
                                <td><b>Fecha Ent.</b></td>
                                <td class="text-right">
                                    {{ date("d-m-Y", strtotime($item->dateDelivered)) }}
                                </td>
                            </tr>
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

                    <td>
                        @if ($item->typeLoan == 'diario')
                           
                            <label class="label label-danger"><small>Deuda: Bs.</small> {{$item->debt}}</label>
                        @endif
                        @if ($item->typeLoan == 'diarioespecial')
                 
                            <label class="label label-danger"><small>Deuda: Bs.</small> {{$item->debt}}</label>
                        @endif
                        @if ($item->payments_period_id)
                            <br>
                            <small style="color: {{ $item->payments_period->color }}">Paga {{ $item->payments_period->name }}</small>
                        @endif
                    </td>
                   
              
                </tr>
                @empty
                    <tr>
                        <td style="text-align: center" valign="top" colspan="5" class="dataTables_empty">No hay datos disponibles en la tabla</td>
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