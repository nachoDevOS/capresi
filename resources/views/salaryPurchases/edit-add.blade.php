@extends('voyager::master')

@section('page_title', 'Registrar Compra de Salarios')

@section('page_header')
    <h1 id="titleHead" class="page-title">
        <i class="fa-solid fa-wallet"></i> Compra de Salarios
    </h1>
@stop

@section('content')
    <div class="page-content edit-add container-fluid">    
        <form class="form-submit" id="form-submit" action="{{ route('salary-purchases.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-bordered">
                        <div class="panel-body">
                            <h5>Datos Generales</h5>
                            
                            <div class="row">                              
                                <div class="form-group col-md-6">
                                    <small for="people_id">Beneficiario del Prestamo</small>
                                    <select name="people_id" class="form-control" id="select-people_id" required></select>
                                </div>
                                <div class="form-group col-md-2">
                                    <small for="interest_rate">Monto a Prestar</small>
                                    <div class="input-group">
                                        <input type="number" name="amount" id="amount" class="form-control" value="0" step="1" min="1"required>
                                        <span class="input-group-addon">Bs.</span>
                                    </div>
                                </div>
                                
                                <div class="form-group col-md-2">
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
                            </div>
                            <hr>
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label for="observations">Observaciones</label>
                                    <textarea name="observations" class="form-control" rows="3" placeholder=""></textarea>
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
            <div class="modal fade" id="confirm-modal" data-backdrop="static" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title"><i class="voyager-thumbs-up"></i> Confirmación</h4>
                        </div>
                        <div class="modal-body">
                            <p>Desea guardar el registro?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                            <input type="submit" class="btn btn-primary btn-submit" value="Aceptar">
                        </div>
                    </div>
                </div>
            </div>
        </form>       
    </div>
    

@stop

@section('css')
    <style>
        .select2{
            width: 100% !important;
        }
      
    </style>
@stop

@section('javascript')
    <script src="{{ asset('js/main.js') }}"></script>
    <script>
        var index = 0;
        var number_features = 0;
        var maxPrices = [];
        var enableValidation = false;
        $(document).ready(function(){
            
            customSelect('#select-people_id', '{{ url("admin/people/search/ajax") }}', formatResultPeople, data => data.first_name+' '+data.last_name1+' '+data.last_name2, null);
            
        
        });


        $('#form-submit').submit(function(e){
            $('.btn-submit').attr('disabled', true);
            $('.btn-submit').val('Guardando...');
        });

      



    </script>
@stop