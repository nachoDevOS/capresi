@extends('voyager::master')

@section('page_title', 'Viendo Turnos')
@if (auth()->user()->hasPermission('browse_shifts'))
@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0px">
                        <div class="col-md-8" style="padding: 0px">
                            <h1 id="titleHead" class="page-title">
                                <i class="fa-solid fa-calendar-days"></i> Turnos
                            </h1>
                        </div>
                        <div class="col-md-4 text-right" style="margin-top: 30px">
                            @if (auth()->user()->hasPermission('add_shifts'))
                                <a href="{{ route('shifts.create') }}" class="btn btn-success">
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


    <form action="#" id="delete_form" method="POST">
        {{ method_field('DELETE') }}
        {{ csrf_field() }}
        <div class="modal modal-danger fade" data-backdrop="static" tabindex="-1" id="delete-modal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="voyager-trash"></i> Desea eliminar el siguiente registro?</h4>
                    </div>
                    <div class="modal-body">
                        <div class="text-center" style="text-transform:uppercase">
                            <i class="voyager-trash" style="color: red; font-size: 5em;"></i>
                            <br>
                                    
                            <p><b>Desea eliminar el siguiente registro?</b></p>
                        </div>
                        <div class="row text-left">
                            <div class="form-group col-md-12">
                                <small>Observación</small>
                                <textarea name="deletedObservation" id="deletedObservation" class="form-control text" cols="30" rows="5"></textarea>
                            </div>                                    
                        </div>
                    </div>

                    <div class="modal-footer">
                        <input type="submit" class="btn btn-dark pull-right delete-confirm" value="Sí, eliminar">
                        <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </form>



 
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
            let url = '{{ url("admin/shifts/ajax/list") }}';

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


        function deleteItem(url){
            $('#delete_form').attr('action', url);
        }
        

       
    </script>
@stop
@endif