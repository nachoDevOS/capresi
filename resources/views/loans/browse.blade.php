@extends('voyager::master')

@section('page_title', 'Viendo Prestamos')


@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0px">
                        <div class="col-md-4" style="padding: 0px">
                            <h1 id="titleHead" class="page-title">
                                <i class="fa-solid fa-hand-holding-dollar"></i> Prestamos Diario
                            </h1>
                        </div>
                        <div class="col-md-8 text-right" style="margin-top: 30px">
                            @if (auth()->user()->hasPermission('add_loans'))
                                <a href="{{ route('loans.create') }}" class="btn btn-success">
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
                                    @if (!auth()->user()->hasRole('cobrador'))
                                        <option value="aprobado">Por Entregar</option>
                                        <option value="verificado">Por Aprobar</option>
                                        <option value="pendiente">Pendientes</option>
                                        <option value="rechazado">Rechazados</option>
                                    @endif
                                    <option value="pagado">Pagados</option>
                                </select>
                            </div>
                            
                            <div class="col-sm-3" style="margin-bottom: 10px">
                                <input type="text" id="input-search" placeholder="🔍 Buscar..." class="form-control">
                            </div>
                        </div>
                        <div class="row" id="div-results" style="min-height: 120px"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>




    <div class="modal modal-primary fade" data-backdrop="static" tabindex="-1" id="agent-modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><span class="icon fa-solid fa-person-digging"></span> Agente</h4>
                </div>
                <div class="modal-body">
                    <form action="#" id="agent_form" method="POST">
                        {{ csrf_field() }}
                    <div class="row">
                        <div class="form-group col-md-6">
                            <small>Cobrador Asignado al Prestamo</small>
                            <select name="loan_id" id="loan_id" class="form-control select2" required>
                                <option value="" disabled selected>-- Selecciona a la persona --</option>
                                @foreach ($collector as $item)
                                    @if ($item->role)
                                        <option value="{{$item->id}}">{{$item->name}}</option>                                                
                                    @endif
                                @endforeach
                            </select>
                        </div> 
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            {{-- <label for="observation"></label> --}}
                            <small>Observación</small>
                            <textarea name="observation" id="observation" class="form-control text" cols="30" rows="5"></textarea>
                        </div>                                  
                    </div>
                </div>
                <div class="modal-footer">
                    <form action="#" id="agent_form" method="POST">
                        {{ csrf_field() }}
                        
                        <input type="submit" class="btn btn-dark pull-right delete-confirm" value="Sí, cambiar">
                    </form>
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>


    @include('partials.modal-delete')
    @include('partials.modal-deliverMoney')
    @include('partials.modal-decline')
    @include('partials.modal-approve')
    {{-- <div class="modal modal-dark fade" data-backdrop="static" tabindex="-1" id="success-modal" role="dialog">
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
    </div> --}}


    {{-- modal para destruir un prestamo  con caja cerrada --}}
    {{-- <form action="#" id="destroy_form" method="POST">
        {{ method_field('DELETE') }}
        {{ csrf_field() }}
        <div class="modal modal-danger fade" data-backdrop="static" tabindex="-1" id="destroy-modal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="voyager-trash"></i> Desea eliminar el siguiente registro?</h4>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <strong>Aviso: </strong>
                            <p> Usted esta eliminando un prestamo que ha sido entregado al beneficiario, por lo tanto al eliminar el prestamo usted debera contar usted con caja abierta para realizar la eliminacion de prestamo. </p>
                        </div> 

                        <div class="text-center" style="text-transform:uppercase">
                            <i class="voyager-trash" style="color: red; font-size: 5em;"></i>
                        </div>
                        <div class="form-group">
                            <label for="observation">Motivo</label>
                            <textarea name="destroyObservation" class="form-control" rows="5" placeholder="Describa el motivo de la anulación del prestamo" required></textarea>
                        </div>
                        <label class="checkbox-inline"><input type="checkbox" value="1" required>Confirmar eliminacion..!</label>
                    </div>

                    <div class="modal-footer">
                        <input type="submit" class="btn btn-danger pull-right delete-confirm" value="Sí, eliminar">
                        
                        <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </form> --}}

    


    <div class="modal modal-success fade" data-backdrop="static" tabindex="-1" id="notificar-modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="fa-brands fa-square-whatsapp"></i> Notificar</h4>
                </div>
                <div class="modal-body">
                        {{-- <input type="hidden" id="id"> --}}
                        <input type="hidden" id="phone">
                        <input type="hidden" id="name">
                </div>   
                
                <div class="modal-footer">
                    <div class="text-center" style="text-transform:uppercase">
                        <i class="fa-brands fa-square-whatsapp" style="color: #52ce5f; font-size: 5em;"></i>
                        <br>
                        <p><b>Desea notificar al beneficiario?</b></p>
                    </div>
                    <input type="submit" class="btn btn-success pull-right delete-confirm" onclick="miFunc()" value="Sí, Enviar">
                    
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <form action="#" id="notification_form" method="GET">
        {{ csrf_field() }}
        <div class="modal modal-success fade" data-backdrop="static" tabindex="-1" id="enableNotification-modal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="fa-brands fa-square-whatsapp"></i> Notificaciones</h4>
                    </div>
                    <div class="modal-footer">
                        <input type="submit" class="btn btn-success pull-right delete-confirm" value="Sí, Actualizar">
                        
                        <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    


    
    





    {{-- Cambio de perido de pago --}}
    <form class="form-submit" id="form-payments-period" action="{{ route('loans.update.payments-period') }}" method="post">
        @csrf
        <input type="hidden" name="id">
        <div class="modal modal-dark fade" tabindex="-1" id="payments-period-modal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="voyager-calendar"></i> Actualizar periodo de pago</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <small>Perido de pago</small>
                            <select name="payments_period_id" id="select-payments_period_id" class="form-control">
                                <option value="" selected>Diaria</option>
                                @foreach (App\Models\PaymentsPeriod::where('status', 1)->get() as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>  
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
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
    
    
    </style>
@stop

@section('javascript')
    <!-- Incluir el nuevo archivo JS de impresión -->
    <script src="{{ asset('js/print.js') }}"></script>

    <script>
        //para inpresion cuando es entregado elñ prestamo para que imprima
        $(document).ready(function(){

            $('#select-payments_period_id').select2({dropdownParent: '#payments-period-modal'});

            @if(session('loan'))
                printTicket('{{ setting('servidores.print') }}', @json(json_decode(session('loan'), true)), '{{ url('admin/loans/comprobante/print') }}', 'LoanComprobante');
            @endif

        });
        


        async function handlePrintClick(element, printUrl, sale, fallbackUrl) {
            const button = $(element);
            const icon = button.find('i');
            const originalIconClass = icon.attr('class');

            // Evitar múltiples clics si ya se está procesando
            if (button.hasClass('disabled')) {
                return;
            }

            // Cambiar ícono a spinner y desactivar botón
            button.addClass('disabled');
            icon.removeClass(originalIconClass).addClass('fa-solid fa-spinner fa-spin');

            try {
                await printTicket(printUrl, sale, fallbackUrl, 'LoanComprobante');
            } finally {
                // Restaurar el botón después de un par de segundos para que el usuario vea el resultado (toastr)
                setTimeout(() => {
                    button.removeClass('disabled');
                    icon.removeClass('fa-solid fa-spinner fa-spin').addClass(originalIconClass);
                }, 2000);
            }
        }

    </script>
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

        $(document).ready(() => {
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
            let type =$("#select-status").val();

            let url = "{{ url('admin/loans/ajax/list')}}";
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

        function deleteItem(url){
            $('#delete_form').attr('action', url);
        }

        //Para la destruccion de un prestamos pero con caja cerrada 
        // function destroyItem(url){
        //     $('#destroy_form').attr('action', url);
        // }
        
        function declineItem(url){
            $('#decline_form').attr('action', url);
        }
        function approveItem(url){
            $('#approve_form').attr('action', url);
        }

        function agentItem(url){
            $('#agent_form').attr('action', url);
        }

        function loanNotification(url){
            $('#notification_form').attr('action', url);
        }

        var loanC = 0;

    
        function deliverItem(url){
            $('#deliverMoney_form').attr('action', url);
        }

        $('#notificar-modal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget) 
            var phone = button.data('phone')
            var name = button.data('name')
            var modal = $(this)
            modal.find('.modal-body #name').val(name)
            modal.find('.modal-body #phone').val(phone)
        });

        $('#enableNotification-modal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget) 
            var id = button.data('id')
            var modal = $(this)
            modal.find('.modal-body #id').val(id)
        });

        function loan(id){
            loanC = id;
            printContract();
        }

        function printContract(){
            window.open("{{ url('admin/loans/contract/daily') }}/"+loanC, "Recibo", `width=700, height=500`)
        }

    </script>
@stop
