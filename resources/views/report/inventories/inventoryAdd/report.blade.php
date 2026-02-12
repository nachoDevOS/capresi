@extends('voyager::master')

@section('page_title', 'Reporte Registro de Inventario')
@if(auth()->user()->hasPermission('browse_printinventories')  )


@section('page_header')

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0px">
                        <div class="col-md-8" style="padding: 0px">
                            <h1 class="page-title">
                                <i class="voyager-calendar"></i> Reporte Registro Inventario
                            </h1>
                        </div>
                        <div class="col-md-4" style="margin-top: 30px">
                            <form name="form_search" id="form-search" action="{{ route('print-inventories.list') }}" method="post">

                                @csrf
                                <input type="hidden" name="print">

                                <div class="form-group">
                                    <div class="form-line">
                                        <select id="type" name="type" class="form-control select2" required>
                                            <option value=""disabled selected>--- Seleccione una opcion ---</option>                                      
                                            <option value="">Todo</option>                                      
                                            <option value="prendario">Prendario</option>                                      
                                            <option value="manual">Manual</option>                                      
                                        </select>
                                        <small>Tipo de Registro</small>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <div class="form-line">
                                        <input type="date" name="start" required class="form-control">
                                        <small>Inicio</small>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="form-line">
                                        <input type="date" name="finish" required class="form-control">
                                        <small>Fin</small>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="form-line">
                                        <select name="agent_id" class="form-control select2" required>
                                            <option value=""disabled selected>--- Seleccione una opcion ---</option>
                                            @foreach ($user as $item)
                                                <option value="{{$item->id}}">{{$item->name}}</option>
                                            @endforeach                                             
                                        </select>
                                        <small>Persona</small>
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
@stop

@section('css')
<style>
    a{
    text-decoration: none;
    }

    .main-wrap {
        background: #000;
            text-align: center;
    }
    .main-wrap h1 {
            color: #fff;
                margin-top: 50px;
        margin-bottom: 100px;
    }
    .col-md-3 {
        display: block;
        float:left;
        margin: 1% 0 1% 1.6%;
        background-color: #eee;
    padding: 50px 0;
    }

    .col:first-of-type {
        margin-left: 0;
    }



</style>
@stop

@section('javascript')

    <script>
    $(document).ready(function () {
      // Función para actualizar el estado de las opciones
      function updateTipoPrestamos(selectedValue) {
        const qrOption = $("#type option[value='Qr']"); // Opción "Cobros con QR"
        
        if (selectedValue === "prenda") {
            qrOption.prop("disabled", true); // Deshabilitar la opción
            $("#type").val(""); // Resetear selección
            $("#type").trigger("change"); // Dispara el evento de cambio si es necesario
        } else {
          qrOption.prop("disabled", false); // Habilitar la opción
        }
      }
  
      // Evento change en el select de "Préstamos"
      $("#prestamos").change(function () {
        const selectedValue = $(this).val(); // Obtener el valor seleccionado
        updateTipoPrestamos(selectedValue); // Actualizar opciones del segundo select
      });
    });
    </script>

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

        });

        function report_print(){
            $('#form-search').attr('target', '_blank');
            $('#form-search input[name="print"]').val(1);
            window.form_search.submit();
            $('#form-search').removeAttr('target');
            $('#form-search input[name="print"]').val('');
        }
        function report_excel()
        {
            // $('#form-search').attr('target', '_blank');
            $('#form-search input[name="print"]').val(2);
            window.form_search.submit();
             $('#form-search').removeAttr('target');
            $('#form-search input[name="print"]').val('');
        }


        
    </script>
@stop
@else
    @section('content')
        {{-- @include('errors.403') --}}
        <h1>403</h1>
    @stop
@endif