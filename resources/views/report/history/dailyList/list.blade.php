<div class="col-md-12">
    <div class="table-responsive">
        <table id="dataStyle" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Ruta</th>                 
                    <th>Tipo.</th>
                    <th>Fecha.</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->route->name }}</td>
                    <td>
                        @if ($item->type=='inicio')
                            Iniciando el día
                        @else
                            Finalizando el día
                        @endif
                    </td>
              
                    <td>{{ date('d/m/Y H:m:s', strtotime($item->created_at)) }} </td>
                   
                    <td class="no-sort no-click bread-actions text-right">
                        <a href="{{ route('history-dailyList.print', ['id' => $item->id]) }}" target="_blank" title="Imprimir" class="btn btn-sm btn-success view">
                            <i class="fa-solid fa-print"></i>
                        </a>
                        <button title="Borrar" class="btn btn-sm btn-danger delete" onclick="deleteItem('{{ route('history-dailyList.delete', ['id' => $item->id]) }}')" data-toggle="modal" data-target="#modal-delete">
                            <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm"></span>
                        </button>
                    </td>
                </tr>
                @empty
                    <tr style="text-align: center">
                        <td colspan="5" class="dataTables_empty">No hay datos disponibles en la tabla</td>
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