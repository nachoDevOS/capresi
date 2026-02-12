@extends('voyager::master')

@section('page_title', 'Crear planilla')

@if (auth()->user()->hasPermission('add_spreadsheets'))
    @section('page_header')
        <h1 id="titleHead" class="page-title">
            <i class="icon fa-solid fa-sheet-plastic"></i>Crear Planilla
        </h1>
        <a href="{{ route('spreadsheets.index') }}" class="btn btn-warning">
            <i class="fa-solid fa-rotate-left"></i> <span>Volver</span>
        </a>
    @stop

    @section('content')
        <div class="page-content edit-add container-fluid">    
            <form id="agent" action="{{ route('spreadsheets.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-bordered">
                            <div class="panel-body">    
                                <h5>Planilla</h5>

                                <div class="row">
                                    <div class="form-group col-md-3">
                                        <small>Mes y Año</small>
                                        <input type="month"  class="form-control text" required id="month-year" name="month-year">
                                    </div> 
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <small>Descripción</small>
                                        <textarea name="description" id="description" required class="form-control text" cols="30" rows="5"></textarea>
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
                                <p>Desea guardar la planilla?</p>
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
    @stop

    @section('css')
        <style>

        </style>
    @endsection

    @section('javascript')
        <script>

            $(document).ready(function(){
           
                
            })


        </script>
    @stop
@endif