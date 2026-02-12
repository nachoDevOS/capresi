@extends('voyager::master')

@section('page_title', 'Añadir Venta')

@section('page_header')
    <h1 class="page-title">
        <i class="fa-solid fa-shop"></i> Añadir Venta
    </h1>
@stop

@section('content')
    <div class="page-content edit-add container-fluid">
        <form id="form-sale" action="{{ route('sales.store') }}" method="post">
            @csrf
            {{-- <input type="hidden" name="cashier_id" value="{{ $cashier ? $cashier->id : null }}"> --}}
            <div class="row">
                @if (setting('ventas.cashier_required') && !$cashier)
                    <div class="col-md-12" style="margin-bottom: 5px">
                        <div class="panel panel-bordered" style="border-left: 5px solid #CB4335">
                            <div class="panel-body" style="padding: 10px">
                                <div class="col-md-12">
                                    <h5 class="text-danger">Advertencia</h5>
                                    <h4>Debe abrir caja antes de registrar ventas. &nbsp; <a href="{{ route('cashiers.create') }}?redirect=admin/sales/create" class="btn btn-success">Abrir ahora <i class="voyager-plus"></i></a></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="col-md-8">
                    <div class="panel panel-bordered">
                        <div class="panel-body" style="padding: 10px 0px">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="product_id">Buscar producto</label>
                                    <select class="form-control" id="select-product_id"></select>
                                </div>
                            </div>
                            <div class="col-md-12" style="height: 300px; max-height: 300px; overflow-y: auto">
                                <div class="table-responsive">
                                    <table id="dataStyle" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th style="width: 30px">N&deg;</th>
                                                <th>Detalles</th>
                                                <th style="min-width: 100px">Precio</th>
                                                <th style="min-width: 100px">Cantidad</th>
                                                <th style="min-width: 100px" class="text-right">Subtotal</th>
                                                <th style="width: 50px"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="table-body">
                                            <tr id="tr-empty">
                                                <td colspan="6" style="height: 240px">
                                                    <h4 class="text-center text-muted" style="margin-top: 50px">
                                                        <i class="glyphicon glyphicon-shopping-cart" style="font-size: 50px"></i> <br><br>
                                                        Lista de venta vacía
                                                    </h4>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="form-group col-md-12">
                                <textarea name="observations" class="form-control" rows="2" placeholder="Observaciones"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="panel panel-bordered">
                        <div class="panel-body" style="padding: 10px 0px">
                            <div class="form-group col-md-12">
                                <label for="person_id">Cliente</label>
                                <div class="input-group">
                                    <select name="person_id" id="select-person_id" class="form-control"></select>
                                    <span class="input-group-btn">
                                        <button class="btn btn-primary" title="Nuevo cliente" data-target="#modal-create-customer" data-toggle="modal" style="margin: 0px" type="button">
                                            <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                                        </button>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group col-md-12">
                                <label for="date">NIT/CI</label>
                                <input type="text" name="dni" id="input-dni" disabled value="" class="form-control" placeholder="NIT/CI">
                            </div>
                            <div class="form-group col-md-12">
                                <label for="date">Tipo de Venta</label>
                                <select class="form-control select2" name="typeSale" id="typeSale" onchange="funtion_typeSale()">
                                    <option value="" disabled selected>--Selecione una opción--</option>
                                    <option value="Contado">Venta al Contado</option>
                                    <option value="Credito">Venta al Credito</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="date">Monto recibido</label>
                                <input type="number" name="amountReceived" id="input-amount" style="text-align: right" min="0" value="0" step="0.01" class="form-control" placeholder="Monto recibo Bs." required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="date">Descuento</label>
                                <input type="number" name="discount" id="input-discount" style="text-align: right" min="0" value="0" step="0.01" class="form-control" placeholder="Descuento Bs." required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="date">Fecha de venta</label>
                                <input type="datetime" name="dateSale" value="{{ date('Y-m-d H:m:s') }}" class="form-control" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="next_payment">Próximo pago</label>
                                <input type="date" name="next_payment" id="next_payment" min="{{ date('Y-m-d') }}" required class="form-control">
                            </div>
                            <div class="form-group col-md-6">
                            </div>
                            <div class="form-group col-md-6">
                                <h2 class="text-right"><small>Total: Bs.</small> <b id="label-total">0.00</b></h2>
                                <input type="hidden" id="amountTotalSale" name="amountTotalSale" value="0">
                            </div>
                            <div class="form-group col-md-12 text-center">
                                <button type="button" id="btn-submit" class="btn btn-primary btn-block" data-toggle="modal" data-target="#modal-confirm">Vender <i class="voyager-basket"></i></button>
                               
                                <a href="{{ route('sales.index') }}" >Volver a la lista</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        
                <div class="modal fade" tabindex="-1" id="modal-confirm" role="dialog">
                    <div class="modal-dialog modal-primary">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title"><i class="voyager-check"></i> Confirmar venta</h4>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="payment_type">Método de pago</label>
                                    <select name="payment_type" id="select-payment_type" class="form-control" required>
                                        <option value="" disabled selected>Seleccionar método de pago</option>
                                        <option value="Efectivo">Efectivo</option>
                                        <option value="Qr">Qr/Transferencia</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary" id="btn-confirm">Confirmar</button>
                            </div>
                        </div>
                    </div>
                </div>
          
        </form>
    </div>

    {{-- Popup para imprimir el recibo --}}
    <div id="popup-button">
        <div class="col-md-12" style="padding-top: 5px">
            <h4 class="text-muted">Desea imprimir el recibo?</h4>
        </div>
        <div class="col-md-12 text-right">
            <button onclick="javascript:$('#popup-button').fadeOut('fast')" class="btn btn-default">Cerrar</button>
            <a id="btn-print" href="#" target="_blank" title="Imprimir" class="btn btn-danger">Imprimir <i class="glyphicon glyphicon-print"></i></a>
        </div>
    </div>

    {{-- Modal crear cliente --}}
    <form action="{{ url('admin/person/store') }}" id="form-create-customer" method="POST">
        <div class="modal fade" tabindex="-1" id="modal-create-customer" role="dialog">
            <div class="modal-dialog modal-primary">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" style="color: #ffffff !important"><i class="voyager-plus" ></i> Registrar Persona</h4>
                    </div>
                    <div class="modal-body">
                        @csrf
                        <div class="form-group">
                            <label for="full_name">Nombres</label>
                            <input type="text" name="first_name" class="form-control" placeholder="Daniel" required>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="full_name">Apellido Paterno</label>
                                <input type="text" name="last_name1" class="form-control" placeholder="Perez">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="full_name">Apellido Materno</label>
                                <input type="text" name="last_name2" class="form-control" placeholder="Ortiz">
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="full_name">NIT/CI</label>
                                <input type="text" name="ci" class="form-control" placeholder="123456789" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="full_name">Celular</label>
                                <input type="text" name="cell_phone" class="form-control" placeholder="76558214">
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="full_name">Género</label>
                                <select name="gender" id="gender" class="form-control select2" required>
                                    <option value="" disabled selected></option>
                                    <option value="masculino">Masculino</option>
                                    <option value="femenino">Femenino</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="full_name">F. Nacimiento</label>
                                <input type="date" name="birth_date" class="form-control">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address">Calle</label>
                            <textarea name="street" class="form-control" rows="3" placeholder="C/ 18 de nov."></textarea>
                        </div>
                        <div class="form-group">
                            <label for="address">Nro de Casa</label>
                            <textarea name="home" class="form-control" rows="3" placeholder="123"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="address">Zona</label>
                            <textarea name="zone" class="form-control" rows="3" placeholder="zona central"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <input type="submit" class="btn btn-primary btn-save-customer" value="Guardar">
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop

