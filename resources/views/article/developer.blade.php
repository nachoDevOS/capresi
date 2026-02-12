@extends('voyager::master')

@section('page_title', 'Crear Input')

{{-- @if (auth()->user()->hasRole('admin')) --}}


    @section('page_header')
        <h1 id="titleHead" class="page-title">
            <i class="fa-solid fa-code"></i> Desarrollo 
        </h1>
        <a href="{{ route('voyager.articles.index') }}" class="btn btn-warning">
            <i class="fa-solid fa-rotate-left"></i> <span>Volver</span>
        </a>
    @stop

    @section('content')
        <div class="page-content edit-add container-fluid">    
            
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-bordered">
                            <div class="panel-heading">
                                <h6 class="panel-title">Patrocinadores</h6>
                            </div>
                            <div class="panel-body">
                                <form id="agent" action="{{route('articles-developer.store', ['article_id'=>$article->id])}}" method="POST">
                                @csrf


                                       

                                        {{-- <small>Modelo</small>
                                        <select name="model2[]" id="model2" class="form-control select2" required>
                                            <option value="" disabled selected>--Selecciona una opción--</option>
                                            @foreach (App\Models\ModelGarment::where('deleted_at', null)->get() as $item)
                                                <option value="{{$item->id}}">{{ $item->name }}</option>                                            
                                            @endforeach
                                        </select> --}}

                                    {{-- {!!"<small>Modelo</small>
                                            @php
                                                $aaa = App\Models\ModelGarment::where('deleted_at', null)->get();
                                                dump($data);
                                            @endphp 
                                        <select name='model2[]' id='model2' class='form-control select2' required>
                                            <option value='' disabled selected>--Selecciona una opción--</option>
                                            
                                        </select>"
                                        !!} --}}
