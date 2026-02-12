<div class="col-md-12">
    <div class="table-responsive">
        <table id="dataStyle" class="table table-bordered table-hover">
            <thead>
                
                <tr>
                    <th rowspan="2">ID</th>
                    <th rowspan="2">Nombre</th>    
                    <th colspan="2" style="text-align: center">Ingreso y Salida</th>
                    <th colspan="2" style="text-align: center">Tolerancia y Salida Temprana</th>
                    <th colspan="2" style="text-align: center">Rango de Entrada</th>
                    <th colspan="2" style="text-align: center">Rango de Salida</th>

                    <th rowspan="2" style="text-align: center">Tiempo</th>
                    <th rowspan="2">Descripción</th>
                    <th rowspan="2" class="text-right">Acciones</th>
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
                @forelse ($data as $item)
                    <tr>
                        <td >{{$item->id}}</td>
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
                        {{-- <td style="text-align: left"> 
                            <small>Cargo: </small>{{$item->work}}
                            <br>
                            <small>Sueldo: </small>{{$item->totalSalary}}
                        </td>
                        <td>
                            <small>Fecha Inicio: </small> {{ date("d-m-Y", strtotime($item->dateStart)) }} <br>
                            <small>Fecha Finalización: </small> {{ date("d-m-Y", strtotime($item->dateFinish)) }}
                        </td> --}}

                        <td>{{$item->description}}</td>


                        <td class="no-sort no-click bread-actions text-right">    

                          
                            @if($item->deleted_at == NULL)

                                <a title="Borrar" class="btn btn-sm btn-danger delete" onclick="deleteItem('{{ route('hour.delete', ['hour' => $item->id]) }}')" data-toggle="modal" data-target="#delete-modal">
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

<script>x
   
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