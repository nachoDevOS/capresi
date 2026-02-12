@extends('voyager::master')

@section('page_title', 'Turnos')

@if (auth()->user()->hasPermission('add_late_penalties'))

    @section('page_header')

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0px">
                        <div class="col-md-6">
                            <h1 id="titleHead" class="page-title">
                                <i class="fa-solid fa-file-invoice-dollar"></i> Añadir Sanción Por Retrazo
                            </h1>
                            <a href="{{ route('voyager.late-penalties.index') }}" class="btn btn-warning">
                                <i class="fa-solid fa-rotate-left"></i> <span>Volver</span>
                            </a>
                        </div>
                        <div class="col-md-6 text-right" style="margin-top: 30px">
                            
                        </div>  
                    </div>
                </div>
            </div>
        </div>
    </div>
        
    @stop

    @section('content')
        <form id="agent" action="{{isset($data)?route('late-penalties.update', ['id'=>$data->id]):route('late-penalties.store') }}" method="POST">
        @csrf
        @isset($data)
                @method('PUT')
        @endisset
            <div class="page-content edit-add container-fluid">    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-bordered">
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="form-group col-md-4">
                                            <small>Minuto Minimo</small>
                                            <input type="number" value="{{$data?$data->start:0}}" min="0" step="1" name="start" id="start" required class="form-control">
                                        </div> 
                                        <div class="form-group col-md-4">
                                            <small>Minuto Maximo</small>
                                            <input type="number" value="{{$data?$data->finish:0}}" min="0" step="1" name="finish" id="finish" required class="form-control">
                                        </div>    
                                        <div class="form-group col-md-4">
                                            <small>Bs. Sanción</small>
                                            <input type="number" value="{{$data?$data->amount:0}}" min="1" step="1" name="amount" id="amount" required class="form-control">
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
            </div>
        </form>
    @stop

    @section('css')
  
    @endsection

    @section('javascript')
        <script>

            $(document).ready(function(){
                
            })

            function deleteItem(url){
                $('#delete_form').attr('action', url);
            }

            function save()
            {
                let id = $(`#id`).val();
                let name = $(`#name`).val()?$(`#name`).val():0;
                let description = $(`#description`).val()?$(`#description`).val():0;
                $.get('{{route('shifts.name')}}/'+id+'/'+name+'/'+description, function(data){ 
                    // toastr.success('Producto agregado..', 'Información');
                    $('#id').val(data);
                    $('#id1').val(data);


                });
            }

        </script>
    @stop
@endif