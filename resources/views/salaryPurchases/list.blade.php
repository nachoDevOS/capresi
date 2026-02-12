<div class="col-md-12">
    <div class="table-responsive">
        <table id="dataStyle" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th style="text-align: center">N°</th>
                    <th style="text-align: center">Codigo</th>
                    <th style="text-align: center">Nombre completo</th>                    
                    <th style="text-align: center">F. Prestamos</th>
                    <th style="text-align: center">Monto Prestado</th>
                    <th style="text-align: center">Tasa de Interes</th>
                    <th style="text-align: center">Estado</th>
                    <th style="text-align: center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $cont=1;
                    $meses = ['', 'ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

                @endphp
                @forelse ($data as $item)
                    <tr>
                        <td>{{ $cont }}</td>
                            <td>
                                {{ $item->code }}

                            </td>
                            <td>
                                {{ $item->person->first_name }} {{ $item->person->last_name1 }} {{ $item->person->last_name2 }} <br>
                                <b>CI: {{ $item->person->ci ?? 'No definido' }}</b>
                            </td>
                        <td style="width: 150px">
                            {{-- {{ date('d/m/Y', strtotime($item->date)) }} --}}
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
                            </table>
                        </td>
                        <td style="text-align: right">{{number_format($item->amount, 2, ',', '.')}}</td>                        
                        <td style="text-align: right">{{number_format($item->interest_rate, 2, ',', '.')}}%</td>                        
                        <td style="text-align: center">
                            @if ($item->status=='pendiente')
                                <label class="label label-warning">Pendiente</label>
                            @endif
                            @if ($item->status=='aprobado')
                                <label class="label label-dark">Aprobado</label>
                            @endif
                            @if ($item->status=='vigente')
                                <label class="label label-success">Vigente</label>
                            @endif

                            @if ($item->status=='pagado')
                                <label class="label label-success">Pagado</label>
                            @endif

                            
                        </td>
                        <td class="no-sort no-click bread-actions text-right">
                            <a href="{{ route('salary-purchases.show', $item->id) }}" title="Ver" class="btn btn-sm btn-warning view">
                                <i class="voyager-eye"></i> <span class="hidden-xs hidden-sm">Ver</span>
                            </a>
                            @if ($item->status=='pendiente' && $item->deleted_at == null)
                                <a title="Aprobar prestamo" class="btn btn-sm btn-dark" onclick="approveItem('{{ route('salaryPurchases.approve', ['salaryPuchase' => $item->id]) }}')" data-toggle="modal" data-target="#approve-modal">
                                    <i class="fa-solid fa-money-check-dollar"></i><span class="hidden-xs hidden-sm"> Aprobar</span>
                                </a>
                            @endif
                            @if ($item->status == 'aprobado' && $item->deleted_at == null)
                                <a title="Entregar dinero al Beneficiario" class="btn btn-sm btn-success" onclick="deliverItem('{{ route('salaryPurchases.deliver', ['salaryPuchase' => $item->id]) }}')" data-toggle="modal" data-target="#deliver-modal" data-fechass="{{$item->date}}">
                                    <i class="fa-solid fa-money-check-dollar"></i><span class="hidden-xs hidden-sm"> Entregar</span>
                                </a>
                            @endif
                            @if ($item->status!='vigente' && $item->status!='pagado' && $item->deleted_at == null)
                                <button title="Borrar" class="btn btn-sm btn-danger delete" onclick="deleteItem('{{ route('salary-purchases.destroy', $item->id) }}')" data-toggle="modal" data-target="#modal-delete">
                                    <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm">Borrar</span>
                                </button>
                            @endif
                        </td>
                    </tr>
                    @php
                        $cont ++;
                    @endphp
                @empty
                    <tr style="text-align: center">
                        <td colspan="8" class="dataTables_empty">No hay datos disponibles en la tabla</td>
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
    });
</script>