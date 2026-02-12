@extends('voyager::master')

@section('page_title', 'Viendo Historial de lista Diaria')

@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0px">
                        <div class="col-md-8" style="padding: 0px">
                            <h1 class="page-title">
                                <i class="fa-solid fa-landmark"></i> Historial de lista Diaria
                            </h1>
                        </div>
                        <div class="col-md-4 text-right" style="margin-top: 30px">
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')

    <div class="page-content browse container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-7">
                                <div class="dataTables_length" id="dataTable_length">
                                    <label>Mostrar <select id="select-paginate" class="form-control input-sm">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select> registros</label>
                                </div>
                            </div>

                            <div class="col-sm-2" style="padding-right: 0px">
                                <select name="status" class="form-control select2" id="select-status">
                                    <option value="" selected>Todos</option>
                                    <option value="inicio">Inicio</option>
                                    <option value="fin">Fin</option>
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <input type="text" id="input-search" class="form-control">
                            </div>
                        </div>
                        <div class="row" id="div-results" style="min-height: 120px"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    @include('partials.modal-delete')



 
@stop

@section('css')
<style>

    
    
    </style>
@stop

@section('javascript')
    <script src="{{ url('js/main.js') }}"></script>
        
    {{-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script> --}}
    <script>
        var countPage = 10, order = 'id', typeOrder = 'desc';
        $(document).ready(() => {
            list();
            
            $('#input-search').on('keyup', function(e){
                if(e.keyCode == 13) {
                    list();
                }
            });
            $('#select-status').change(function(){
                list();
            });

            $('#select-paginate').change(function(){
                countPage = $(this).val();
               
                list();
            });
        });

        function deleteItem(url){
            $('#delete_form').attr('action', url);
        }
        function list(page = 1){
            $('#div-results').loading({message: 'Cargando...'});
            let status =$("#select-status").val();
            let url = '{{ url("admin/history/dailyList/ajax/list") }}';
            // let url = "{{ route('pawn.list')}}";
            
            let search = $('#input-search').val() ? $('#input-search').val() : '';

            $.ajax({
                // url: `${url}/${search}?paginate=${countPage}&page=${page}`,
                url: `${url}?paginate=${countPage}&page=${page}&status=${status}&search=${search}`,
                type: 'get',
                
                success: function(result){
                    $("#div-results").html(result);
                    $('#div-results').loading('toggle');
                }
            });

        }
        var id=0;
        var phone =0;
        var name ='';
        $('#verificar-modal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget) //captura valor del data-empresa=""
            id = button.data('id')
            phone = button.data('phone')
            name = button.data('name')
            var modal = $(this)
            
            modal.find('.modal-body #id').val(id)
            modal.find('.modal-body #name').val(name)
            modal.find('.modal-body #phone').val(phone)
        });



        $('#form-create-customer').submit(function(e){
                // alert(1)
                e.preventDefault();
                $('.btn-save-customer').attr('disabled', true);
                $('.btn-save-customer').val('Guardando...');
                $.post($(this).attr('action'), $(this).serialize(), function(data){
                    if(data.customer){
                        toastr.success('Solicitud Enviada', 'Éxito');
                        $(this).trigger('reset');
                    }else{
                        toastr.error(data.error, 'Error');
                    }
                })
                .always(function(){
                    $('.btn-save-customer').attr('disabled', false);
                    $('.btn-save-customer').text('Guardar');
                    // $('#modal-create-customer').modal('hide');
                    $("#verificar-modal").modal('hide');

                });
            });

       
    </script>
@stop