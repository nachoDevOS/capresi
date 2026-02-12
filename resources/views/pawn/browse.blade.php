@extends('voyager::master')

@section('page_title', 'Viendo Registros de Empeño')

@if (auth()->user()->hasPermission('browse_pawn'))

@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0px">
                        <div class="col-md-4">
                            <h1 id="titleHead" class="page-title">
                                <i class="fa-solid fa-handshake"></i> Empeño
                            </h1>
                        </div>
                        <div class="col-md-8 text-right" style="margin-top: 30px">
                            @if (auth()->user()->hasPermission('add_pawn'))
                                <a href="{{ route('pawn.create') }}" class="btn btn-success">
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
                                    <option value="entregado" selected>En Pagos</option>
                                    <option value="aprobado">Por Entregar</option>
                                    <option value="pendiente">Por aprobar</option>
                                    <option value="recogida">Pagado</option>
                                    <option value="concluido">Concluido/Expirado</option>
                                    <option value="rechazado">Rechazado</option>
                                    <option value="inventario">Inventario</option>
                                </select>
                            </div>

                            <div class="col-sm-3" style="margin-bottom: 10px">
                                <input type="text" id="input-search" class="form-control" placeholder="Ingrese busqueda...">
                            </div>  
                        </div>
                        <div class="row" id="div-results" style="min-height: 120px"></div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Create type items modal --}}
    <form action="{{ route('pawn.payment') }}" id="form-payment" class="form-submit" method="POST">
        @csrf
        <div class="modal modal-primary fade" tabindex="-1" id="payment-modal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="voyager-dollar"></i> Registrar pago</h4>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id">
                        <div class="form-group">
                            <label for="date">Fecha de pago</label>
                            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="amount">Monto</label>
                            <input type="number" name="amount" min="0.1" step="0.1" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="observations">Observaciones</label>
                            <textarea name="observations" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <input type="submit" class="btn btn-dark btn-submit" value="Guardar">
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
        <form action="#" id="deliver_form" method="POST">
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
                                    
                                <p><b>Desea entregar el dinero al cliente?</b></p>
                                <input type="date" name="date" class="form-control">
                                <br>
                            </div>
                            <div id="progress-container" style="display: none; margin-top: 15px;">
                                <small>Procesando...</small>
                                <div class="progress">
                                    <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <button type="submit" id="btn-submit-delivered" style="display:block" class="btn btn-success pull-right delete-confirm">Sí, entregar</button>
                            <button type="button" class="btn btn-default pull-right btn-cancel-delivered" data-dismiss="modal">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

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
    <style>

        .progress {
            height: 20px;
            background-color: #f3f3f3;
            border-radius: 5px;
            overflow: hidden;
        }

        .progress-bar {
            background-color: #42d17f !important; /* Color verde */
            height: 100%;
            transition: width 0.3s;
        }

        @media (max-width: 767px) {
            .table-responsive .dropdown-menu {
                position: static !important;
            }
        }
        @media (min-width: 768px) {
            .table-responsive {
                overflow: visible;
            }
        }
    </style>

    
@stop

@section('javascript')
    <script>
        $(document).ready(function(){
            document.getElementById('btn-submit-delivered').addEventListener('click', function() {
                    $('.btn-cancel-delivered').attr('disabled', true);
                    $('.close-delivered').attr('disabled', true);
                    this.textContent = 'Guardando...';
                    this.disabled = true;
                    this.closest('form').submit();

                    const progressContainer = document.getElementById('progress-container');
                    const progressBar = document.getElementById('progress-bar');
                    progressContainer.style.display = 'block';

                    // Simular progreso
                    let progress = 0;
                    const interval = setInterval(function () {
                        progress += 10;
                        progressBar.style.width = progress + '%';
                        progressBar.setAttribute('aria-valuenow', progress);

                        // Finalizar progreso
                        if (progress >= 100) {
                            clearInterval(interval);

                            // Simular finalización del proceso (ejemplo: cerrar modal)
                            setTimeout(() => {
                                $('#confirm-modal').modal('hide');
                            }, 500);
                        }
                    }, 30);
            });
        })
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
            let url = "{{ route('pawn.list')}}";
            let search = $('#input-search').val() ? $('#input-search').val() : '';
            $.ajax({
                url: `${url}?paginate=${countPage}&page=${page}&status=${status}&search=${search}`,
                type: 'get',
                success: function(result){
                $("#div-results").html(result);
                $('#div-results').loading('toggle');
            }});
        }

        function deleteItem(url){
            $('#delete_form').attr('action', url);
        }
        function rechazarItem(url){
            $('#rechazar_form').attr('action', url);
        }
        function successItem(url){
            $('#success_form').attr('action', url);
        }

        function codeItem(url){
            $('#code_form').attr('action', url);
        }

        function deliverItem(url, id, amountTotal){
            $('#deliver_form').attr('action', url);
        }
    </script>
@stop
@endif