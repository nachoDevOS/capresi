@extends('voyager::master')

@section('page_title', 'Pagos de Planillas')
@if (auth()->user()->hasPermission('browse_bonus'))
@section('page_header')

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0px">
                        <div class="col-md-8" style="padding: 0px">
                            <h1 id="titleHead" class="page-title">
                                <i class="fa-solid fa-file-invoice-dollar"></i> Pagos de Planillas
                            </h1> 
                        </div>
                        <div class="col-md-4" style="margin-top: 30px">
                            <form name="form_search" id="form-search" action="{{ route('paymentSheet.list') }}" method="post">
                                @csrf
                                <input type="hidden" name="print">

                                <div class="form-group">
                                    <div class="form-line">
                                        <small>Tipo</small>
                                        <select name="type" class="form-control select2" required>
                                            <option value="periodo">Periodo</option>                                          
                                            <option value="aguinaldo">Aguinaldo</option>  
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="form-line">
                                        <small for="customer_id">Ci / Nombre del Empleado</small>
                                        <select name="people_id" class="form-control" id="select_people_id" required></select>
                                    </div>
                                </div>
                                
                                <div class="text-right">
                                    <button type="submit" class="btn btn-primary" style="padding: 5px 10px"> <i class="voyager-settings"></i> Generar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="page-content browse container-fluid">
        @include('voyager::alerts')
        <div class="row">
            <div id="div-results" style="min-height: 100px">
                
            </div>
        </div>
    </div>
    <div class="modal modal-success fade" data-backdrop="static" tabindex="-1" id="confirmationModal" role="dialog">
        <div class="modal-dialog modal-success">
            <div class="modal-content modal-success">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="confirmationModalLabel"><i class="fa-solid fa-file-invoice-dollar"></i> Pagar Sueldo</h4>
                </div>
                <div class="modal-body">
                    <div class="text-center" style="text-transform:uppercase">
                    <i class="fa-solid fa-file-invoice-dollar" style="color: rgb(68, 68, 68); font-size: 5em;"></i>
                    <br>
                    <p><b>Desea pagar?</b></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="submit" class="btn btn-success pull-right delete-confirm" id="confirmPrintButton" value="Sí, pagar">
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>

    </style>
@stop

@section('javascript')
    <script>
        $(document).ready(function() {

            $('#form-search').on('submit', function(e){
                e.preventDefault();
                $('#div-results').loading({message: 'Cargando...'});
                $.post($('#form-search').attr('action'), $('#form-search').serialize(), function(res){
                    $('#div-results').html(res);
                })
                .fail(function() {
                    toastr.error('Ocurrió un error!', 'Oops!');
                })
                .always(function() {
                    $('#div-results').loading('toggle');
                    $('html, body').animate({
                        scrollTop: $("#div-results").offset().top - 70
                    }, 500);
                });
            });

            $('#select_people_id').select2({
                    placeholder: '<i class="fa fa-search"></i> Buscar...',
                    escapeMarkup : function(markup) {
                        return markup;
                    },
                    language: {
                        inputTooShort: function (data) {
                            return `Por favor ingrese ${data.minimum - data.input.length} o más caracteres`;
                        },
                        noResults: function () {
                            return `<i class="far fa-frown"></i> No hay resultados encontrados`;
                        }
                    },
                    quietMillis: 250,
                    minimumInputLength: 2,
                    ajax: {
                        url: "{{ url('admin/contractPeople/ajax') }}",        
                        processResults: function (data) {
                            let results = [];
                            data.map(data =>{
                                results.push({
                                    ...data,
                                    disabled: false
                                });
                            });
                            return {
                                results
                            };
                        },
                        cache: true
                    },
                    templateResult: formatResultCustomers_people,
                    templateSelection: (opt) => {
                        return opt.first_name?opt.first_name+' '+opt.last_name1+' '+opt.last_name2:'<i class="fa fa-search"></i> Buscar... ';
                    }
                }).change(function(){
                
                });
        });



        
        function formatResultCustomers_people(option){
            // Si está cargando mostrar texto de carga
            if (option.loading) {
                return '<span class="text-center"><i class="fas fa-spinner fa-spin"></i> Buscando...</span>';
            }
            let image = "{{ asset('images/default.jpg') }}";
            if(option.image){
                image = "{{ asset('storage') }}/"+option.image.replace('.', '-cropped.');
            }                
                // Mostrar las opciones encontradas
            return $(`  <div style="display: flex">
                            <div style="margin: 0px 10px">
                                <img src="${image}" width="50px" />
                            </div>
                            <div>
                                <small>CI: </small><b style="font-size: 15px; color: black">${option.ci?option.ci:'No definido'}</b><br>
                                <b style="font-size: 15px; color: black">${option.first_name} ${option.last_name1} ${option.last_name2} </b>
                            </div>
                        </div>`);
        }


    let selectId = null; // Variable para guardar el contrato seleccionado
    let selectType= null;

    // Mostrar el modal de confirmación
    function showConfirmationModal(type, id) {
        selectId = id; // Guardar el contrato en la variable
        selectType= type;
        $('#confirmationModal').modal('show'); // Mostrar el modal (asegúrate de usar Bootstrap)

    }

    // Llamar a openPrinf al confirmar
    document.getElementById('confirmPrintButton').addEventListener('click', function () {
        if (selectId && selectType) {
            openPrinf(selectType, selectId); // Llamar a la función para guardar y imprimir
        }
        $('#confirmationModal').modal('hide'); // Cerrar el modal
    });


    function openPrinf(types, id){
        // window.open("{{ url('admin/paymentSheet/print') }}/"+id, "Recibo", `width=800, height=700`);
        // alert(types)
        $.ajax({
            url: "paymentSheet/"+types+"/save/"+id, // La ruta de tu controlador
            type: 'GET',
            success: function(response) {
                if (response.viewclose) {
                    // Cerrar la ventana
                    toastr.warning(response.message, response.type);
                    window.close();
                }
                else
                {
                    $('#div-results').loading({message: 'Sin registro'});
                    $('#div-results').html('');


                    toastr.success(response.message, response.type);
                    
                    window.open("{{ url('admin/paymentSheet/print') }}/"+types+"/"+id, "Comprobante de Pago", `width=800, height=700`);

                }
            },
            error: function(xhr, status, error) {
                console.log('Error:', error);
            }
        });
    }

        


        
    </script>
@stop
@endif