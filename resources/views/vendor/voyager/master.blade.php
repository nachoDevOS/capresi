<!DOCTYPE html>
<html lang="{{ config('app.locale') }}" dir="{{ __('voyager::generic.is_rtl') == 'true' ? 'rtl' : 'ltr' }}">
<head>
    <title>@yield('page_title', setting('admin.title') . " - " . setting('admin.description'))</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <meta name="assets-path" content="{{ route('voyager.voyager_assets') }}"/>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,700" rel="stylesheet">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/style/dataTable.css') }}">

    <link rel="stylesheet" href="{{ asset('css/style/page-title.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style/small.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style/h.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style/input.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style/label.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style/p.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style/li.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style/span.css') }}">

    <link rel="stylesheet" href="{{ asset('css/image-expandable.css') }}">

    {{-- show swetalert message --}}
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Favicon -->
    <?php $admin_favicon = Voyager::setting('admin.icon_image', ''); ?>
    @if($admin_favicon == '')
        <link rel="shortcut icon" href="{{ asset('images/icon.png') }}" type="image/png">
    @else
        <link rel="shortcut icon" href="{{ Voyager::image($admin_favicon) }}" type="image/png">
    @endif



    <!-- App CSS -->
    <link rel="stylesheet" href="{{ voyager_asset('css/app.css') }}">

    <style>
        #voyager-loader img{
            animation:none !important;
            height:170px;
            left:50%;
            margin-left:-100px;
            margin-right:-50px;
            position:absolute;
            top:40%;
            width:200px
        }
    </style>

    @yield('css')
    @if(__('voyager::generic.is_rtl') == 'true')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-rtl/3.4.0/css/bootstrap-rtl.css">
        <link rel="stylesheet" href="{{ voyager_asset('css/rtl.css') }}">
    @endif

    <!-- Few Dynamic Styles -->
    <style type="text/css">
        .voyager .side-menu .navbar-header {
            background:{{ config('voyager.primary_color','#22A7F0') }};
            border-color:{{ config('voyager.primary_color','#22A7F0') }};
        }
        .widget .btn-primary{
            border-color:{{ config('voyager.primary_color','#22A7F0') }};
        }
        .widget .btn-primary:focus, .widget .btn-primary:hover, .widget .btn-primary:active, .widget .btn-primary.active, .widget .btn-primary:active:focus{
            background:{{ config('voyager.primary_color','#22A7F0') }};
        }
        .voyager .breadcrumb a{
            color:{{ config('voyager.primary_color','#22A7F0') }};
        }
    </style>

    @if(!empty(config('voyager.additional_css')))<!-- Additional CSS -->
        @foreach(config('voyager.additional_css') as $css)<link rel="stylesheet" type="text/css" href="{{ asset($css) }}">@endforeach
    @endif

    @yield('head')
