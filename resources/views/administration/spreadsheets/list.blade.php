<div class="col-md-12">
    <div class="table-responsive">
        <table id="dataStyle" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Mes</th>                         
                    <th>Año</th>        
                    <th>Descripción</th>        
                    <th>Monto Total</th>
                    <th style="text-align: center">Estado</th>

                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $monthList = [
                        '1' => 'Enero',
                        '2' => 'Febrero',
                        '3' => 'Marzo',
                        '4' => 'Abril',
                        '5' => 'Mayo',
                        '6' => 'Junio',
                        '7' => 'Julio',
                        '8' => 'Agosto',
                        '9' => 'Septiembre',
                        '10' => 'Octubre',
                        '11' => 'Noviembre',
                        '12' => 'Diciembre'
                    ];
                @endphp
                @forelse ($data as $item)
                    <tr>
                        <td>{{$item->id}}</td>
                        <td>{{$monthList[$item->month]}}</td>
                        <td>{{$item->year}}</td>
                        <td>{{$item->description}}</td>
                        @php
                            $aux = $item->spreadsheetContract->where('deleted_at', null)->sum('liquidPaid');
                        @endphp
                        <td><small>Bs. {{number_format($aux, 2, '.', '')}}</small></td>

                        <td style="text-align: center">
                            @if ($item->deleted_at != NULL)
                                <label class="label label-danger">Eliminado</label>                            
                            @endif  
                          

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
                                <a title="Aprobar" class="btn btn-sm btn-info" onclick="successItem('{{ route('spreadsheets.generate', ['spreadsheet' => $item->id]) }}')" data-toggle="modal" data-target="#success-modal">
                                    <i class="fa-solid fa-file-csv"></i> Generar</span>
                                </a>
                                <a title="Rechazar" class="btn btn-sm btn-dark" onclick="rechazarItem('{{ route('spreadsheets.rechazar', ['spreadsheet' => $item->id]) }}')" data-toggle="modal" data-target="#rechazar-modal">
                                    <i class="fa-solid fa-thumbs-down"></i> <span class="hidden-xs hidden-sm">Rechazar</span>
                                </a>
                            @endif

                        
                            {{-- @if ($item->status=='aprobado' && $item->deleted_at == NULL)
                                <a href="{{ route('contracts.show', ['contract' => $item->id]) }}" title="Ver"  class="btn btn-sm btn-success">
                                    <i class="fa-solid fa-eye"></i><span class="hidden-xs hidden-sm"> Ver</span>
                                </a>
                            @endif  --}}
                            @if (($item->status=='finalizado' || $item->status=='aprobado') && $item->deleted_at == NULL)
                                <a href="{{ route('spreadsheets.print', ['spreadsheet' => $item->id]) }}" title="Imprimir" target="_blank" class="btn btn-sm btn-dark">
                                    <i class="fa-solid fa-print"></i><span class="hidden-xs hidden-sm"></span>
                                </a>
                            @endif 


                            @if($item->deleted_at == NULL && $item->status != 'rechazado' && $item->status != 'aprobado'&& $item->status != 'finalizado')

                                <a title="Borrar" class="btn btn-sm btn-danger delete" onclick="deleteItem('{{ route('spreadsheets.destroy', ['spreadsheet' => $item->id]) }}')" data-toggle="modal" data-target="#delete-modal">
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