@section('css')
    <style>
        .form-group{
            margin-bottom: 10px !important;
        }
        .label-description{
            cursor: pointer;
        }
        #popup-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 400px;
            height: 100px;
            background-color: white;
            box-shadow: 5px 5px 15px grey;
            z-index: 1000;

            /* Mostrar/ocultar popup */
            @if(session('sale_id'))
            animation: show-animation 1s;
            @else
            right: -500px;
            @endif
        }

        @keyframes show-animation {
            0% {
                right: -500px;
            }
            100% {
                right: 20px;
            }
        }
    </style>
@endsection

@section('javascript')
    <script src="{{ asset('vendor/tippy/popper.min.js') }}"></script>
    <script src="{{ asset('vendor/tippy/tippy-bundle.umd.min.js') }}"></script>
    <script>
        var productSelected, customerSelected;
 
        $(document).ready(function(){
            $('#select-product_id').select2({
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
                    url: "{{ url('admin/inventories/item/ajax') }}",      

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
                templateResult: formatResultProducts,
                templateSelection: (opt) => {
                    productSelected = opt;
                    return opt.code;
                }
            }).change(function(){
                if($('#select-product_id option:selected').val()){
                    let product = productSelected;
                    if($('.table').find(`#tr-item-${product.id}`).val() === undefined){
                        $('#table-body').append(`
                            <tr class="tr-item" id="tr-item-${product.id}">
                                <td class="td-item"></td>
                                <td>
                                    <b class="label-description" id="description-${product.id}">${product.code}<br>
                                    <small>${parseFloat(product.quantity) === parseInt(product.quantity) ? parseInt(product.quantity) : product.quantity} ${product.item.unit?product.item.unit:''} ${product.item.name} a Bs. ${product.amountTotal} | $ ${product.dollarTotal}</small>
                                    <input type="hidden" name="product_id[]" value="${product.id}" />
                                </td>
                                <td width="150px">
                                    <input type="number" style="text-align: right" name="price[]" class="form-control" id="input-price-${product.id}" onkeyup="getSubtotal(${product.id})" onchange="getSubtotal(${product.id})" value="${product.amountTotal}" min="1" step="0.01" required/>
                                </td>
                                <td width="100px">
                                    <input type="number" style="text-align: right" class="form-control" id="show-quantity-${product.id}" value="1" min="1" step="1" disabled required/>
                                    <input type="hidden" name="quantity[]" class="form-control" id="input-quantity-${product.id}" onkeyup="getSubtotal(${product.id})" onchange="getSubtotal(${product.id})" value="1" min="1" step="1" required/>
                                </td>
                                <td width="120px" class="text-right">
                                    <h4 class="label-subtotal" id="label-subtotal-${product.id}">${product.price}</h4>
                                </td>
                                <td width="50px" class="text-right"><button type="button" onclick="removeTr(${product.id})" class="btn btn-link"><i class="voyager-trash text-danger"></i></button></td>
                            </tr>
                        `);

                        setNumber();
                        getSubtotal(product.id);
                        $(`#select-price-${product.id}`).select2({tags: true});
                    }else{
                        toastr.info('EL producto ya está agregado', 'Información')
                    }

                    $('#select-product_id').val('').trigger('change');
                }
            });

            $('#select-person_id').select2({
                // tags: true,
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
                    url: "{{ url('admin/people/search/ajax') }}",        
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
                templateResult: formatResultPeople,
                templateSelection: (opt) => {
                    customerSelected = opt;
                    // return opt.first_name;
                    return opt.first_name?opt.first_name+' '+opt.last_name1+' '+opt.last_name2:'<i class="fa fa-search"></i> Buscar... ';
                }
            }).change(function(){
                if(customerSelected){
                    $('#input-dni').val(customerSelected.ci ? customerSelected.ci : '');
                }
            });

            $('#form-create-customer').submit(function(e){
                e.preventDefault();
                $('.btn-save-customer').attr('disabled', true);
                $('.btn-save-customer').val('Guardando...');
                // alert(1)
                $.post($(this).attr('action'), $(this).serialize(), function(data){                    
                    if(data.people.id){
                        toastr.success('Usuario creado', 'Éxito');
                        $(this).trigger('reset');
                    }else{
                        toastr.error(data.error, 'Error');
                    }
                })
                .always(function(){
                    $('.btn-save-customer').attr('disabled', false);
                    $('.btn-save-customer').text('Guardar');
                    $('#modal-create-customer').modal('hide');
                });
            });

       

            $('#form-sale').submit(function(e){
                $('#btn-submit').attr('disabled', 'disabled');
                $('#btn-proforma').attr('disabled', 'disabled');
                $('#btn-confirm').attr('disabled', 'disabled');
            });

            $('#input-discount').keyup(function(){
                getTotal();
            });

            $('#input-discount').change(function(){
                getTotal();
            });

           

            // $('#btn-add-service').click(function(){
            //     let code = new Date().getMilliseconds();
            //     let step_value = "{{ setting('otros.decimal_quantity') ?? '0.1' }}";
            //     $('#table-body').append(`
            //         <tr class="tr-item" id="tr-item-${code}">
            //             <td class="td-item"></td>
            //             <td><textarea name="product_id[]" class="form-control" rows="3" required></textarea></td>
            //             <td width="150px"><input type="number" name="price[]" id="select-price-${code}" onchange="getSubtotal(${code})" onkeyup="getSubtotal(${code})" min="0.1" step="${step_value}" class="form-control" required /></td>
            //             <td width="100px"><input type="number" name="quantity[]" id="input-quantity-${code}" class="form-control" value="1" readonly/></td>
            //             <td width="120px" class="text-right"><h4 class="label-subtotal" id="label-subtotal-${code}">0</h4></td>
            //             <td width="50px" class="text-right"><button type="button" onclick="removeTr(${code})" class="btn btn-link"><i class="voyager-trash text-danger"></i></button></td>
            //         </tr>
            //     `);
            //     setNumber();
            // });
            // $('#typeSale').change(function() {
            //     let typeSale = $(this).val();
            //     alert(typeSale);
            // });
        });

        function getSubtotal(id){
            let price = $(`#input-price-${id}`).val() ? parseFloat($(`#input-price-${id}`).val()) : 0;
            let quantity = $(`#input-quantity-${id}`).val() ? parseFloat($(`#input-quantity-${id}`).val()) : 0;
            $(`#label-subtotal-${id}`).text((price * quantity).toFixed(2));
            getTotal();
        }


        function funtion_typeSale() {
            let typeSale = $('#typeSale').val();
            $('#next_payment').attr('disabled', typeSale=='Credito'?false:true);
            $('#input-amount').attr('readonly', typeSale=='Credito'?false:true);

            getTotal();

        }

        function getTotal(){
            let total = 0;
            let discount = $('#input-discount').val() ? parseFloat($('#input-discount').val()) : 0;
            $(".label-subtotal").each(function(index) {
                total += parseFloat($(this).text());
            });
            $('#input-discount').attr('max', total.toFixed(2));

            $('#label-total').text((total - discount).toFixed(2));
            $('#amountTotalSale').val((total - discount).toFixed(2));

            $('#input-amount').val((total - discount).toFixed(2));
            $('#input-amount').attr('max', (total - discount).toFixed(2));
        }

        function setNumber(){

            var length = 0;
            $(".td-item").each(function(index) {
                $(this).text(index +1);
                length++;
            });

            if(length > 0){
                $('#tr-empty').css('display', 'none');
            }else{
                $('#tr-empty').fadeIn('fast');
            }
        }

        function removeTr(id){
            $(`#tr-item-${id}`).remove();
            $('#select-product_id').val("").trigger("change");
            setNumber();
            getTotal();
        }

        function formatResultProducts(option){
            // Si está cargando mostrar texto de carga
            if (option.loading) {
                return '<span class="text-center"><i class="fas fa-spinner fa-spin"></i> Buscando...</span>';
            }
            let image = "{{ asset('images/default.jpg') }}";
            if(option.image){
                image = "{{ asset('storage') }}/"+option.image.replace('.', '-cropped.');
            }

            let features_list = '';
            // if (option.features && Array.isArray(option.features)) {
                option.features.forEach(feature => {
                    if (feature.value) {
                        features_list += `<span><b>${feature.title}</b>: ${feature.value}</span><br>`;
                    }
                });
            // }
            // alert(option.features.length)
            // console(option)
            // console.log("Total de características:", option);


            // Mostrar las opciones encontradas
            return $(`  <div style="display: flex">
                            <div style="margin: 0px 10px">
                                <img src="${image}" width="60px" />
                            </div>
                            <div>
                                <b style="font-size: 16px">${option.code} | ${parseFloat(option.quantity) === parseInt(option.quantity) ? parseInt(option.quantity) : option.quantity} ${option.item.unit?option.item.unit:''} ${option.item.name} a <small>Bs. ${option.amountTotal} | $ ${option.dollarTotal}</small></b><br>
                                 ${features_list} 
                            </div>
                        </div>`);

                        
        }

        function formatResultPeople(option){
            // Si está cargando mostrar texto de carga
            if (option.loading) {
                return '<span class="text-center"><i class="fas fa-spinner fa-spin"></i> Buscando...</span>';
            }

            let image = "{{ asset('images/icono-anonimato.png') }}";
                if(option.image){
                    image = "{{ asset('storage') }}/"+option.image.replace('.', '-cropped.');
                }
            // Mostrar las opciones encontradas
            return $(`  
                        <div style="display: flex">
                            <div style="margin: 0px 10px">
                                <img src="${image}" width="50px" />
                            </div>
                            <div>
                                <small>CI: </small><b style="font-size: 15px; color: black">${option.ci?option.ci:'No definido'}</b><br>
                                <b style="font-size: 15px; color: black">${option.first_name} ${option.last_name1} ${option.last_name2} </b>
                            </div>
                        </div>
                        `);
        }
    </script>
@stop
