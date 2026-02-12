<div class="col-md-12">
    <div class="table-responsive">
        <table id="dataStyle" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>                         
                    <th>Celular</th>        
                    <th>Cargo</th>
                    <th>Fecha</th>
                    {{-- <th>Adelantos</th> --}}
                    <th style="text-align: center">Estado</th>

                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $item)
                    <tr>
                        <td>{{$item->id}}</td>
                        <td>
                            <table>
                                @php
                                    $image = asset('images/icono-anonimato.png');
                                    if($item->people->image){
                                        $image = asset('storage/'.str_replace('.', '-cropped.', $item->people->image));
                                    }
                                    $now = \Carbon\Carbon::now();
                                    $birthday = new \Carbon\Carbon($item->people->birth_date);
                                    $age = $birthday->diffInYears($now);
                                @endphp
                                <tr>
                                    <td><img src="{{ $image }}" alt="{{ $item->people->first_name }} " style="width: 60px; height: 60px; border-radius: 30px; margin-right: 10px"></td>
                                    <td>
                                        <small>CI: {{$item->people->ci}}</small> <br>
                                        {{ strtoupper($item->people->first_name) }} {{ strtoupper($item->people->last_name1) }} {{ strtoupper($item->people->last_name2) }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td>{{$item->people->cell_phone}}</td>
                        <td style="text-align: le"ft> 
                            <small>Cargo: </small>{{$item->work}}
                            <br>
                            <small>Sueldo: </small>{{$item->totalSalary}}
                        </td>
                        <td>
                            <small>Fecha Inicio: </small> {{ date("d-m-Y", strtotime($item->dateStart)) }} <br>
                            <small>Fecha Finalización: </small> {{ date("d-m-Y", strtotime($item->dateFinish)) }}
                        </td>
                        {{-- <td>
                            {{$item->advancement}}
                        </td> --}}
                        <td style="text-align: center">
                            @if ($item->deleted_at != NULL)
                                <label class="label label-danger">Eliminado</label>                            
                            @endif  
                            {{-- @if ($item->advancement == 0 && $item->status == 'aprobado' && $item->deleted_at == NULL)
                                <label class="label label-success">Sin Adelantos</label> <br>
                            @endif --}}
                          

                            @if ($item->status == 'pendiente' && $item->deleted_at == NULL)
                                <label class="label label-danger">PENDIENTE</label>                            
                            @endif
                            @if ($item->status == 'finalizado'&& $item->deleted_at == NULL)
                                <label class="label label-dark">FINALIZADO</label>                            
                            @endif
                            @if ($item->status == 'aprobado'&& $item->deleted_at == NULL)
                                <label class="label label-success">EN CURSO</label>                            
                            @endif
                            @if ($item->status == 'rechazado' && $item->deleted_at == NULL)
                                <label class="label label-dark">RECHAZADO</label>                            
                            @endif       
                        </td>

                        <td class="no-sort no-click bread-actions text-right">             
                            @if ($item->status=='pendiente' && $item->deleted_at == NULL)
                                <a title="Aprobar" class="btn btn-sm btn-info" onclick="successItem('{{ route('contracts.success', ['contract' => $item->id]) }}')" data-toggle="modal" data-target="#success-modal">
                                    <i class="fa-solid fa-thumbs-up"></i><span class="hidden-xs hidden-sm"> Aprobar</span>
                                </a>
                                <a title="Rechazar" class="btn btn-sm btn-dark" onclick="rechazarItem('{{ route('contracts.rechazar', ['contract' => $item->id]) }}')" data-toggle="modal" data-target="#rechazar-modal">
                                    <i class="fa-solid fa-thumbs-down"></i> <span class="hidden-xs hidden-sm">Rechazar</span>
                                </a>
                            @endif

                            {{-- @if ($item->status=='aprobado' && $item->deleted_at == NULL) --}}
                        
                            {{-- @endif --}}

                            <a href="{{ route('contracts.show', ['contract' => $item->id]) }}" title="Ver"  class="btn btn-sm btn-success">
                                <i class="fa-solid fa-eye"></i><span class="hidden-xs hidden-sm"> Ver</span>
                            </a>
                            {{-- @if($item->deleted_at == NULL && $item->status != 'rechazado'&& $item->advancement == 0)

                                <a title="Borrar" class="btn btn-sm btn-danger delete" onclick="deleteItem('{{ route('contracts.destroy', ['contract' => $item->id]) }}')" data-toggle="modal" data-target="#delete-modal">
                                    <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm">Borrar</span>
                                </a>
                            @endif  --}}
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