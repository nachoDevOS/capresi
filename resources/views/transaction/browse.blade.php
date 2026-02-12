@extends('voyager::master')

@section('page_title', 'Lista de transacciones')

{{-- @if (auth()->user()->hasPermission('add_contracts') || auth()->user()->hasPermission('edit_contracts')) --}}

@section('page_header')
    <h1 id="titleHead" class="page-title">
        <i class="fa-solid fa-money-bill-transfer"></i> Transacciones
    </h1>
    <a href="{{ route('loans.index') }}" class="btn btn-warning">
        <i class="fa-solid fa-rotate-left"></i> <span>Volver</span>
    </a>
@stop

@section('content')
    <div class="page-content edit-add container-fluid">

        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table id="dataStyle" class="table-hover">
                                <thead>
                                    <tr>
                                        <th style="text-align: center; width:12%">Transacción</th>
                                        <th style="text-align: center">Monto</th>
                                        <th style="text-align: center">Fecha</th>
                                        <th style="text-align: center">Atendido Por</th>
                                        <th style="text-align: right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data as $item)
                                        <tr>
                                            <td style="text-align: center">
                                                @php
                                                    $image = asset('images/icono-anonimato.png');
                                                    if ($item->urlRegister) {
                                                        $image = asset('storage/' . $item->urlRegister);
                                                    }
                                                @endphp

                                                Transaccion: {{ $item->transaction }} <br>

                                                @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('gerente') || auth()->user()->hasRole('administrador'))
                                                    <img src="{{ $image }}" alt=""
                                                        style="width: 60px; height: 60px; border-radius: 30px; margin-right: 10px">
                                                    <br>
                                                    Latitude: {{ $item->latitude }} <br>
                                                    longitude: {{ $item->longitude }} <br>
                                                    {{ $item->DescriptionPrecision }}
                                                @endif
                                            </td>
                                            <td style="text-align: center">
                                                @if ($item->deleted_at)
                                                    <del>BS. {{ $item->amount }} <br></del>
                                                    <label class="label label-danger">Anulado por
                                                        {{ $item->eliminado }}</label>
                                                @else
                                                    BS. {{ $item->amount }}
                                                @endif
                                            </td>
                                            <td style="text-align: center">
                                                {{ date('d/m/Y H:i:s', strtotime($item->created_at)) }}<br><small>{{ \Carbon\Carbon::parse($item->created_at)->diffForHumans() }}
                                            </td>
                                            <td style="text-align: center">{{ $item->agentType }} <br> {{ $item->name }}
                                            </td>
                                            <td class="no-sort no-click bread-actions text-right">
                                                <a href="#" class="btn btn-sm btn-primary view-location"
                                                    data-lat="{{ $item->latitude }}" data-lng="{{ $item->longitude }}">
                                                    <i class="fa-solid fa-location-dot"></i>
                                                </a>
                                                @if (!$item->deleted_at)
                                                    <a href="{{ route('loans.payment.notification', $item->transaction_id) }}"
                                                        data-phone="{{ $item->people_phone }}"
                                                        class="btn btn-success btn-notification" title="Reenviar recibo">
                                                        <i class="fa fa-paper-plane"></i>
                                                    </a>
                                                    {{-- <a onclick="printDailyMoney({{$item->loan}}, {{$item->transaction_id}})" title="Imprimir"  class="btn btn-danger">
                                                                <i class="glyphicon glyphicon-print"></i>
                                                            </a> --}}
                                                    @php
                                                    $data = App\Models\Loan::with([
                                                                'people',
                                                                'loanDay' => function ($query) use ($item) {
                                                                    $query
                                                                        ->whereHas('loanDayAgents', function ($q) use (
                                                                            $item,
                                                                        ) {
                                                                            $q->where('transaction_id', $item->transaction_id);
                                                                        })
                                                                        ->with([
                                                                            'loanDayAgents' => function ($q) use (
                                                                                $item,
                                                                            ) {
                                                                                $q->where(
                                                                                    'transaction_id',
                                                                                    $item->transaction_id,
                                                                                )->with(['transaction', 'agent']);
                                                                            },
                                                                        ]);
                                                                },
                                                            ])
                                                            ->where('id', $item->loan)
                                                            ->first();
                                                    @endphp
                                                    <a onclick="handlePrintClick(this, '{{ setting('servidores.print') }}',{{ json_encode($data) }}, '{{ url('admin/loans/daily/money/print') }}')"
                                                        title="Imprimir" class="btn btn-danger">
                                                        <i class="glyphicon glyphicon-print"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5">No hay datos registrados</td>
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

    @include('partials.modal-mapsView')



