@extends('voyager::master')

@section('page_title', 'Viendo Adelantos')

@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <h1 class="page-title">
                    <i class="voyager-dollar"></i> Adelantos
                </h1>
            </div>
            <div class="col-md-8 text-right" style="padding-top: 20px">
                <a href="#" data-toggle="modal" data-target="#payment-modal" class="btn btn-info btn-payment" style="display: none; border: 0px; margin-top: 2px">
                    <i class="voyager-dollar"></i> <span>Saldar</span>
                </a>
                <a href="#" data-toggle="modal" data-target="#add-modal" class="btn btn-success btn-add-new">
                    <i class="voyager-plus"></i> <span>Crear</span>
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="page-content browse container-fluid">
        @include('voyager::alerts')
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <div class="table-responsive">
                            <form action="{{ route('employes.payoff.store', $employe->id) }}" class="form-submit" method="post">
                                @csrf
                                <table id="dataStyle" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>N&deg;</th>
                                            <th>Fecha</th>
                                            <th>Descripción</th>
                                            <th>Monto</th>
                                            <th>Estado</th>
                                            <th class="text-right">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $cont = 1;
                                        @endphp
                                        @foreach ($employe->payments->sortByDesc('date') as $item)
                                            <tr>
                                                <td><input type="checkbox" name="payment_id[]" value="{{ $item->id }}" class="checkbox-payment_id" @if($item->status != 'pendiente') disabled @endif></td>
                                                <td>{{ $cont }}</td>
                                                <td>{{ date('d/m/Y', strtotime($item->date)) }}</td>
                                                <td>{{ $item->description }}</td>
                                                <td>{{ $item->amount == intval($item->amount) ? intval($item->amount) : $item->amount }}</td>
                                                <td><label class="label label-{{ $item->status == 'pendiente' ? 'danger' : 'success' }}">{{ ucfirst($item->status) }}</label></td>
                                                <td class="no-sort no-click bread-actions">
    
                                                </td>
                                            </tr>
                                            @php
                                                $cont++;
                                            @endphp
                                        @endforeach
                                    </tbody>
                                </table>

                                <div class="modal modal-info fade" tabindex="-1" id="payment-modal" role="dialog">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('voyager::generic.close') }}"><span aria-hidden="true">&times;</span></button>
                                                <h4 class="modal-title"><i class="voyager-dollar"></i> Desea saldar los adelantos?</h4>
                                            </div>
                                            <div class="modal-footer text-right">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-primary btn-submit">Saldar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Add modal --}}
    <form action="{{ route('employes.payments.store', $employe->id) }}" class="form-submit" id="delete_form" method="POST">
        @csrf
        <div class="modal modal-info fade" tabindex="-1" id="add-modal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('voyager::generic.close') }}"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="voyager-dollar"></i> Registrar adelanto</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="amount">Monto</label>
                            <input type="number" name="amount" class="form-control" step="0.5" min="1" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Descripción</label>
                            <textarea name="description" class="form-control" rows="5" placeholder="Describa el motivo del adelanto" required></textarea>     
                        </div>
                        <div class="form-group">
                            <label for="date">Fecha</label>
                            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="modal-footer text-right">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-submit">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop

@section('css')

@stop

@section('javascript')
<script src="{{ asset('js/main.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#dataStyle').DataTable({language});

            $('.checkbox-payment_id').click(function(){
                if($('.checkbox-payment_id').filter(':checked').length > 0){
                    $('.btn-payment').fadeIn();
                }else{
                    $('.btn-payment').fadeOut();
                }
            });
        });
    </script>
@stop