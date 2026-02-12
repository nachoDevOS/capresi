@extends('voyager::master')

@section('page_title', 'Viendo Asistencia')
@if (auth()->user()->hasPermission('browse_attendances'))
@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0px">
                        <div class="col-md-9" style="padding: 0px">
                            <h1 id="titleHead" class="page-title">
                                <i class="fa-solid fa-file-import"></i> Importar Asistencias
                            </h1>
                        </div>
                        <div class="col-md-3" style="margin-top: 10px" >
                            @if (auth()->user()->hasPermission('add_attendances'))
                                <form name="form_search" id="form-search" action="{{ route('attendances.import') }}" method="post" enctype="multipart/form-data">
                                    @csrf
                                    <input type="file" name="file" id="file" accept=".xlsx, .xls, .csv" style="text-align: right" required>
                                    <button type="submit" class="btn btn-success"><i class="fa-brands fa-usb"></i> <span>Importar</span></button>
                                </form>
                            @endif
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
                            <div class="col-sm-10">
                                <div class="dataTables_length" id="dataTable_length">
                                    <label>Mostrar <select id="select-paginate" class="form-control input-sm">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select> registros</label>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <input type="text" id="input-search" class="form-control" placeholder="Buscar...">
                            </div>

                        </div>
                        <div class="row" id="div-results" style="min-height: 120px"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>




 
@stop

@section('css')

@stop

@section('javascript')
    <script src="{{ url('js/main.js') }}"></script>
        
    {{-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script> --}}
    <script>
        var countPage = 10, order = 'id', typeOrder = 'desc';
        $(document).ready(() => {
            list();
            
            $('.radio-type').click(function(){
                list();
            });

            $('#input-search').on('keyup', function(e){
                if(e.keyCode == 13) {
                    list();
                }
            });

            $('#select-paginate').change(function(){
                countPage = $(this).val();
               
                list();
            });
        });


        function list(page = 1){
            $('#div-results').loading({message: 'Cargando...'});
            // let type = $(".radio-type:checked").val();
            
            // let url = "{{ url('admin/loans/ajax/list')}}/"+cashier_id;
            let url = '{{ url("admin/attendances/ajax/list") }}';

            let search = $('#input-search').val() ? $('#input-search').val() : '';

            $.ajax({
                url: `${url}/${search}?paginate=${countPage}&page=${page}`,
                type: 'get',
                
                success: function(result){
                    $("#div-results").html(result);
                    $('#div-results').loading('toggle');
                }
            });
        }
    
       
    </script>
@stop
@endif