@stop

@section('css')
    <style>
        #map {
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .view-location {
            margin-top: 5px;
        }

        .location-info {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
@endsection

{{-- @section('javascript')
        <script src="{{ url('js/main.js') }}"></script>
        <script>
            $(document).ready(function(){
                $('#dataStyle').DataTable({
                    language,
                    order: [[ 0, 'desc' ]],
                })
            });

            function printDailyMoney(loan_id, transaction_id)
            {
                // alert(loan_id);
                window.open("{{ url('admin/loans/daily/money/print') }}/"+loan_id+"/"+transaction_id, "Recibo", `width=700, height=700`)
            }
        </script>
    @stop --}}

@section('javascript')
    <script src="{{ url('js/main.js') }}"></script>
    <script src="{{ asset('js/print.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Inicializar el mapa cuando se hace clic en el botón
            $('.view-location').click(function(e) {
                e.preventDefault();
                var lat = $(this).data('lat');
                var lng = $(this).data('lng');

                if (!lat || !lng) {
                    toastr.warning('No hay coordenadas disponibles para esta transacción');
                    return;
                }

                $('#mapModal').modal('show');

                // Esperar a que el modal se muestre completamente
                $('#mapModal').on('shown.bs.modal', function() {
                    initMap(lat, lng);
                });
            });

            // Función para inicializar el mapa
            function initMap(lat, lng) {
                // Si ya existe un mapa, lo eliminamos
                if (window.mapInstance) {
                    window.mapInstance = null;
                    $('#map').empty();
                }

                var mapOptions = {
                    center: {
                        lat: parseFloat(lat),
                        lng: parseFloat(lng)
                    },
                    zoom: 15
                };

                window.mapInstance = new google.maps.Map(document.getElementById('map'), mapOptions);

                new google.maps.Marker({
                    position: {
                        lat: parseFloat(lat),
                        lng: parseFloat(lng)
                    },
                    map: window.mapInstance,
                    title: 'Ubicación de la transacción'
                });
            }

            // Cargar la API de Google Maps
            function loadGoogleMaps() {
                if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
                    var script = document.createElement('script');
                    script.src =
                        'https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initMap';
                    script.defer = true;
                    script.async = true;
                    document.head.appendChild(script);
                }
            }

            // Cargar la API cuando la página esté lista
            loadGoogleMaps();
        });

        // function printDailyMoney(loan_id, transaction_id) {
        //     window.open("{{ url('admin/loans/daily/money/print') }}/"+loan_id+"/"+transaction_id, 
        //     "Recibo", `width=700, height=700`);
        // }

        async function handlePrintClick(element, printUrl, data, fallbackUrl) {
            const button = $(element);
            const icon = button.find('i');
            const originalIconClass = icon.attr('class');

            // Evitar múltiples clics si ya se está procesando
            if (button.hasClass('disabled')) {
                return;
            }

            // Cambiar ícono a spinner y desactivar botón
            button.addClass('disabled');
            icon.removeClass(originalIconClass).addClass('fa-solid fa-spinner fa-spin');

            try {
                await printTicket(printUrl, data, fallbackUrl, 'LoanPayment');
            } finally {
                // Restaurar el botón después de un par de segundos para que el usuario vea el resultado (toastr)
                setTimeout(() => {
                    button.removeClass('disabled');
                    icon.removeClass('fa-solid fa-spinner fa-spin').addClass(originalIconClass);
                }, 2000);
            }
        }


        $(document).ready(function() {
            $('#dataStyle').DataTable({
                language,
                ordering: false, // Desactiva el ordenamiento de DataTables
                order: [] // Elimina cualquier orden predeterminado
            });

            // Resto de tu código...
        });
    </script>
@stop
