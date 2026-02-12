@extends('voyager::master')

@section('page_title', 'Registrar empeño')

@section('page_header')
    <h1 id="titleHead" class="page-title">
        <i class="fa-solid fa-handshake"></i> Registrar empeño
    </h1>
@stop

@section('content')
    <div class="page-content edit-add container-fluid">    
        <form class="form-submit" action="{{ route('pawn.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="cashier_id" value="">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-bordered">
                        <div class="panel-body">
                            {{-- Verificar que se haya abierto caja --}}
                            @if (false)  
                                <div class="alert alert-warning">
                                    <strong>Advertencia:</strong>
                                    <p>No puedes registrar debido a que no tiene una caja asignada.</p>
                                </div>
                            @else     
                                @if (false)
                                    <div class="alert alert-warning">
                                        <strong>Advertencia:</strong>
                                        <p>No puedes registrar debido a que no tiene una caja activa.</p>
                                    </div>
                                @endif
                            @endif

                            <h5>Datos Generales</h5>
                            
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <small for="estado">ESTADO</small>
                                    <select name="endeavor" id="endeavor" class="form-control" required>
                                        <option selected value="antiguo">Antiguo</option>
                                        <option value="nuevo">Nuevo</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <small for="people_id">Beneficiario del Prestamo</small>
                                    <select name="people_id" class="form-control" id="select-people_id" required></select>
                                </div>
                                <div class="form-group col-md-3">
                                    <small for="codeManual">Codigo Manual</small>
                                    <input type="text" name="codeManual" id="codeManual" class="form-control" required>
                                </div>
                                <div class="form-group col-md-3">
                                    <small for="interest_rate">Tasa de interes mensual</small>
                                    <div class="input-group">
                                        <input type="number" name="interest_rate" id="input-interest_rate" class="form-control" value="10" step="0.01" min="0.01"required>
                                        <span class="input-group-addon">%</span>
                                    </div>
                                </div>
                                <div class="form-group col-md-3">
                                    <small for="date">Fecha del prestamo</small>
                                    <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="form-group col-md-3">
                                    <small for="date_limit">Fecha del límite de devolución</small>
                                    <div class="input-group">
                                        <input type="number" name="date_limit_months" id="input-date_limit_months" class="form-control" step="1" min="1" value="3" required>
                                        <span class="input-group-addon">mes(es)</span>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <h5>Detalle de artículos</h5>
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <small for="item_id">Tipo de artículo</small>
                                    <select name="item_id" class="form-control" id="select-item_id">
                                        <option value="" selected disabled>Seleccione tipo de artículo</option>
                                        @foreach (App\Models\ItemType::with(['category.features'])->where('status', 1)->get() as $item)
                                            <option value="{{ $item->id }}" data-item='@json($item)'>{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-12" style="margin: 0px">
                                    <table class="table" id="table-details">
                                        <thead>
                                            <tr>
                                                <th>N&deg;</th>
                                                <th>Tipo</th>
                                                <th class="label-unit"></th>
                                                <th class="label-discount" style="max-width: 120px !important"></th>
                                                <th>Precio</th>
                                                <th>Características</th>
                                                <th>Imágen</th>
                                                <th class="text-right">Subtotal</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-details">
                                            <tr class="tr-empty">
                                                <td colspan="9">No hay artículos seleccionados</td>
                                            </tr>
                                            <tr>
                                                <td class="text-right" colspan="7">TOTAL A PRESTAR Bs.</td>
                                                <td class="text-right" id="td-total">
                                                    <h4>0.00</h4>
                                                </td>
                                                <input type="hidden" id="amountTotals" name="amountTotals">

                                                <td class="text-right" id="td-dolartotal">
                                                    <h4>$ 0.00</h4>
                                                    
                                                </td>
                                                <input type="hidden" id="dollarTotals" name="dollarTotals">
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td class="text-right" colspan="7">INTERES/GANANCIA Bs.</td>
                                                <td class="text-right" id="td-revenue"><h4>0.00</h4></td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td class="text-right" colspan="7">TOTAL A DEVOLVER Bs.</td>
                                                <td class="text-right" id="td-return"><h4>0.00</h4></td>
                                                <td></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="form-group col-md-12">
                                    <label for="observations">Observaciones</label>
                                    <textarea name="observations" class="form-control" rows="3" placeholder=""></textarea>
                                </div>
                                <div class="form-group col-md-12 text-right" id="div-validate" style="display: none">
                                    <div class="checkbox">
                                        <label><input type="checkbox" id="checkbox-validate" name="validate" value="1">Existe un monto que sobrepasa el límite, desea solicitar validación?</label>
                                    </div>
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
            <div class="modal fade" tabindex="-1" id="confirm-modal" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title"><i class="voyager-thumbs-up"></i> Confirmación</h4>
                        </div>
                        <div class="modal-body">
                            <p>Desea guardar el registro de empeño?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary btn-submit">Aceptar</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>       
    </div>
    
    {{-- Create type items modal --}}
    <form action="{{ route('item_types.store') }}" id="form-type-items" class="form-submit" method="POST">
        @csrf
        <div class="modal modal-primary fade" tabindex="-1" id="type-items-modal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="voyager-tag"></i> Registrar tipo de artículo</h4>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="contract_id">
                        <div class="form-group">
                            <label for="item_category_id">Categoría</label>
                            <select name="item_category_id" class="form-control" id="select-item_category_id" required>
                                <option value="">--Seleccionar categoría--</option>
                                @foreach (App\Models\ItemCategory::where('status', 1)->get() as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="name">Nombre</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="unit">Unidad</label>
                            <select name="unit" class="form-control select2">
                                <option value="">Ninguna</option>
                                <option value="kg">kg</option>
                                <option value="g">g</option>
                                <option value="otra">Otra</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="price">Precio sugerido</label>
                            <input type="number" name="price" min="0.1" step="0.01" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="max_price">Precio máximo</label>
                            <input type="number" name="max_price" min="0.1" step="0.01" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="description">Descripción</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-dark btn-submit">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Create type items modal --}}
    <form action="{{ route('people.store') }}" id="form-person" class="form-submit" method="POST">
        @csrf
        <div class="modal modal-primary fade" tabindex="-1" id="person-modal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="voyager-tag"></i> Registrar beneficiario</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="first_name">Nombre(s)</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name1">Apellido paterno</label>
                            <input type="text" name="last_name1" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name2">Apellido materno</label>
                            <input type="text" name="last_name2" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="ci">CI</label>
                            <input type="text" name="ci" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="cell_phone">N&deg; de celular</label>
                            <input type="text" name="cell_phone" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="birth_date">Fecha de nac.</label>
                            <input type="date" name="birth_date" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="street">Dirección</label>
                            <textarea name="street" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-dark btn-submit">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop

@section('css')
    <style>
        .select2{
            width: 100% !important;
        }
        .input-feature{
            width: 120px;
            border: 0px !important
        }
        .label-description{
            cursor: pointer;
        }
        .div-details small{
            color: white !important
        }
        #table-details th {
            font-size: 11px;
            font-weight: bold
        }
    </style>
@stop

@section('javascript')
    <script src="{{ asset('js/main.js') }}"></script>
    <script src="{{ asset('vendor/tippy/popper.min.js') }}"></script>
    <script src="{{ asset('vendor/tippy/tippy-bundle.umd.min.js') }}"></script>
    <script>
        var index = 0;
        var number_features = 0;
        var maxPrices = [];
        var enableValidation = false;
        $(document).ready(function(){
            
            customSelect('#select-people_id', '{{ url("admin/people/search/ajax") }}', formatResultPeople, data => data.first_name+' '+data.last_name1+' '+data.last_name2, null, 'createPerson()');
            
            $('#select-item_category_id').select2({
                tags: true,
                dropdownParent: '#type-items-modal',
                createTag: function (params) {
                    return {
                        id: params.term,
                        text: params.term,
                        newOption: true
                    }
                },
                templateResult: function (data) {
                    var $result = $("<span></span>");
                    $result.text(data.text);
                    if (data.newOption) {
                        $result.append(" <em>(ENTER para agregar)</em>");
                    }
                    return $result;
                }
            });

            $('#select-item_id').select2({
                language: {
                    noResults: function() {
                        return `Resultados no encontrados <button class="btn btn-link" onclick="dismissSelect2()" data-toggle="modal" data-target="#type-items-modal">Crear nuevo</a>`;
                    },
                },
                escapeMarkup: function(markup) {
                    return markup;
                }
            });

            $('#select-item_id').change(function(){
                let type = $('#select-item_id option:selected').data('item');
                if (type) {
                    // Obetener la lista de características de cada tipo de item
                    let features = '';
                    type.category.features.map(item => {
                        features += `
                            <tr id="tr-features-${number_features}">
                                <td style="width:120px !important"><input type="hidden" name="features_${index}[]" value="${item.name}" /><b>${item.name}</b>&nbsp;</td>
                                <td><input type="text" name="features_value_${index}[]"  style="width: 120px !important" /></td>
                                <td><button type="button" class="btn-danger" onclick="removeTrFeature(${number_features})" >x</button></td>
                            </tr>`;
                            number_features++;
                    });
                    
                    $('.tr-empty').css('display', 'none');
                    
                    $('#body-details').append(`
                        <tr class="tr-item-${index}">
                            <td class="td-number" rowspan="2"></td>
                            <td rowspan="2">
                                <span id="label-description-${type.id}" class="label-description">${type.name}</span> <br>
                                <span style="font-size: 12px">${type.category.name}</span>
                                <input type="hidden" name="item_type_id[]" value="${type.id}" />
                            </td>
                            <td width="130px">
                                <div class="input-group">
                                    <input type="number" name="quantity[]" id="input-quantity-${index}" onchange="getSubtotal(${index})" onkeyup="getSubtotal(${index})" class="form-control" value="1" min="0.01" step="0.001" required>
                                    <span class="input-group-addon" style="padding: 6px"><small>${type.unit ? type.unit : 'pza'}</small></span>
                                </div>
                            </td>
                            <td style="${!type.category.quantity_discount ? '' : 'max-width: 120px !important'}">
                                <div class="input-group" style="${!type.category.quantity_discount ? 'display:none' : 'max-width: 120px !important'}">
                                    <input type="number" name="quantity_discount[]" id="input-quantity-discount-${index}" onchange="getSubtotal(${index})" onkeyup="getSubtotal(${index})" class="form-control" value="0" min="0" step="0.001" title="Descuento a la cantidad">
                                    <span class="input-group-addon" style="padding: 6px"><small>${type.unit ? type.unit : 'pza'}</small></span>
                                </div>
                            </td>
                            <td width="130px">
                                <div class="input-group">
                                    <input type="number" name="price[]" id="input-price-${index}" onchange="getSubtotal(${index})" onkeyup="getSubtotal(${index})" class="form-control input-price" value="${type.price % 1 == 0 ? parseInt(type.price) : type.price.toFixed(2)}" step="0.001" min="0.01" max="${type.max_price ? type.max_price : ''}" required>
                                    <span class="input-group-addon" style="padding: 6px"><small>Bs.</small></span>
                                </div>
                            </td>
                            <td style="width: 300px" class="table-features">
                                <table id="table-features-${index}">${features}</table>
                                <a class="btn btn-link" onclick="addFeature(${index})" style="padding-left: 0px"><i class="voyager-plus"></i> agregar</a>
                            </td>
                            <td>
                                <img src="{{ asset('images/default.jpg') }}" height="60px" onclick="triggerClickInput(${index})" id="img-preview-${index}" />
                                <input type="file" id="input-file-${index}" data-id="${index}" onchange="changeImage(${index})" name="image[]" style="display:none" accept="image/png, image/jpeg" />
                            </td>
                            <td width="140px">
                                <div class="input-group">
                                    <input type="number" name="subtotal[]" id="input-subtotal-${index}" onchange="getPrice(${index})" onkeyup="getPrice(${index})" class="form-control input-subtotal" value="${type.price % 1 == 0 ? parseInt(type.price) : type.price.toFixed(2)}" step="0.1" required>
                                    <span class="input-group-addon" style="padding: 6px"><small>Bs.</small></span>
                                </div>
                            </td>
                            <td class="text-right"><button type="button" class="btn btn-link" onclick="removeTr(${index})"><span class="voyager-trash text-danger"></span></button></td>
                        </td>
                        <tr class="tr-item-${index}" style="${type.unit || type.max_price ? 'background-color: #F5F5F5' : 'display:none'}">
                            <td colspan="4">
                                <span id="label-quantity-${index}" style="${type.unit ? '' : 'display:none'}">
                                    <p style="font-size: 16px">Peso bruto: <b><span class="span-quantity">1</span>${type.unit}</b> &nbsp; Peso de piedra: <b><span class="span-quantity-discount">0</span>${type.unit}</b> &nbsp; Peso neto: <b><span class="span-quantity-total">1</span>${type.unit}<b></p>
                                </span>
                            </td>
                            <td colspan="3" class="text-right">
                                <span id="label-price-${index}" style="${type.max_price ? '' : 'display:none'}">
                                    <p style="font-size: 16px">desde <b><span class="label-min-subtotal">${type.price % 1 == 0 ? parseInt(type.price) : type.price.toFixed(2)}</span> Bs.</b> hasta <b><span class="label-max-subtotal">${type.max_price % 1 == 0 ? parseInt(type.max_price) : type.max_price.toFixed(2)}</span> Bs.</b></p>
                                </span>
                            </td>
                        </tr>
                    `);

                    // popover
                    let image = "{{ asset('images/default.jpg') }}";
                    if(type.images){
                        image = JSON.parse(type.images)[0];
                        image = "{{ asset('storage') }}/" + image.replace('.', '-cropped.');
                    }

                    tippy(`#label-description-${type.id}`, {
                        content: `  <div style="display: flex; flex-direction: row;" class="div-details">
                                        <div style="margin-right:10px">
                                            <img src="${image}" width="70px" alt="${type.name}" />
                                        </div>
                                        <div>
                                            <b>${type.name}</b><br>
                                            <small>categoría: <b>${type.category.name}</b></small><br>
                                            <small>Precio sugerido: <b>${type.price % 1 == 0 ? parseInt(type.price) : type.price.toFixed(2)} Bs.</b></small><br>
                                            <small>Precio máximo: <b>${type.max_price ? (type.max_price % 1 == 0 ? parseInt(type.max_price) : type.max_price.toFixed(2))+' Bs.'+(type.unit ? ' por '+type.unit : '') : 'No definido'}</b></small><br>
                                        </div>
                                    </div>`,
                        allowHTML: true,
                        maxWidth: 450,
                    });

                    generateNumber();
                    index++;
                    $('#select-item_id').val('').trigger('change');
                    getTotal();

                    // Cambiar etiqueta a cantidad
                    if(type.unit){
                        $('.label-unit').text('Peso');
                    }else{
                        $('.label-unit').text('Cantidad');
                    }

                    // Cambiar etiqueta a decuento
                    if(type.category.quantity_discount){
                        $('.label-discount').text('Peso de piedra');
                    }else{
                        $('.label-discount').text('');
                    }
                }
            });

            $('#form-person').submit(function(e){
                e.preventDefault();
                $.post($(this).attr('action'), $(this).serialize(), function(res){
                    if(res.success){
                        $('#person-modal').modal('hide');
                        toastr.success('Beneficiario registrado correctamente', 'Bien hecho!');
                        $(this).trigger('reset');
                    }else{
                        toastr.error(res.error, 'Error');
                    }
                    $('.form-submit .btn-submit').prop('disabled', false);
                });
            });

            $('#form-type-items').submit(function(e){
                e.preventDefault();
                $.post($(this).attr('action'), $(this).serialize(), function(res){
                    if(res.success){
                        let newOption = `<option value="${res.type.id}" data-item='${JSON.stringify(res.type)}'>${res.type.name}</option>`;
                        $('#select-item_id').append(newOption).trigger('change');
                        $('#type-items-modal').modal('hide');
                        toastr.success('Tipo registrado correctamente', 'Bien hecho!');
                        setTimeout(() => {
                            $('#select-item_id').val(res.type.id).trigger('change');
                        }, 250);
                    }else{
                        toastr.error('Ocurrió un error', 'Error');
                    }
                    $('.form-submit .btn-submit').prop('disabled', false);
                });
            });

            // Validar registro en caso de sobrepasar el precio máximo de un item
            $('#checkbox-validate').change(function(){
                if($(this).is(':checked')){
                    $('.input-price').each(function(){
                        $(this).prop('max', '');
                    });
                }else{
                    maxPrices.map(item => {
                        // Devolver el atributo max a cada input-price
                        $(`#${item.id}`).prop('max', item.max);

                        // Agregar/quitar la alerta
                        let index = item.id.substring(12);
                        alertPrice(index);
                    });
                }
            });
        });

        function createPerson(){
            dismissSelect2();
            $('#person-modal').modal('show');
        }

        function addFeature(index){
            $(`#table-features-${index}`).append(`
                <tr id="tr-features-${number_features}">
                    <td><input type="text" name="features_${index}[]" placeholder="Nuevo..." autofocus class="input-feature" /></td>
                    <td><input type="text" name="features_value_${index}[]" style="width: 120px !important" /></td>
                    <td><button type="button" class="btn-danger" onclick="removeTrFeature(${number_features})">x</button></td>
                </tr>
            `);
            // <td><input type="text" name="features_value_${index}[]" style="width: 120px !important" required /></td>

            number_features++;
        }

        function getSubtotal(index){
            let price = $(`#input-price-${index}`).val() ? parseFloat($(`#input-price-${index}`).val()) : 0;
            let priceMax = $(`#input-price-${index}`).attr('max') ? parseFloat($(`#input-price-${index}`).attr('max')) : 0;
            let quantity = $(`#input-quantity-${index}`).val() ? parseFloat($(`#input-quantity-${index}`).val()) : 0;
            let quantity_discount = $(`#input-quantity-discount-${index}`).val() ? parseFloat($(`#input-quantity-discount-${index}`).val()) : 0;

            $('#checkbox-validate').prop('checked', false).trigger('change');
            let total = price*(quantity - quantity_discount);
            $(`#input-subtotal-${index}`).val(total % 1 == 0 ? parseInt(total) : total.toFixed(2));
            getTotal();
            alertPrice(index);

            $(`#label-quantity-${index} .span-quantity`).html(quantity);
            $(`#label-quantity-${index} .span-quantity-discount`).html(quantity_discount);
            $(`#label-quantity-${index} .span-quantity-total`).html(quantity - quantity_discount);

            let minSubtotal = (quantity - quantity_discount) * price;
            let maxSubtotal = (quantity - quantity_discount) * priceMax;
            $(`#label-price-${index} .label-min-subtotal`).html(minSubtotal % 1 == 0 ? parseInt(minSubtotal) : minSubtotal.toFixed(2));
            $(`#label-price-${index} .label-max-subtotal`).html(maxSubtotal % 1 == 0 ? parseInt(maxSubtotal) : maxSubtotal.toFixed(2));
        }

        function getPrice(index){
            let subtotal = $(`#input-subtotal-${index}`).val() ? parseFloat($(`#input-subtotal-${index}`).val()) : 0;
            if(subtotal > 0){
                let quantity = $(`#input-quantity-${index}`).val() ? parseFloat($(`#input-quantity-${index}`).val()) : 0;
                let quantity_discount = $(`#input-quantity-discount-${index}`).val() ? parseFloat($(`#input-quantity-discount-${index}`).val()) : 0;
                let price = subtotal / (quantity - quantity_discount);
                $(`#input-price-${index}`).val(price % 1 == 0 ? parseInt(price) : price.toFixed(2));
                getTotal();

                // Aletar de precio máximo sobrepasado
                alertPrice(index);
            }
        }

        function getTotal(){
            let total = 0;
            $('.input-subtotal').each(function(){
                let value = parseFloat($(this).val());
                total += value;
            });

            $(`#td-total`).html(`<h4>${total.toFixed(2)}</h4>`);
            $(`#td-dolartotal`).html(`<h4>$ ${(total/7).toFixed(2)}</h4>`);
            $(`#amountTotals`).val(total.toFixed(2));
            $(`#dollarTotals`).val((total/7).toFixed(2));


            // Calcular interes
            let interest = $('#input-interest_rate').val() ? parseFloat($('#input-interest_rate').val()) : 0;
            let months = $('#input-date_limit_months').val() ? parseFloat($('#input-date_limit_months').val()) : 0
            if(interest > 0){
                $(`#td-revenue`).html(`<h4>${(total * (interest / 100) * months).toFixed(2)}</h4>`);
                $(`#td-return`).html(`<h4>${((total * (interest / 100) * months) + total).toFixed(2)}</h4>`);
            }else{
                toastr.warning('Debe definir la tasa de interes mensual', 'Advertencia');
                $(`#td-revenue`).html(`<h4>0.00</h4>`);
                $(`#td-return`).html(`<h4>0.00</h4>`);
            }
            

            // Obtener todos los precios máximos para habilitar/deshabilitar boton de guardar
            maxPrices = [];
            let validate = true;
            $('.input-price').each(function(){
                let id = $(this).prop('id');
                let val = $(this).val() ? parseFloat($(this).val()) : 0;
                let max = $(this).prop('max') ? parseFloat($(this).prop('max')) : '';
                maxPrices.push({id, val, max})

                // Si el valor se sobrepasa del precio máximo necesita validación
                if(max && val > max){
                    validate = false;
                }
            });

            // Si algún precio se sobrepasa necesita hacer check en validar
            if (validate) {
                $('#div-validate').fadeOut('fast');
                $('#checkbox-validate').prop('required', false);
                $('#checkbox-validate').prop('checked', false);
            } else {
                $('#div-validate').fadeIn('fast');
                $('#checkbox-validate').prop('required', true);
            }
        }

        function generateNumber(){
            let number = 1;
            $('.td-number').each(function(){
                $(this).text(number);
                number++;
            });

            // Si está vacío
            if(number == 1){
                $('.tr-empty').css('display', 'block');
            }
        }

        function alertPrice(index){
            let price = $(`#input-price-${index}`).val() ? parseFloat($(`#input-price-${index}`).val()) : 0;
            let price_max = $(`#input-price-${index}`).prop('max');
            if(price_max && price > price_max){
                $(`#input-price-${index}`).css('color', 'red');
                $(`#input-subtotal-${index}`).css('color', 'red');
                $(`#input-price-${index}`).prop('title', `El precio máximo es ${price_max % 1 == 0 ? parseInt(price_max) : price_max.toFixed(2)} Bs.`);
            }else{
                $(`#input-price-${index}`).css('color', '#555555');
                $(`#input-subtotal-${index}`).css('color', '#555555');
                $(`#input-price-${index}`).prop('title', '');
            }
        }

        function triggerClickInput(index){
            $('#input-file-'+index).trigger('click');
        }

        function changeImage(index){
            const fileInput = document.getElementById('input-file-'+index);
            const imgPreview = document.getElementById('img-preview-'+index);
            const selectedImage = fileInput.files[0];
            const imageURL = URL.createObjectURL(selectedImage);
            imgPreview.src = imageURL;
            // console.log(imageURL);
        }

        function removeTr(index){
            $(`.tr-item-${index}`).remove();
            generateNumber();
            getTotal();
        }

        function removeTrFeature(index){
            $(`#tr-features-${index}`).remove();
            generateNumber();
            getTotal();
        }

        function dismissSelect2(){
            $('#select-people_id').select2("close");
            $('#select-item_id').select2("close");
        }
    </script>
@stop