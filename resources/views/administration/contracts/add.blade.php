@extends('voyager::master')

@section('page_title', 'Crear contrato')

@if (auth()->user()->hasPermission('add_contracts'))
    @section('page_header')
        <h1 id="titleHead" class="page-title">
            <i class="fa-solid fa-file-signature"></i>Crear Contratos
        </h1>
        <a href="{{ route('contracts.index') }}" class="btn btn-warning">
            <i class="fa-solid fa-rotate-left"></i> <span>Volver</span>
        </a>
    @stop

    @section('content')
        <div class="page-content edit-add container-fluid">    
            <form id="form-create-contract" action="{{ route('contracts.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-bordered">
                            <div class="panel-body">        
                                <h5>Datos Personales</h5>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <small for="customer_id">Beneficiario del Prestamo</small>
                                        <select name="people_id" class="form-control" id="select_people_id" required></select>
                                    </div>
                                 
                                </div>

                                <h5>Datos del Contrato</h5>

                                <div class="row">
                                    <div class="form-group col-md-3">
                                        <small>Cargos</small>
                                        <select name="employeJob" id="employeJob" class="form-control select2" required>
                                            <option value="" disabled selected>-- Selecciona un cargo --</option>
                                            @foreach ($employeJob as $item)
                                                <option value="{{$item->id}}"><small>{{$item->name}} - {{$item->amount}}</small> </option>  
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <small>Fecha Inicio</small>
                                        <input type="date" name="dateStart" class="form-control text" required>
                                    </div>   
                                    <div class="form-group col-md-3">
                                        <small>Fecha Finalizacion</small>
                                        <input type="date" name="dateFinish" class="form-control text" required>
                                    </div> 
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-5">
                                        <small>Turnos</small>
                                        <select name="shift_id" id="shift_id" class="form-control select2" required>
                                            <option value="" disabled selected>-- Seleccione un turno --</option>
                                            @foreach ($shifts as $item)
                                                <option value="{{$item->id}}"><small>{{$item->name}}</small> </option>  
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <small>Observación</small>
                                        <textarea name="observation" id="observation" class="form-control text" cols="30" rows="5"></textarea>
                                    </div>                                    
                                </div>
                                <div class="row">
                                    <div class="col-md-12 text-right">
                                        <button type="button" data-toggle="modal" data-target="#confirm-modal" class="btn btn-primary btn-submit">Guardar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal fade" tabindex="-1"  id="confirm-modal" role="dialog" data-backdrop="static" data-keyboard="false">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title"><i class="voyager-thumbs-up"></i> Confirmación</h4>
                            </div>
                            {{-- <div class="modal-body">
                                <p>Desea guardar el contrato?</p>
                            </div> --}}
                            <div class="modal-body">
                                <p>Está a punto de guardar un contrato con los siguientes datos:</p>
                                <ul>
                                    <li>Beneficiario: <span id="summary-beneficiary"></span></li>
                                    <li>Cargo: <span id="summary-employeJob"></span></li>
                                    <li>Fecha de Inicio: <span id="summary-dateStart"></span></li>
                                    <li>Fecha de Finalización: <span id="summary-dateFinish"></span></li>
                                    <li>Turno: <span id="summary-shift"></span></li>
                                </ul>
                                <p>¿Desea proceder?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default btn-cancel" data-dismiss="modal" id="btn-cancel">Cancelar</button>
                                <button type="submit" class="btn btn-primary btn-submit" id="btn-submit">Aceptar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    @stop

    @section('css')
        <style>

        </style>
    @endsection

    @section('javascript')
        <script>

            
            $(document).ready(function(){
                
                document.getElementById('btn-submit').addEventListener('click', function() {
                    $('.btn-cancel').attr('disabled', true);
                    $('.close').attr('disabled', true);
                    this.textContent = 'Guardando...';
                    this.disabled = true;
                    this.closest('form').submit();
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
                        url: "{{ url('admin/loans/people/ajax') }}",        
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
                        // productSelected = opt;
                        // alert(opt)
                        console.log(opt);

                        
                        return opt.first_name?opt.first_name+' '+opt.last_name1+' '+opt.last_name2:'<i class="fa fa-search"></i> Buscar... ';
                    }
                }).change(function(){
                
                });

                
            })
            document.querySelector('.btn-submit').addEventListener('click', () => {
                document.getElementById('summary-beneficiary').textContent = document.querySelector('#select_people_id').options[document.querySelector('#select_people_id').selectedIndex].text;
                document.getElementById('summary-employeJob').textContent = document.querySelector('#employeJob').options[document.querySelector('#employeJob').selectedIndex].text;
                document.getElementById('summary-dateStart').textContent = document.querySelector('[name="dateStart"]').value;
                document.getElementById('summary-dateFinish').textContent = document.querySelector('[name="dateFinish"]').value;
                document.getElementById('summary-shift').textContent = document.querySelector('#shift_id').options[document.querySelector('#shift_id').selectedIndex].text;
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
        </script>
    @stop
@endif