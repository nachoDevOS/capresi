@extends('voyager::master')

@section('page_title', 'Viendo Prestamos')

{{-- @if (auth()->user()->hasPermission('browse_loans')) --}}

@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0px">
                        <div class="col-md-8" style="padding: 0px">
                            <h1 id="titleHead" class="page-title">
                                <i class="fa-solid fa-hand-holding-dollar"></i> Recuperación de Prestamos
                            </h1>
                        </div>
                        <div class="col-md-4 text-right" style="margin-top: 30px">
                            <a href="{{route('loanRecoveries-list.print')}}" target="_blank" title="Imprimir"  class="btn btn-primary">
                                <i class="glyphicon glyphicon-print"></i>
                            </a>
                            @if (auth()->user()->hasPermission('add_hours'))
                                <a href="#" class="btn btn-success" data-target="#modal-create-customer" data-toggle="modal">
                                    <i class="voyager-plus"></i> <span>Agregar Prestamos a la Cartera</span>
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
                            <div class="col-sm-8">
                                <div class="dataTables_length" id="dataTable_length">
                                    <label>Mostrar <select id="select-paginate" class="form-control input-sm">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select> registros</label>
                                </div>
                            </div>
                            
                            <div class="col-sm-4" style="margin-bottom: 10px">
                                <input type="text" id="input-search" class="form-control" placeholder="Ingrese busqueda...">
                            </div>
                           
                        </div>
                        <div class="row" id="div-results" style="min-height: 120px"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<form action="{{route('loanRecoveries.store')}}" id="form-create-customer" method="POST">
    @csrf
        <div class="modal fade" tabindex="-1" id="modal-create-customer" role="dialog">
            <div class="modal-dialog modal-success">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="fa-solid fa-hand-holding-dollar"></i> Agregar Prestamos</h4>
                    </div>
                    <div class="modal-body">

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <small>Inicio.</small>
                                    <input type="date" name="start" id="start" class="form-control">
                                </div>
                            </div> 
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <small>Fin.</small>
                                    <input type="date" name="finish" id="finish" class="form-control">
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
    
@stop

@section('javascript')
    
    <script>
        var countPage = 10;

        $(document).ready(() => {
            list();
            $('#input-search').on('keyup', function(e){
                if(e.keyCode == 13) {
                    list();
                }
            });
            $('#select-paginate').change(function(){
                countPage = $(this).val();
                list();
            });
        });

        function list(page = 1){
            $('#div-results').loading({message: 'Cargando...'});
            let url = "{{ url('admin/loanRecoveries/ajax/list')}}";
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
    </script>
@stop
{{-- @endif --}}