</head>
    <body class="voyager @if(isset($dataType) && isset($dataType->slug)){{ $dataType->slug }}@endif">
        <div id="voyager-loader" style="animation: none !important;">
            <?php $admin_loader_img = Voyager::setting('admin.loader', ''); ?>
            @if($admin_loader_img == '')
                <img src="{{ asset('images/loader_img.gif') }}" alt="Voyager Loader">
            @else
                <img src="{{ Voyager::image($admin_loader_img) }}" alt="Voyager Loader">
            @endif
        </div>

        <?php
            if (\Illuminate\Support\Str::startsWith(Auth::user()->avatar, 'http://') || \Illuminate\Support\Str::startsWith(Auth::user()->avatar, 'https://')) {
                $user_avatar = Auth::user()->avatar;
            } else {
                $user_avatar = Voyager::image(Auth::user()->avatar);
            }
        ?>

        <div class="app-container">
            <div class="fadetoblack visible-xs"></div>
            <div class="row content-container">
                @include('voyager::dashboard.navbar')
                @include('voyager::dashboard.sidebar')
                <script>
                    var whatsappServer = "{{ setting('servidores.whatsapp') }}";
                    var whatsappServerSession = "{{ setting('servidores.whatsapp-session') }}";
                    var imagesGeneratorServer = "{{ setting('servidores.image-from-url') }}";
                    (function(){
                            var appContainer = document.querySelector('.app-container'),
                                sidebar = appContainer.querySelector('.side-menu'),
                                navbar = appContainer.querySelector('nav.navbar.navbar-top'),
                                loader = document.getElementById('voyager-loader'),
                                hamburgerMenu = document.querySelector('.hamburger'),
                                sidebarTransition = sidebar.style.transition,
                                navbarTransition = navbar.style.transition,
                                containerTransition = appContainer.style.transition;

                            sidebar.style.WebkitTransition = sidebar.style.MozTransition = sidebar.style.transition =
                            appContainer.style.WebkitTransition = appContainer.style.MozTransition = appContainer.style.transition =
                            navbar.style.WebkitTransition = navbar.style.MozTransition = navbar.style.transition = 'none';

                            if (window.innerWidth > 768 && window.localStorage && window.localStorage['voyager.stickySidebar'] == 'true') {
                                appContainer.className += ' expanded no-animation';
                                loader.style.left = (sidebar.clientWidth/2)+'px';
                                hamburgerMenu.className += ' is-active no-animation';
                            }

                        navbar.style.WebkitTransition = navbar.style.MozTransition = navbar.style.transition = navbarTransition;
                        sidebar.style.WebkitTransition = sidebar.style.MozTransition = sidebar.style.transition = sidebarTransition;
                        appContainer.style.WebkitTransition = appContainer.style.MozTransition = appContainer.style.transition = containerTransition;
                    })();
                </script>
                <!-- Main Content -->
                <div class="container-fluid">
                    <div class="side-body padding-top">
                        @yield('page_header')
                        <div id="voyager-notifications"></div>

                        <div class="hover-container">
                            <div class="hover-trigger">
                                <img src="{{ asset('images/billeteraDigital.png') }}" style="width: 35px; height: 30px; border-radius: 35px;">
                            </div>
            
                            <div class="hover-window">
                                <h4>Detalles de Saldo 
                                    <a style="font-size: 18px" title="Actualizar" onclick="location.reload()"><i class="fa-solid fa-arrows-rotate"></i></a>

                                </h4>
                                @if (!$global_cashier['cashier'])
                                    <div class="alert alert-info">
                                        <strong>Información:</strong>
                                        <p>Tiene que tener una caja abierta para ver mas detalles.</p>
                                    </div>
                                @else                        
                                    <table class="table">
                                        <tbody>
                                            <tr>
                                                <th style="text-align: left">
                                                    <small style="font-size: 15px">Saldo Disponible:</small>
                                                </th>
                                                <th style="text-align: right">
                                                    <small style="font-size: 15px">{{ number_format($global_cashier['amountCashier'], 2, ',', '.') }}</small>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th style="text-align: left">
                                                    <small style="font-size: 15px">Ingreso Efectivo:</small>
                                                </th>
                                                <th style="text-align: right">
                                                    <small style="font-size: 15px"><i class="fa-solid fa-dollar-sign"></i> {{ number_format($global_cashier['amountEfectivo'], 2, ',', '.') }}</small>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th style="text-align: left">
                                                    <small style="font-size: 15px">Ingreso Qr:</small>
                                                </th>
                                                <th style="text-align: right">
                                                    <small style="font-size: 15px"><i class="fa-solid fa-qrcode"></i> {{ number_format($global_cashier['amountQr'], 2, ',', '.') }}</small>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th style="text-align: left">
                                                    <small style="font-size: 15px">Egreso Efectivo:</small>
                                                </th>
                                                <th style="text-align: right">
                                                    <small style="font-size: 15px"><i class="fa-solid fa-dollar-sign"></i> {{ number_format($global_cashier['amountEgres'] + $global_cashier['cashierOut'], 2, ',', '.') }}</small>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th style="text-align: left">
                                                    <small style="font-size: 15px">Dinero Asignado:</small>
                                                </th>
                                                <th style="text-align: right">
                                                    <small style="font-size: 15px"><i class="fa-solid fa-cash-register"></i> {{ number_format($global_cashier['cashierIn'], 2, ',', '.') }}</small>
                                                </th>
                                            </tr>
                                        </tbody>
                                    </table>
                                @endif
            
                            </div>

                            
                        </div>
                        <style>
                                    /* Estilos para el contenedor de la ventana emergente */
                            .hover-container {
                                position: fixed;
                                top: 8%;
                                right: 0;
                                transform: translateY(-50%);
                                z-index: 1000;
                            }
            
                            /* Estilos para el botón que activa la ventana */
                            .hover-trigger {
                                background: #2ecc71; /* Verde */
                                color: white;
                                padding: 10px 5px;
                                border-radius: 15px 0 0 15px;
                                cursor: pointer;
                                box-shadow: -2px 0 5px rgba(0, 0, 0, 0.1);
                                transition: background 0.3s, transform 0.3s;
                            }
            
                            .hover-trigger:hover {
                                background: #27ae60; /* Verde más oscuro */
                                transform: translateX(-10px); /* Efecto de desplazamiento */
                            }
            
                            /* Estilos para la ventana emergente */
                            .hover-window {
                                position: absolute;
                                top: 0;
                                right: -350px; /* Oculta la ventana */
                                width: 300px;
                                background: white;
                                padding: 20px;
                                border-radius: 10px 0 0 10px;
                                box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
                                transition: right 0.3s, opacity 0.3s;
                                opacity: 0;
                                visibility: hidden;
                            }
            
                            .hover-container:hover .hover-window {
                                right: 40px; /* Muestra la ventana */
                                opacity: 1;
                                visibility: visible;
                            }
            
                            /* Estilos para el título de la ventana */
                            .hover-window h4 {
                                margin-top: 0;
                                color: #333;
                                font-size: 18px;
                                font-weight: bold;
                                text-align: center;
                                border-bottom: 2px solid #2ecc71;
                                padding-bottom: 10px;
                            }
            
                            /* Estilos para la tabla */
                            .hover-window table {
                                width: 100%;
                                margin-top: 10px;
                            }
            
                            .hover-window th, .hover-window td {
                                padding: 8px;
                                text-align: left;
                            }
            
                            .hover-window th {
                                color: #555;
                                font-weight: 600;
                            }
            
                            .hover-window td {
                                color: #777;
                            }
            
                            /* Estilos para los íconos */
                            .hover-window .fa-solid {
                                margin-right: 5px;
                                color: #2ecc71; /* Verde */
                            }
            
                            /* Efecto de sombra al pasar el mouse sobre las filas de la tabla */
                            .hover-window tbody tr:hover {
                                background-color: #f9f9f9;
                                transition: background-color 0.2s;
                            }
                        </style>
                        @yield('content')
                    </div>
                </div>
            </div>            
        </div>
        @include('voyager::partials.app-footer')

        <!-- Javascript Libs -->
        <script type="text/javascript" src="{{ voyager_asset('js/app.js') }}"></script>

        <script src="{{ asset('js/input-numberBlock.js') }}"></script>

        <script>
            @if(Session::has('alerts'))
                let alerts = {!! json_encode(Session::get('alerts')) !!};
                helpers.displayAlerts(alerts, toastr);
            @endif

            @if(Session::has('message'))

            // TODO: change Controllers to use AlertsMessages trait... then remove this
            var alertType = {!! json_encode(Session::get('alert-type', 'info')) !!};
            var alertMessage = {!! json_encode(Session::get('message')) !!};
            var alerter = toastr[alertType];

            if (alerter) {
                alerter(alertMessage);
            } else {
                toastr.error("toastr alert-type " + alertType + " is unknown");
            }
            @endif
        </script>

        <script src="{{ asset('vendor/momentjs/moment.min.js') }}"></script>
        <script src="{{ asset('vendor/momentjs/moment-with-locales.min.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/howler/2.2.1/howler.min.js"></script>
        <script>       
            $(function() {
                notification();
            });
            function notification()
            {
                let count = 0;
                $.get('{{route('notification.cashierOpen')}}', function (data) {    
                    count = 1;
                    if(data)
                    {
                        var luz = '<span class="badge badge-danger navbar-badge" id="bandeja">'+count+'</span>';
                        $('#countNotification').html(luz)        
                        $('#notificationInbox').append(`
                            <a href="{{url('admin#rowCashierOpen')}}" style="font-size: 16px; color:black"><i class="fa-solid fa-cash-register"></i><small> Tiene una Caja Pendiente Asignada</small></a>
                            <hr>
                        `);
                        setInterval(() => {
                            $('#bellNotification').html('<i class="voyager-bell" style="font-size: 1.8em; color : #ff0808"></i>');
                            setTimeout(function(){
                                $('#bellNotification').html('<i class="fa-solid fa-bell" style="font-size: 1.8em; color : #22a7f0"></i>')  ;
                            }, 500)
                        }, 1000);
                    }
                    else
                    {
                        $('#countNotification').html('')
                        $('#notificationInbox').html('')
                    }       
                })
            }
        </script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.4.0/socket.io.js" integrity="sha512-nYuHvSAhY5lFZ4ixSViOwsEKFvlxHMU2NHts1ILuJgOS6ptUmAGt/0i5czIgMOahKZ6JN84YFDA+mCdky7dD8A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script>
            const socket = io("{{ env('SOCKET_URL').':'.env('SOCKET_PORT') }}");
            socket.on(`change notificationCashierOpen`, data => {
                let auth =  @json(Auth::user());
                if(auth.id == data.auth.id)
                {
                    notification()
                    toastr.info('<a href="{{url('admin#rowCashierOpen')}}" style="font-size: 15px; color:black">Hola '+data.auth.name+', Tiene una Caja Pendiente Asignada</a>', 'Notificación',
                        {   "positionClass" : "toast-bottom-right",
                            "timeOut": "10000",
                            "closeButton": true,
                            "progressBar": true,
                        }
                    );
                }
            });
        </script>
        @include('voyager::media.manager')

        @yield('javascript')
        @stack('javascript')
        @if(!empty(config('voyager.additional_js')))
            @foreach(config('voyager.additional_js') as $js)<script type="text/javascript" src="{{ asset($js) }}"></script>@endforeach
        @endif

        {{-- Loading --}}
        <script src="{{ asset('vendor/loading/loading.js') }}"></script>
        <link rel="stylesheet" href="{{ asset('vendor/loading/loading.css') }}">

        <script>
            $(document).ready(function() {
                $('[data-toggle="tooltip"]').tooltip();
                $('[data-toggle="popover"]').popover();
                $('.form-submit').submit(function(){
                    $('.form-submit .btn-submit').attr('disabled', 'disabled');
                });

                // // Actualizar estado de cuotas atrasadas
                // $.get("{{ url('loans/dayLate') }}", function (data) {
                // });
     
            });
        </script>

        {{-- Para GPS para ingresar al sistema tiene que estar activo al gps caso contrario no podra acceder --}}
        <script>
            // document.addEventListener('DOMContentLoaded', function () {
            //     if ("geolocation" in navigator) {
            //         navigator.geolocation.getCurrentPosition(
            //             function (position) {
            //                 console.log("Ubicación permitida");
            //                 // Aquí podrías habilitar el botón de login
            //             },
            //             function (error) {
            //                 alert("Debes activar tu ubicación GPS para ingresar al sistema.");
            //                 window.location.href = "/acceso-denegado"; // o cerrar la sesión
            //             }
            //         );
            //     } else {
            //         alert("Tu navegador no soporta geolocalización.");
            //         window.location.href = "/acceso-denegado";
            //     }
            // });

            let gpsWatcher = null;
            
            function iniciarVerificacionContinua() {
                if ("geolocation" in navigator) {
                    gpsWatcher = navigator.geolocation.watchPosition(
                        function (position) {
                            // OK, el GPS sigue activo
                            // toastr.success('GPS activo', 'GPS');                            
                        },
                        function (error) {
                            if ('{{!auth()->user()->hasRole("admin") && !auth()->user()->hasRole("gerente") && !auth()->user()->hasRole("administrador")}}') {
                                alert("Se ha desactivado el GPS. Serás desconectado del sistema.");
                                window.location.href = "/gpsBlockAccess";
                            }
                            
                        }
                    );
                }
            }
            
            document.addEventListener('DOMContentLoaded', function () {
                iniciarVerificacionContinua();
            });



            // setInterval(function () {
            //     navigator.geolocation.getCurrentPosition(function (position) {
            //         fetch('/api/verificar-ubicacion', {
            //             method: 'POST',
            //             headers: {
            //                 'Content-Type': 'application/json',
            //                 'X-CSRF-TOKEN': '{{ csrf_token() }}',
            //             },
            //             body: JSON.stringify({
            //                 lat: position.coords.latitude,
            //                 lng: position.coords.longitude
            //             })
            //         });
            //     });
            // }, 60000); // cada minuto

        </script>
    
    
    </body>
</html>