@extends('voyager::master')

@section('page_title', 'Viendo Horarios')
@if (auth()->user()->hasPermission('browse_hours'))

@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0px">
                        <div class="col-md-8" style="padding: 0px">
                            <h1 id="titleHead" class="page-title">
                                <i class="fa-solid fa-clock"></i> Horarios
                            </h1>
                        </div>
                        <div class="col-md-4 text-right" style="margin-top: 30px">
                            @if (auth()->user()->hasPermission('add_hours'))
                                <a href="#" class="btn btn-success" data-target="#modal-create-customer" data-toggle="modal">
                                    <i class="voyager-plus"></i> <span>Crear</span>
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


    <div class="page-content browse container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-10">
                                <div class="dataTables_length" id="dataTable_length">
                                    <label>Mostrar <select id="select-paginate" class="form-control input-sm">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select> registros</label>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <input type="text" id="input-search" class="form-control" placeholder="Buscar...">
                            </div>
                        </div>
                        <div class="row" id="div-results" style="min-height: 120px"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form action="{{route('hours.store')}}" id="form-create-customer" method="POST">
    @csrf
        <div class="modal fade" tabindex="-1" id="modal-create-customer" role="dialog">
            <div class="modal-dialog modal-success">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="fa-solid fa-clock"></i> Agregar Horarios</h4>
                    </div>
                    <div class="modal-body">

                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <small>Nombre.</small>
                                    <input type="text" name="name" id="name" class="form-control" required placeholder="Nombre">
                                </div>
                            </div> 
                        </div>  

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <small>Hora de Entrada.</small>
                                    <input type="time" name="hourStart" id="hourStart" class="form-control">
                                </div>
                            </div> 
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <small>Hora de Salida.</small>
                                    <input type="time" name="hourFinish" id="hourFinish" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <small>Hora Tardia(Minutos).</small>
                                    <input type="number" value="0" min="0" step="1" name="minuteLate" id="minuteLate" class="form-control">
                                </div>
                            </div> 
                            <div class="col-sm-6">
                                <div class="form-group">
                                    {{-- <small>Hora Salida Temprana(Minuto).</small> --}}
                                    <input type="hidden" value="0" min="0" step="1" name="minuteEarly" id="minuteEarly" class="form-control">
                                </div>
                            </div>
                        </div>

                        <h4>Rango de Entrada.</h4>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <small>Empesando en.</small>
                                    <input type="time" name="rangeStartInput" id="rangeStartInput" class="form-control" required>
                                </div>
                            </div> 
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <small>Terminando en.</small>
                                    <input type="time" name="rangeStartOutput" id="rangeStartOutput" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <h4>Rango Salida.</h4>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <small>Terminando Afuera.</small>
                                    <input type="time" name="rangeFinishOutput" id="rangeFinishOutput" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    {{-- <small>Empesando en.</small> --}}
                                    {{-- <input type="time" name="rangeFinishInput" id="rangeFinishInput" class="form-control" required> --}}
                                </div>
                            </div> 
                            {{-- <div class="col-sm-6">
                                <div class="form-group">
                                    <small>Terminando Afuera.</small>
                                    <input type="time" name="rangeFinishOutput" id="rangeFinishOutput" class="form-control" required>
                                </div>
                            </div> --}}
                        </div>

                        <div class="row"> 
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <small>Tiempo</small>
                                    <select name="day" id="day" class="form-control select2">
                                        <option value="" disabled selected>Seleccione una opción</option>
                                        <option value="0.5">Medio Tiempo</option>
                                        <option value="1">Tiempo Completo</option>
                                    </select>
                                </div>
                            </div> 
                        </div> 
                        
                        <div class="form-group">
                            <small>Descripción</small>
                            <textarea name="description" id="description" class="form-control" rows="3" placeholder="Descripción del horario"></textarea>
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




 
@stop

@section('css')

@stop

@section('javascript')
    <script src="{{ url('js/main.js') }}"></script>
        
    {{-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script> --}}
    <script>
        var countPage = 10, order = 'id', typeOrder = 'desc';
        $(document).ready(() => {
            list();
            
            // $('.radio-type').click(function(){
            //     list();
            // });

            $('#input-search').on('keyup', function(e){
                if(e.keyCode == 13) {
                    list();
                }
            });


            // let txtInput = document.querySelector('#input-search');

            // txtInput.addEventListener('keyup',()=>{
            //     list();
                
            // });

            $('#select-paginate').change(function(){
                countPage = $(this).val();
               
                list();
            });
        });


        function list(page = 1){
            $('#div-results').loading({message: 'Cargando...'});
            let url = '{{ url("admin/hours/ajax/list") }}';

            let search = $('#input-search').val() ? $('#input-search').val() : '';

            $.ajax({
                url: `${url}/${search}?paginate=${countPage}&page=${page}`,
                type: 'get',
                
                success: function(result){
                    $("#div-results").html(result);
                    $('#div-results').loading('toggle');
                }
            });
        }

        function successItem(url){
            $('#success_form').attr('action', url);
        }

        function rechazarItem(url){
            $('#rechazar_form').attr('action', url);
        }

        function deleteItem(url){
            $('#delete_form').attr('action', url);
        }

       
    </script>
@stop
@endif