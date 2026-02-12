@extends('voyager::master')

@section('page_title', 'Turnos')

@if (auth()->user()->hasPermission('add_shifts'))

    @section('page_header')

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0px">
                        <div class="col-md-6">
                            <h1 id="titleHead" class="page-title">
                                <i class="fa-solid fa-calendar-days"></i> Crear Turnos
                            </h1>
                            <a href="{{ route('shifts.index') }}" class="btn btn-warning">
                                <i class="fa-solid fa-rotate-left"></i> <span>Volver</span>
                            </a>
                        </div>
                        <div class="col-md-6 text-right" style="margin-top: 30px">
                            @if ( $shift && $shift->status == "pendiente")
                                <a href="#" data-toggle="modal" data-target="#confirm-modal" class="btn btn-success">
                                    <i class="fa-solid fa-floppy-disk"></i> <span>Guardar</span>
                                </a>
                                <a href="{{ route('shifts-hour.decline', ['id'=> $shift->id]) }}" class="btn btn-danger">
                                    <i class="voyager-trash"></i> <span>Descartar</span>
                                </a>
                            @endif
                        </div>  
                    </div>
                </div>
            </div>
        </div>
    </div>
        
    @stop

    @section('content')
        <div class="page-content edit-add container-fluid">    
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-bordered">
                            <div class="panel-body">
                                <input type="hidden" name="id" id="id" value="{{$shift?$shift->id:0}}">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <small>Nombre Del Turno</small>
                                        <input type="text" name="name" id="name" value="{{$shift?$shift->name:''}}" {{($shift && $shift->status == "aprobado")?'disabled':''}} onchange="save()" class="form-control text" required>
                                    </div>                                                                    
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <small>Descripción</small>
                                        <textarea name="description" id="description" onchange="save()" {{($shift && $shift->status == "aprobado")?'disabled':''}} class="form-control text" cols="30" rows="5">{{$shift?$shift->description:''}}</textarea>
                                    </div>
                                  
                                </div>
                                <div class="panel-body" style="padding: 0px">
                                    <div class="col-md-8" style="padding: 0px">
                                        <h1 id="titleHead" class="page-title">
                                            <i class="fa-solid fa-list"></i>Detalles
                                        </h1>
                                    </div>
                                    <div class="col-md-4 text-right" style="margin-top: 30px">
                                        @if ( !isset($shift) || $shift->status != "aprobado")
                                            <a href="#" class="btn btn-success" data-target="#modal-create-customer" data-toggle="modal">
                                                <i class="fa-solid fa-clock"></i> <span>Agregar</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="dataStyle" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th style="width: 10%">Días</th>
                                                <th style="width: 90%; text-align: center">Detalles</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $week=[
                                                        'Lunes','Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'
                                                    ];
                                                $i=0;
                                            @endphp         
                                            <tr>
                                                @foreach ($week as $item)
                                                    @php
                                                        $i++;
                                                        $n=1;
                                                    @endphp
                                                    <tr>
                                                        @if ($shiftsHour)
                                                            <td>{{$week[$i-1]}}</td>
                                                            @if ($shiftsHour->where('dayWeekNumber', $i))
                                                                <td>
                                                            @endif
                                                            
                                                            @foreach ($shiftsHour->where('dayWeekNumber', $i) as $item)     
                                                                @php
                                                                    $count = $shiftsHour->where('dayWeekNumber', $i)->count();
                                                                    // dump($count);
                                                                @endphp
                                                                <label >
                                                                    {{\Carbon\Carbon::createFromFormat('H:i:s', $item->hourStart)->format('h:i A')}} - 
                                                                    {{\Carbon\Carbon::createFromFormat('H:i:s', $item->hourFinish)->format('h:i A')}}

                                                                    <a data-toggle="modal" data-target="#hour-modal" >
                                                                        <i class="fa-solid fa-circle-info"></i>
                                                                    </a>
                                                                    
                                                                </label>
                                                                @if ( $shift && $shift->status == "pendiente")
                                                                    <a title="Borrar" class="btn btn-sm btn-danger delete" onclick="deleteItem('{{ route('shifts-hour.delete', ['shifts'=>$item->shifts_id,'shiftsHour' => $item->id]) }}')" data-toggle="modal" data-target="#delete-modal">
                                                                        <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm"></span>
                                                                    </a>                                                                    
                                                                @endif
                                                                @if ($n < $count)
                                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa-solid fa-right-left"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;                                                                   
                                                                @endif
                                                                @php
                                                                    $n++;
                                                                @endphp
                                                            @endforeach

                                                            @if ($shiftsHour->where('dayWeekNumber', $i))
                                                                </td>
                                                            @endif
                                                        @else
                                                            <td>{{$week[$i-1]}}</td>
                                                            <td></td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            </tr>

                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            
                        </div>
                    </div>
                </div>
        </div>

        <form class="form-submit" action="{{ route('shifts-hours.save') }}" method="POST">
            {{ csrf_field() }}
            <div class="modal fade" tabindex="-1" id="confirm-modal" role="dialog">
                <div class="modal-dialog modal-success">
                    <div class="modal-content ">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title"><i class="fa-solid fa-floppy-disk"></i> Guardar</h4>
                        </div>
                        <div class="modal-body">
                            <div class="text-center" style="text-transform:uppercase">
                                <i class="fa-solid fa-floppy-disk" style="color: #42d17f; font-size: 5em;"></i>
                                <br>
                                        
                                <p><b>Desea guardar el siguiente registro?</b></p>
                            </div>
                            <input type="hidden" name="shift" value="{{$shift?$shift->id:''}}">
                        </div>
                        <div class="modal-footer">

                            <input type="submit" class="btn btn-dark pull-right delete-confirm" value="Sí, guardar">
                            <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
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
                                    
                                    <p><b>Desea eliminar el siguiente horario?</b></p>
                                </div>
                            <input type="submit" class="btn btn-danger pull-right delete-confirm" value="Sí, eliminar">
                        </form>
                        <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>

            <form action="{{route('shifts-hours.store')}}" id="form-create-customer" method="POST">
            @csrf
                <div class="modal fade" tabindex="-1" id="modal-create-customer" role="dialog">
                    <div class="modal-dialog modal-success">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title"><i class="fa-solid fa-clock"></i> Agregar Horarios</h4>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="id1" id="id1" value="{{$shift?$shift->id:0}}">

                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="table-responsive">
                                            <table id="dataStyle" class="table table-bordered table-hover">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 5%; text-align: center"></th>
                                                        <th style="width: 55%">Horarios</th>
                                                        <th style="width: 20%">H. Inicio</th>
                                                        <th style="width: 20%">H. Finalización</th>
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
                                                        <td><small>{{ $item->name }}</small></td>
                                                        <td>{{\Carbon\Carbon::createFromFormat('H:i:s', $item->hourStart)->format('h:i A')}}</td>
                                                        <td>{{\Carbon\Carbon::createFromFormat('H:i:s', $item->hourFinish)->format('h:i A')}}</td>                                                        
                                                    </tr>
                                                    @empty
                                                        
                                                    @endforelse        
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="table-responsive">
                                            <table id="dataStyle" class="table table-bordered table-hover">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 10%; text-align: center"></th>
                                                        <th style="width: 90%">Días</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>
                                                            <label>
                                                                <input type="checkbox" name="day[]" id="day-1"  value="1">
                                                            </label>
                                                        </td>
                                                        <td>Lunes</td>
                                                    </tr>
                                                    
                                                    <tr>
                                                        <td>
                                                            <label>
                                                                <input type="checkbox" name="day[]" id="day-2"  value="2">
                                                            </label>
                                                        </td>
                                                        <td>Martes</td>
                                                    </tr>

                                                    <tr>
                                                        <td>
                                                            <label>
                                                                <input type="checkbox" name="day[]" id="day-3"  value="3">
                                                            </label>
                                                        </td>
                                                        <td>Miercoles</td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <label>
                                                                <input type="checkbox" name="day[]" id="day-4"  value="4">
                                                            </label>
                                                        </td>
                                                        <td>Jueves</td>
                                                    </tr>

                                                    <tr>
                                                        <td>
                                                            <label>
                                                                <input type="checkbox" name="day[]" id="day-5"  value="5">
                                                            </label>
                                                        </td>
                                                        <td>Viernes</td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <label>
                                                                <input type="checkbox" name="day[]" id="day-6"  value="6">
                                                            </label>
                                                        </td>
                                                        <td>Sabado</td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <label>
                                                                <input type="checkbox" name="day[]" id="day-7"  value="7">
                                                            </label>
                                                        </td>
                                                        <td>Domingo</td>
                                                    </tr>
        
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

    @endsection

    @section('javascript')
        <script>

            $(document).ready(function(){
                
            })

            function deleteItem(url){
                $('#delete_form').attr('action', url);
            }

            function save()
            {
                let id = $(`#id`).val();
                let name = $(`#name`).val()?$(`#name`).val():0;
                let description = $(`#description`).val()?$(`#description`).val():0;

                // alert(description);

                $.get('{{route('shifts.name')}}/'+id+'/'+name+'/'+description, function(data){ 
                    // toastr.success('Producto agregado..', 'Información');
                    $('#id').val(data);
                    $('#id1').val(data);


                });
            }

          


        </script>
    @stop
@endif