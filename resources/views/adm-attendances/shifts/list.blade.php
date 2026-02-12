<div class="col-md-12">
    <div class="table-responsive">
        <table id="dataStyle" class="table table-bordered table-hover">
            <thead>               
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>    
                    <th>Descripción</th>
                    <th class="text-center" style="width :15% ; text-right">Acciones</th>
                </tr>                
            </thead>
            <tbody>
                @forelse ($data as $item)
                    <tr>
                        <td >{{$item->id}}</td>
                        <td>{{$item->name}}</td>     
                        <td>{{$item->description}}</td>
                        <td class="text-right">
                            <a href="{{ route('shifts.show', ['shift' => $item->id]) }}" title="Ver"  class="btn btn-sm btn-warning">
                                @if ($item->status == 'pendiente')
                                    <i class="fa-solid fa-pen-to-square"></i>
                                @endif
                                <i class="fa-solid fa-eye"></i>
                                <span class="hidden-xs hidden-sm"></span>
                            </a>
                            @if (auth()->user()->hasPermission('delete_shifts'))
                                <a title="Borrar" class="btn btn-sm btn-danger delete" onclick="deleteItem('{{ route('shifts.destroy', ['shift' => $item->id]) }}')" data-toggle="modal" data-target="#delete-modal">
                                    <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm">Borrar</span>
                                </a>
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