<br>
                                        
                                    <div class="row">
                                        <div class="form-group col-md-2">
                                            <small>Titulo</small>
                                            <input type="text" name="title" id="title" class="form-control" required>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <small>Herramienta</small>
                                            <input type="text" name="tool" id="tool" class="form-control" required>
                                            {{-- <textarea name="detail" id="detail" clas                           --}}
                                        </div>
                                        <div class="form-group col-md-2">
                                            <small>Tipo</small>
                                            <input type="text" name="type" id="type" class="form-control">
                                            {{-- <textarea name="--}}
                                        </div>
                                        <div class="form-group col-md-2">
                                            <small>Id/Name/Class</small>
                                            <input type="text" name="detail" id="detail" class="form-control" required>
                                            {{-- <textarea name="--}}
                                        </div>    
                                        
                                        <div class="form-group col-md-2">
                                            <small>Concatenar</small>
                                            <input type="text" name="concatenar" id="concatenar" class="form-control">
                                        </div>  
                                        <div class="form-group col-md-2">
                                            <small>Requerido</small>
                                            <select name="required" id="required" class="form-control select2">
                                                <option value="">No Requerido</option>
                                                <option value="required">Requerido</option>
                                            </select>
                                        </div> 
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 text-right">
                                            <button type="submit" class="btn btn-primary">Guardar</button>
                                        </div>
                                    </div>
                                </form> 
                                <div class="table-responsive">
                                    <table id="dataStyle" class=" table-hover">
                                        <thead>
                                            <tr>
                                                <th style="width: 30">id</th>
                                                <th style="text-align: center">Titulo</th>
                                                <th style="text-align: center">Herramienta</th>
                                                <th style="text-align: center">Tipo</th>
                                                <th style="text-align: center">Id/Name/Class</th>
                                                <th style="text-align: center">Concatenado</th>
                                                <th style="text-align: center">Requerido</th>
                                                <th style="text-align: center">Resultado</th>
                                                <th style="text-align: right">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($article->developer as $item)
                                                <tr>
                                                    <td style="text-align: center">{{$item->id}}</td>
                                                    <td style="text-align: center">{{$item->title}}</td>
                                                    <td style="text-align: center">{{$item->tool}}</td>
                                                    <td style="text-align: center">{{$item->type}}</td>
                                                    <td style="text-align: center">{{$item->detail}}</td>
                                                    <td style="text-align: center">{{$item->concatenar}}</td>
                                                    <td style="text-align: center">{{$item->required}}</td>
                                                    <td style="text-align: left">
                                                        @if ($item->tool == 'input')
                                                            <small>{{$item->title}}</small>
                                                            <input type="{{$item->type}}" name="{{$item->detail}}" id="{{$item->detail}}" @if($item->type=='number')style="text-align: right" value="0" min="1" step=".01" @endif class="form-control">
                                                        
                                                        @endif
                                                        @if ($item->tool == 'select')
                                                            <small>{{$item->title}}</small>
                                                            <select name="" id="" class="form-control select2">
                                                                <option value="">opciones</option>
                                                            </select>
                                                            {{-- <input type="{{$item->type}}" name="{{$item->detail}}" id="{{$item->detail}}" @if($item->type=='number')style="text-align: right" value="0" min="1" step=".01" @endif class="form-control"> --}}
                                                        
                                                        @endif

                                                    
                                                    </td>
                                                    <td class="no-sort no-click bread-actions text-right">
                                                        <button title="Borrar" class="btn btn-sm btn-danger delete" onclick="deleteItem('{{ route('articles-developer.destroy', ['article_id' => $article->id, 'detail_id'=>$item->id]) }}')" data-toggle="modal" data-target="#delete-modal">
                                                            <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm">Borrar</span>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr style="text-align: center">
                                                    <td colspan="9" class="dataTables_empty">No hay datos disponibles en la tabla</td>
                                                </tr>
                                            @endforelse                                      
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>                
                         
        </div>
        <div class="modal modal-danger fade" data-backdrop="static" tabindex="-1" id="delete-modal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="voyager-trash"></i> Desea eliminar el siguiente registro?</h4>
                    </div>
                    <div class="modal-footer">
                        <form action="#" id="delete_form" method="POST">
                            {{ method_field('DELETE') }}
                            {{ csrf_field() }}
                            <input type="hidden" name="id" id="id">
    
                                <div class="text-center" style="text-transform:uppercase">
                                    <i class="voyager-trash" style="color: red; font-size: 5em;"></i>
                                    <br>
                                    
                                    <p><b>Desea eliminar el siguiente registro?</b></p>
                                </div>
                            <input type="submit" class="btn btn-danger pull-right delete-confirm" value="Sí, eliminar">
                        </form>
                        <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal modal-success fade" data-backdrop="static" tabindex="-1" id="habilitar-modal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="fa-solid fa-motorcycle"></i> Desea habilitar el siguiente registro?</h4>
                    </div>
                    <div class="modal-footer">
                        <form action="#" id="habilitar_form" method="GET">
                                <div class="text-center" style="text-transform:uppercase">
                                    <i class="fa-solid fa-thumbs-up" style="color: #1abc9c; font-size: 5em;"></i>
                                    <br>
                                    
                                    <p><b>Desea habilitar el siguiente registro?</b></p>
                                </div>
                            <input type="submit" class="btn btn-success pull-right delete-confirm" value="Sí, habilitar">
                        </form>
                        <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    
        <div class="modal modal-warning fade" data-backdrop="static" tabindex="-1" id="inhabilitar-modal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="fa-solid fa-motorcycle"></i> Desea Inhabilitar el siguiente registro?</h4>
                    </div>
                    <div class="modal-footer">
                        <form action="#" id="inhabilitar_form" method="GET">
    
                                <div class="text-center" style="text-transform:uppercase">
                                    <i class="fa-solid fa-thumbs-down" style="color: #fabe28; font-size: 5em;"></i>
                                    <br>
                                    
                                    <p><b>Desea inhabilitar el siguiente registro?</b></p>
                                </div>
                            <input type="submit" class="btn btn-warning pull-right delete-confirm" value="Sí, inhabilitar">
                        </form>
                        <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    @stop

    @section('css')
        <style>

        </style>
    @endsection

    @section('javascript')
        <script>
            function deleteItem(url){
                $('#delete_form').attr('action', url);
            }

            function inhabilitarItem(url){
                $('#inhabilitar_form').attr('action', url);
            }

            function habilitarItem(url){
                $('#habilitar_form').attr('action', url);
            }
        </script>
    @stop

{{-- @endif --}}