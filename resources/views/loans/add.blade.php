@extends('voyager::master')

@section('page_title', 'Crear prestamos')

@if (auth()->user()->hasPermission('add_loans'))

    @section('page_header')
        <h1 id="titleHead" class="page-title">
            <i class="fa-solid fa-hand-holding-dollar"></i> Crear Prestamos
        </h1>
        <a href="{{ route('loans.index') }}" class="btn btn-warning">
            <i class="fa-solid fa-rotate-left"></i> <span>Volver</span>
        </a>
    @stop

    @section('content')
        <div class="page-content edit-add container-fluid">    
            <form id="agent" action="{{ route('loans.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-bordered">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h4 class="text-primary"><i class="fa-solid fa-file-contract"></i> Datos Generales</h4>
                                        <hr style="margin-top: 5px; margin-bottom: 15px">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label class="control-label">Fecha</label>
                                        <input type="date" name="date" value="{{date('Y-m-d')}}" class="form-control" required>
                                    </div>   
                                    <div class="form-group col-md-3">
                                        <label class="control-label">Tipo de Préstamo</label>
                                        <select name="optradio" id="optradio" required class="form-control select2"  onchange="funtion_type()">
                                            <option value="diario" selected>Diario</option>
                                            <option value="diarioespecial">Diario Especial</option>
                                        </select>
                                    </div>   
                                    <div class="form-group col-md-3">
                                        <label class="control-label">Asignar Ruta</label>
                                        <select name="route_id" id="route_id" class="form-control select2" required>
                                            <option value="" disabled selected>-- Selecciona una ruta --</option>
                                            @foreach ($routes as $item)
                                                <option value="{{$item->id}}">{{$item->name}}</option>  
                                            @endforeach
                                        </select>
                                    </div>       
                                    <div class="form-group col-md-3">
                                        <label class="control-label">Autorizado por</label>
                                        <select name="manager_id" class="form-control select2" required>
                                            <option value="" selected disabled>-- Seleccione --</option>
                                            @foreach (App\Models\Manager::where('status', 1)->get() as $item)
                                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>                           
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        {{-- <label class="control-label">Beneficiario del Préstamo</label>
                                        <select name="people_id" class="form-control" id="select_people_id" required></select> --}}

                                        <label for="select_people_id">Propietario</label>
                                        <div class="input-group">
                                            <select name="people_id" id="select_people_id" required class="form-control"></select>
                                            <span class="input-group-btn">
                                                <button class="btn btn-primary" title="Nueva persona" data-target="#modal-create-person" data-toggle="modal" style="margin: 0px" type="button">
                                                    <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                                                </button>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="control-label">Garante</label>
                                        <select name="guarantor_id" class="form-control" id="select_guarantor_id"></select>
                                    </div>
                                </div>
                                {{-- <input type="hidden" name="type" id="text_type"> --}}
                                <div class="row" style="margin-top: 15px">
                                    <div class="col-md-12">
                                        <h4 class="text-primary"><i class="fa-solid fa-calculator"></i> Cálculos del Préstamo</h4>
                                        <hr style="margin-top: 5px; margin-bottom: 15px">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label class="control-label">Monto</label>
                                        <div class="input-group">
                                            <span class="input-group-addon">Bs.</span>
                                            <input type="number" name="amountLoan" id="amountLoan" style="text-align: right" value="0" min="1" step=".01" onkeypress="return filterFloat(event,this);" onchange="subTotal()" onkeyup="subTotal()" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label class="control-label">Días</label>
                                        <div class="input-group">
                                            <input type="number" min="1" id="day1" value="24" style="text-align: right" disabled onkeypress="return filterFloat(event,this);" onchange="diasPagar()" onkeyup="diasPagar()" class="form-control" required>
                                            <span class="input-group-addon">Días</span>
                                        </div>
                                        <input type="hidden" min="1" name="day" id="day" onkeypress="return filterFloat(event,this);" value="24" class="form-control" required>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label class="control-label">Interés</label>
                                        <div class="input-group">
                                            <input type="number" id="porcentage1" min="0" step="any" style="text-align: right" disabled value="20" onkeypress="return filterFloat(event,this);" onchange="porcentagePagar()" onkeyup="porcentagePagar()" onchange="subTotal()" onkeyup="subTotal()" class="form-control" required>
                                            <span class="input-group-addon">%</span>
                                        </div>
                                        <input type="hidden" name="porcentage" id="porcentage" onkeypress="return filterFloat(event,this);" value="20" class="form-control" required>
                                    </div>    
                                    <div class="form-group col-md-2">
                                        <label class="control-label">Ganancia</label>
                                        <div class="input-group">
                                            <span class="input-group-addon">Bs.</span>
                                            <input type="number" id="amountPorcentage1" min="0" step="any" style="text-align: right" disabled value="0" onkeypress="return filterFloat(event,this);" onchange="porcentageAmount()" onkeyup="porcentageAmount()" onchange="subTotal()" onkeyup="subTotal()" class="form-control" required>
                                        </div>
                                        <input type="hidden" name="amountPorcentage" id="amountPorcentage" onkeypress="return filterFloat(event,this);" value="0" class="form-control" required>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label class="control-label">Cuota Diaria</label>
                                        <div class="input-group">
                                            <span class="input-group-addon">Bs.</span>
                                            <input type="text" id="amountDay1" style="text-align: right; background-color: #f0f0f0;" disabled value="0" class="form-control">
                                        </div>
                                        <input type="hidden" name="amountDay" id="amountDay"onkeypress="return filterFloat(event,this);" value="0" class="form-control">
                                        <b class="text-danger" id="label-amount" style="display:none">Incorrecto..</b>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label class="control-label">Total a Pagar</label>
                                        <div class="input-group">
                                            <span class="input-group-addon">Bs.</span>
                                            <input type="number" id="amountTotal1" style="text-align: right; background-color: #e8f5e9; font-weight: bold;" disabled value="0" class="form-control">
                                        </div>
                                        <input type="hidden" name="amountTotal" id="amountTotal" value="0" class="form-control">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label class="control-label">Observaciones</label>
                                        <textarea name="observation" id="observation" class="form-control" cols="30" rows="3" placeholder="Ingrese alguna observación si es necesario..."></textarea>
                                    </div>
                                    
                                </div>
                                <div class="row">
                                    <div class="col-md-12 text-right">
                                        <button type="submit" id="btn_submit" class="btn btn-primary">Guardar</button>
                                    </div>
                                </div>
                                
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
        <script src="{{ asset('js/include/person-select.js') }}"></script>
        <script src="{{ asset('js/include/person-register.js') }}"></script>
        <script src="{{ asset('js/btn-submit.js') }}"></script>
        <script>

            $(document).ready(function(){
                var productSelected;
                
                $('#agent').submit(function(e){
                    $('#btn_submit').text('Guardando...');
                    $('#btn_submit').attr('disabled', true);

                });


                $('#select_guarantor_id').select2({
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
                        productSelected = opt;
                        // alert(opt)
                        
                        return opt.first_name?opt.first_name+' '+opt.last_name1+' '+opt.last_name2:'<i class="fa fa-search"></i> Buscar... ';
                    }
                });
            })

            function funtion_type() {
                let optradio = $('#optradio').val();
                // $(`#text_type`).val(optradio);
                list(optradio);
            }

            function list(optradio){        
              
                if(optradio=='diario')
                {
                    $('#label-amount').css('display', 'none');
                    $('#btn_submit').attr('disabled',false);
                    $('#amountLoan').val(0);

                    $('#day1').val(24);
                    $('#day').val(24);

                    $('#porcentage1').val(20);
                    $('#porcentage').val(20);

                    $('#amountPorcentage1').val(0);
                    $('#amountPorcentage').val(0);

                    $('#amountDay1').val(0);
                    $('#amountDay').val(0);

                    $('#amountTotal1').val(0);
                    $('#amountTotal').val(0);
                    
                    $('#day1').attr('disabled',true);
                    $('#porcentage1').attr('disabled',true);
                    $('#amountPorcentage1').attr('disabled',true);
                }
                if(optradio=='diarioespecial')
                {
                    $('#label-amount').css('display', 'none');
                    $('#btn_submit').attr('disabled',false);
                    $('#amountLoan').val(0);

                 
                    $('#day1').val(0); //0
                    $('#day').val(0);



                    $('#amountDay1').val(0);
                    $('#amountDay').val(0);

                    $('#amountTotal1').val(0);
                    $('#amountTotal').val(0);

                    $('#porcentage1').val(0);//0
                    $('#porcentage').val(0);

                    $('#amountPorcentage1').val(0);
                    $('#amountPorcentage').val(0);

                    $('#day1').attr('disabled',false);         
                    $('#porcentage1').attr('disabled',false);     
                    $('#amountPorcentage1').attr('disabled',false);

                    
                }
            }

            function diasPagar(){
                let day = $(`#day1`).val() ? parseFloat($(`#day1`).val()) : 0;
                $('#day').val(day);

                subTotal()
            }

            function porcentagePagar(){
                let porcentage = $(`#porcentage1`).val() ? parseFloat($(`#porcentage1`).val()) : 0;
                $('#porcentage').val(porcentage);

                let amountLoan = $(`#amountLoan`).val() ? parseFloat($(`#amountLoan`).val()) : 0;

                porcentage = porcentage/100;
                let amountPorcentage = amountLoan*porcentage;
                $(`#amountPorcentage1`).val(amountPorcentage.toFixed(2));
                $(`#amountPorcentage`).val(amountPorcentage.toFixed(2));

                subTotal()
            }

            function porcentageAmount(){
                let amountPorcentage = $(`#amountPorcentage1`).val() ? parseFloat($(`#amountPorcentage1`).val()) : 0;
                $('#amountPorcentage').val(amountPorcentage.toFixed(2));

                let amountLoan = $(`#amountLoan`).val() ? parseFloat($(`#amountLoan`).val()) : 0;

                amountPorcentage = amountPorcentage/amountLoan;
                amountPorcentage = amountPorcentage*100;
                
                $(`#porcentage1`).val(amountPorcentage.toFixed(2));
                $(`#porcentage`).val(amountPorcentage.toFixed(2));

                subTotal();

            }

            function subTotal(){
                let type = $('#optradio').val();

                if(type=='diario')
                {
                    // $(`#text_type`).val('diario');

                    let amountLoan = $(`#amountLoan`).val() ? parseFloat($(`#amountLoan`).val()) : 0;
                    let porcentage = $(`#porcentage`).val() ? parseFloat($(`#porcentage`).val()) : 0;

                    let day = $(`#day`).val() ? parseFloat($(`#day`).val()) : 0;

                    porcentage = porcentage/100;
                    let amountPorcentage = amountLoan*porcentage;
                    let amountTotal = amountLoan+amountPorcentage;
                    let amountDay = amountTotal / day;

                

                    $(`#amountPorcentage1`).val(amountPorcentage);
                    $(`#amountTotal1`).val(amountTotal);         

                    $(`#amountPorcentage`).val(amountPorcentage);
                    $(`#amountTotal`).val(amountTotal);  

                    $(`#amountDay1`).val(amountDay);
                    $(`#amountDay`).val(amountDay);  

                    if (amountDay % 1 == 0) {
                        $('#label-amount').css('display', 'none');
                        $('#btn_submit').attr('disabled',false);

                    } else {
                        $('#label-amount').css('display', 'block');
                        $('#btn_submit').attr('disabled',true);
                    }
                }
                if(type=='diarioespecial')
                {
                    // $(`#text_type`).val('diarioespecial');

                    let amountLoan = $(`#amountLoan`).val() ? parseFloat($(`#amountLoan`).val()) : 0;
                    let day = $(`#day1`).val() ? parseFloat($(`#day1`).val()) : 0;


                    let porcentage = $(`#porcentage1`).val() ? parseFloat($(`#porcentage1`).val()) : 0;
                    $('#porcentage').val(porcentage.toFixed(2));

                    porcentage = porcentage/100;
                    porcentage = amountLoan*porcentage;

                    $(`#amountPorcentage1`).val(porcentage.toFixed(2));
                    $(`#amountPorcentage`).val(porcentage.toFixed(2));


                    let amountPorcentage = $(`#amountPorcentage1`).val() ? parseFloat($(`#amountPorcentage1`).val()) : 0;


                    let amountTotal = (amountLoan+amountPorcentage).toFixed(2);

                    $(`#amountTotal1`).val(amountTotal);         
                    $(`#amountTotal`).val(amountTotal);  

                    let amountDay = amountTotal / day;

                    amountDay = Math.trunc(amountDay);
                    
                    let amountDayTotal =amountDay * day;

                    let aux = amountTotal-amountDayTotal;
                    aux = amountDay+aux;



                    $(`#amountDay1`).val((aux?aux.toFixed(2):0)+' - '+(amountDay!='Infinity'?amountDay.toFixed(2):0));
                    $(`#amountDay`).val(amountDay);  
                    
                }
            }


            function filterFloat(evt,input){
                // Backspace = 8, Enter = 13, ‘0′ = 48, ‘9′ = 57, ‘.’ = 46, ‘-’ = 43
                var key = window.Event ? evt.which : evt.keyCode;    
                var chark = String.fromCharCode(key);
                var tempValue = input.value+chark;
                if(key >= 48 && key <= 57){
                    if(filter(tempValue)=== false){
                        return false;
                    }else{       
                        return true;
                    }
                }
            }

            function filter(__val__){
                var preg = /^([0-9]+\.?[0-9]{0,2})$/; 
                if(preg.test(__val__) === true){
                    return true;
                }else{
                return false;
                }
                
            }

            
        </script>
    @stop
@endif