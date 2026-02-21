@extends('voyager::master')

@section('page_title', 'Viendo Inventarios')

@if (auth()->user()->hasPermission('browse_inventories'))

@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0px">
                        <div class="col-md-8">
                            <h1 id="titleHead" class="page-title">
                                <i class="fa-solid fa-box-open"></i> Registro de Inventario
                            </h1>
                        </div>
                        <div class="col-md-4 text-right" style="margin-top: 30px">
                            @if (auth()->user()->hasPermission('add_inventories'))
                                <a href="{{ route('inventories.create') }}" class="btn btn-success">
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
                                    <option value="disponible" selected>Disponibles</option>
                                    <option value="vendido">Vendidos</option>
                                    <option value="eliminado">Eliminados</option>
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





    <form action="#" id="price_form" method="POST">
        {{ csrf_field() }}
        <div class="modal fade modal-info" id="price-modal" data-backdrop="static" role="dialog">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content ">
                    <div class="modal-header ">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title" id="inventoryModalLabel">
                            <i class="fa-solid fa-pen-to-square"></i> Precio
                        </h4>
                    </div>
    
                    <div class="modal-footer">
                        

                        <div class="form-group" style="text-align: left">
                            <div class="col-sm-4">
                                <small for="">Cantidad</small>
                                <input type="number" style="text-align: right" id="input-show-quantity" class="form-control text" disabled>
                                <input type="hidden" id="input-quantity" name="quantity" class="form-control text">
                            </div>
                            <div class="col-sm-4">
                                <small for="">Precio</small>
                                <input type="number" onkeyup="getSubtotal()" onchange="getSubtotal()" style="text-align: right" name="price" min="0" step="0.01" id="input-price" class="form-control text" required>

                            </div>
                            <div class="col-sm-4">
                                <small for="">Total</small>
                                <input type="number" id="input-show-amountTotal" class="form-control text" style="text-align: right" disabled>
                                <input type="hidden" id="input-amountTotal" name="amountTotal" class="form-control text">


                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <input type="submit" class="btn btn-primary" value="Sí, actualizar">
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
                        <input type="hidden" name="id" id="id">

                            <div class="text-center" style="text-transform:uppercase">
                                <i class="voyager-trash" style="color: red; font-size: 5em;"></i>
                                <br>
                                
                                <p><b>Desea eliminar el siguiente registro?</b></p>
                            </div>
                        <input type="submit" class="btn btn-danger pull-right delete-confirm" value="Sí, eliminar">
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
                    <h4 class="modal-title"><i class="fa-solid fa-thumbs-down"></i> Desea rechazar el siguiente registro?</h4>
                </div>
                <div class="modal-footer">
                    <form action="#" id="rechazar_form" method="GET">
                        {{ csrf_field() }}
                        <input type="hidden" name="id" id="id">

                            <div class="text-center" style="text-transform:uppercase">
                                <i class="fa-solid fa-thumbs-down" style="color: #353d47; font-size: 5em;"></i>
                                <br>
                                
                                <p><b>Desea rechazar el siguiente registro?</b></p>
                            </div>
                        <input type="submit" class="btn btn-dark pull-right delete-confirm" value="Sí, rechazar">
                    </form>
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal modal-dark fade" data-backdrop="static" tabindex="-1" id="success-modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="fa-solid fa-money-check-dollar"></i> Aprobar Prestamo</h4>
                </div>
                <div class="modal-footer">
                    <form action="#" id="success_form" method="GET">
                        {{ csrf_field() }}
                        <input type="hidden" name="id" id="id">

                            <div class="text-center" style="text-transform:uppercase">
                                <i class="fa-solid fa-money-check-dollar" style="color: rgb(68, 68, 68); font-size: 5em;"></i>
                                <br>
                                
                                <p><b>Desea aprobar el prestamo?</b></p>
                            </div>
                        <input type="submit" class="btn btn-dark pull-right delete-confirm" value="Sí, aprobar">
                    </form>
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>


    {{-- Para el Codigo Manual --}}
    <div class="modal modal-dark fade" data-backdrop="static" tabindex="-1" id="code-modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="fa-solid fa-mobile"></i> Agregar Codigo Manual</h4>
                </div>
                <div class="modal-footer">
                    <form action="#" id="code_form" method="POST">
                        {{ csrf_field() }}
                        {{-- <input type="hidden" name="id" id="id">     --}}
                        <label for="">Codigo Manual</label>
                        <input type="text" class="form-control" name=codeManual>
                        <br>
                        <input type="submit" class="btn btn-dark pull-right delete-confirm" value="Sí, Actualizar">
                    </form>
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    {{-- <style>

    
    </style> --}}
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

            $('.form-submit').submit(function(e){
                e.preventDefault();
                $.post($(this).attr('action'), $(this).serialize(), function(res){
                    if(res.success){
                        list();
                        $('#payment-modal').modal('hide');
                        toastr.success('Registro exitoso', 'Bien hecho');
                    }else{
                        toastr.error('Ocurrió un error', 'Error');
                    }
                    $('.form-submit .btn-submit').removeAttr('disabled');
                });
            });
        });

        function list(page = 1){
            $('#div-results').loading({message: 'Cargando...'});
            let status =$("#select-status").val();
            let url = "{{ route('inventories.list')}}";
            let search = $('#input-search').val() ? $('#input-search').val() : '';
            $.ajax({
                url: `${url}?paginate=${countPage}&page=${page}&status=${status}&search=${search}`,
                type: 'get',
                success: function(result){
                $("#div-results").html(result);
                $('#div-results').loading('toggle');
            }});
        }

        // function deleteItem(url){
        //     $('#delete_form').attr('action', url);
        // }
        function priceItem(url, quantity, price){
            $(`#input-price`).val((price).toFixed(2));
            
            $(`#input-quantity`).val((quantity).toFixed(2));
            $(`#input-show-quantity`).val((quantity).toFixed(2));

            $(`#input-amountTotal`).val((price * quantity).toFixed(2));
            $(`#input-show-amountTotal`).val((price * quantity).toFixed(2));
            
            $('#price_form').attr('action', url);
        }

        function getSubtotal(){
            let price = $(`#input-price`).val() ? parseFloat($(`#input-price`).val()) : 0;
            let quantity = $(`#input-quantity`).val() ? parseFloat($(`#input-quantity`).val()) : 0;

            $(`#input-amountTotal`).val((price * quantity).toFixed(2));
            $(`#input-show-amountTotal`).val((price * quantity).toFixed(2));
        }




    </script>
@stop
@endif