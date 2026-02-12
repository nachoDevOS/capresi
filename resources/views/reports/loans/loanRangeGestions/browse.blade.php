@extends('voyager::master')

@section('page_title', 'Reporte de Prestamos')
@if(auth()->user()->hasPermission('browse_printloanRangeGestion'))


@section('page_header')

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0px">
                        <div class="col-md-8" style="padding: 0px">
                            <h1 class="page-title">
                                <i class="voyager-calendar"></i> Reporte de Prestamos Por Rango de Gestión
                            </h1>
                        </div>
                        <div class="col-md-4" style="margin-top: 30px">
                            <form name="form_search" id="form-search" action="{{ route('print-loanRangeGestion.list') }}" method="post">

                                @csrf
                                <input type="hidden" name="print">
                                


                                {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.6.1/nouislider.min.css">
                                <script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.6.1/nouislider.min.js"></script>
                                
                                <div id="slider-rango"></div>
                                <input type="hidden" id="inicio" name="inicio">
                                <input type="hidden" id="fin" name="fin">
                                
                                <script>
                                    const slider = document.getElementById('slider-rango');
                                    noUiSlider.create(slider, { 
                                        start: [7, 21], // Valores iniciales [inicio, fin]
                                        connect: true,   // Muestra una barra conectada
                                        range: {
                                            'min': 1,
                                            'max': 24
                                        },
                                        step: 1,        // Incrementos de 1 en 1
                                    });
                                
                                    // Actualiza los inputs ocultos al mover el slider
                                    slider.noUiSlider.on('update', function(values) {
                                        document.getElementById('inicio').value = parseInt(values[0]);
                                        document.getElementById('fin').value = parseInt(values[1]);
                                    });
                                </script> --}}





                                
                                <div class="form-group">
                                    <div class="form-line">
                                        <input type="date"  class="form-control text" required id="start" name="start">
                                        <small>Inicio</small>
                                    </div> 
                                </div>
                                <div class="form-group">
                                    <div class="form-line">
                                        <input type="date"  class="form-control text" required id="finish" name="finish">
                                        <small>Fin</small>
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


        /* ALL LOADERS */

        .loader{
            width: 100px;
            height: 100px;
            border-radius: 100%;
            position: relative;
            margin: 0 auto;
        }
        /* LOADER 3 */

        #loader-3:before, #loader-3:after{
            content: "";
            width: 20px;
            height: 20px;
            position: absolute;
            top: 0;
            left: calc(50% - 10px);
            background-color: #3498db;
            animation: squaremove 1s ease-in-out infinite;
        }

        #loader-3:after{
            bottom: 0;
            animation-delay: 0.5s;
        }

        @keyframes squaremove{
            0%, 100%{
                -webkit-transform: translate(0,0) rotate(0);
                -ms-transform: translate(0,0) rotate(0);
                -o-transform: translate(0,0) rotate(0);
                transform: translate(0,0) rotate(0);
            }

            25%{
                -webkit-transform: translate(40px,40px) rotate(45deg);
                -ms-transform: translate(40px,40px) rotate(45deg);
                -o-transform: translate(40px,40px) rotate(45deg);
                transform: translate(40px,40px) rotate(45deg);
            }

            50%{
                -webkit-transform: translate(0px,80px) rotate(0deg);
                -ms-transform: translate(0px,80px) rotate(0deg);
                -o-transform: translate(0px,80px) rotate(0deg);
                transform: translate(0px,80px) rotate(0deg);
            }

            75%{
                -webkit-transform: translate(-40px,40px) rotate(45deg);
                -ms-transform: translate(-40px,40px) rotate(45deg);
                -o-transform: translate(-40px,40px) rotate(45deg);
                transform: translate(-40px,40px) rotate(45deg);
            }
        }
    </style>
@stop

@section('javascript')
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
    </script>
@stop
@else
    @section('content')
        <h1>Sin Permiso</h1>
    @stop
@endif