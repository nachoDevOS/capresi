@extends('voyager::master')

@section('page_title', 'Viendo Planilla')
@if (auth()->user()->hasPermission('browse_spreadsheets'))
@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0px">
                        <div class="col-md-8" style="padding: 0px">
                            <h1 id="titleHead" class="page-title">
                                <i class="icon fa-solid fa-sheet-plastic"></i> Planillas
                            </h1>
                        </div>
                        <div class="col-md-4 text-right" style="margin-top: 30px">
                            @if (auth()->user()->hasPermission('add_spreadsheets'))
                                <a href="{{ route('spreadsheets.create') }}" class="btn btn-success">
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
                            <div class="col-md-12 text-right">
                                
                                <label class="radio-inline"><input type="radio" class="radio-type" name="optradio" value="todo">Todos</label>
                                <label class="radio-inline"><input type="radio" class="radio-type" name="optradio" value="aprobado" checked>Aprobadas</label>
                                <label class="radio-inline"><input type="radio" class="radio-type" name="optradio" value="pendiente">Pendientes</label>
                                <label class="radio-inline"><input type="radio" class="radio-type" name="optradio" value="eliminado">Eliminados</label>
                            </div>
                        </div>
                        <div class="row" id="div-results" style="min-height: 120px"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal modal-info fade" data-backdrop="static" tabindex="-1" id="success-modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="fa-solid fa-file-csv"></i> Planilla</h4>
                </div>
                <div class="modal-footer">
                    <form action="#" id="success_form" method="GET">
                        {{ csrf_field() }}
                        <input type="hidden" name="id" id="id">

                            <div class="text-center" style="text-transform:uppercase">
                                <i class="fa-solid fa-file-csv" style="color: rgb(68, 68, 68); font-size: 5em;"></i>
                                <br>
                                
                                <p><b>Desea generar la planilla?</b></p>
                            </div>
                        <input type="submit" class="btn btn-info pull-right delete-confirm" value="Sí, generar">
                    </form>
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- para rechazar --}}
    <div class="modal modal-primary fade" data-backdrop="static" tabindex="-1" id="rechazar-modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="fa-solid fa-file-signature"></i> Rechazar</h4>
                </div>
                <div class="modal-footer">
                    <form action="#" id="rechazar_form" method="GET">
                        {{ csrf_field() }}
                            <div class="text-center" style="text-transform:uppercase">
                                <i class="fa-solid fa-file-csv" style="color: #353d47; font-size: 5em;"></i>
                                <br>
                                
                                <p><b>Desea rechazar?</b></p>
                            </div>
                            {{-- <div class="row text-left">
                                <div class="form-group col-md-12">
                                    <small>Observación</small>
                                    <textarea name="observation" id="observation" class="form-control text" cols="30" rows="5"></textarea>
                                </div>                                    
                            </div> --}}
                        <input type="submit" class="btn btn-dark pull-right delete-confirm" value="Sí, rechazar">
                    </form>
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

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
                                
                                <p><b>Desea eliminar el siguiente registro?</b></p>
                            </div>
                            <div class="row text-left">
                                <div class="form-group col-md-12">
                                    <small>Observación</small>
                                    <textarea name="observation" id="observation" class="form-control text" cols="30" rows="5"></textarea>
                                </div>                                    
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
            
            $('.radio-type').click(function(){
                list();
            });

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
            let type = $(".radio-type:checked").val();
            
            let url = '{{ url("admin/spreadsheets/ajax/list") }}';

            let search = $('#input-search').val() ? $('#input-search').val() : '';

            $.ajax({
                url: `${url}/${type}/${search}?paginate=${countPage}&page=${page}`,
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