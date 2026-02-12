@extends('voyager::master')

@section('page_title', 'Viendo Compra de sueldos')

@if (auth()->user()->hasPermission('browse_salary_purchases'))

@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0px">
                        <div class="col-md-4">
                            <h1 id="titleHead" class="page-title">
                                <i class="fa-solid fa-wallet"></i> Compra de Salarios
                            </h1>   
                        </div>
                        <div class="col-md-8 text-right" style="margin-top: 30px">
                            @if (auth()->user()->hasPermission('add_salary_purchases'))
                                <a href="{{ route('salary-purchases.create') }}" class="btn btn-success">
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
                            <div class="col-sm-7">
                                <div class="dataTables_length" id="dataTable_length">
                                    <label>Mostrar <select id="select-paginate" class="form-control input-sm">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select> registros</label>
                                </div>
                            </div>
                            
                            <div class="col-sm-2" style="margin-bottom: 10px">
                                <select name="status" class="form-control select2" id="select-status">
                                    <option value="">Todos</option>
                                    <option value="vigente" selected>Vigente</option>
                                    <option value="aprobado">Aprobado</option>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="pagado">Pagado</option>
                                    <option value="rechazado">Rechazado</option>
                                </select>
                            </div>

                            <div class="col-sm-3" style="margin-bottom: 10px">
                                <input type="text" id="input-search" placeholder="Buscar..." class="form-control">
                            </div>
                        </div>
                        <div class="row" id="div-results" style="min-height: 120px"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Create type items modal --}}
  
    @include('partials.modal-decline')
    @include('partials.modal-approve')
    @include('partials.modal-delete')


    <form action="#" id="deliverMoney_form" method="POST">
        {{ csrf_field() }}
        <div class="modal modal-success fade" data-backdrop="static" id="deliver-modal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><i class="fa-solid fa-money-check-dollar"></i> Entregar Dinero</h4>
                    </div>
                    <div class="modal-footer">    
                        <div class="text-center" style="text-transform:uppercase">
                            <i class="fa-solid fa-money-check-dollar" style="color: rgb(68, 68, 68); font-size: 5em;"></i>
                            <br>                                
                            <p><b>Desea entregar el prestamo?</b></p>                            
                            <input type="datetime-local" name="date" class="form-control">

                            <br>
                        </div>
                        
                        <input type="submit" class="btn btn-success pull-right" id="btn-submit-delivered" value="Sí, entregar">
                        <button type="button" class="btn btn-default pull-right btn-cancel-delivered" data-dismiss="modal">Cancelar</button>
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
        var countPage = 10, order = 'id', typeOrder = 'desc';

        $(document).ready(function(){
            list();
           
            $('#input-search').on('keyup', function(e){
                if(e.keyCode == 13) {
                    list();
                }
            });

            $('#select-status').change(function(){
                list();
            });

            $('#select-paginate').change(function(){
                countPage = $(this).val();
                list();
            });


        });

        function list(page = 1){
            $('#div-results').loading({message: 'Cargando...'});
            let status =$("#select-status").val();
            let url = "{{ route('salaryPurchase.list')}}";
            let search = $('#input-search').val() ? $('#input-search').val() : '';
            $.ajax({
                url: `${url}?paginate=${countPage}&page=${page}&status=${status}&search=${search}`,
                type: 'get',
                success: function(result){
                $("#div-results").html(result);
                $('#div-results').loading('toggle');
            }});
        }


        function declineItem(url){
            $('#decline_form').attr('action', url);
        }
        function approveItem(url){
            $('#approve_form').attr('action', url);
        }
        function deleteItem(url){
            $('#delete_form').attr('action', url);
        }

        function deliverItem(url){
            $('#deliverMoney_form').attr('action', url);
        }

        $('#deliverMoney_form').submit(function(e){
            $('#btn-submit-delivered').attr('disabled', true);
            $('#btn-submit-delivered').val('Guardando...');
        });


    </script>
@stop
@endif