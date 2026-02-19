@extends('voyager::master')

@section('page_title', 'Abonar Pago')

@section('page_header')
    <h1 id="titleHead" class="page-title">
        <i class="voyager-dollar"></i> Abonar Pago
    </h1>
    <a href="{{ route('loans.index') }}" class="btn btn-warning btn-return">
        <i class="fa-solid fa-rotate-left"></i> <span>Volver</span>
    </a>
@stop

@section('content')
    <div class="page-content edit-add container-fluid">
        <div class="row">
            <div class="col-md-12">
                
                <!-- Información del Cliente y Préstamo -->
                <div class="panel panel-bordered" style="border-left: 5px solid #22A7F0;">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h3 style="margin-top: 0; font-weight: bold;">
                                    <i class="fa-solid fa-user-circle"></i> {{ $loan->people->first_name }} {{ $loan->people->last_name1 }} {{ $loan->people->last_name2 }}
                                    <small class="text-muted">({{ $loan->code }})</small>
                                </h3>
                                <div class="row" style="margin-top: 15px;">
                                    <div class="col-md-4 col-sm-6">
                                        <p class="text-muted" style="margin-bottom: 2px;"><i class="fa-solid fa-id-card"></i> CI</p>
                                        <strong>{{ $loan->people->ci }}</strong>
                                    </div>
                                    <div class="col-md-4 col-sm-6">
                                        <p class="text-muted" style="margin-bottom: 2px;"><i class="fa-solid fa-mobile-screen"></i> Celular</p>
                                        <strong>{{ $loan->people->cell_phone ? $loan->people->cell_phone : 'SN' }}</strong>
                                    </div>
                                    <div class="col-md-4 col-sm-6">
                                        <p class="text-muted" style="margin-bottom: 2px;"><i class="fa-solid fa-route"></i> Ruta</p>
                                        <strong>{{ $route->route->name }}</strong>
                                    </div>
                                    <div class="col-md-4 col-sm-6" style="margin-top: 10px;">
                                        <p class="text-muted" style="margin-bottom: 2px;"><i class="fa-solid fa-calendar"></i> Fecha Solicitud</p>
                                        <strong>{{ date('d/m/Y', strtotime($loan->date)) }}</strong>
                                    </div>
                                    <div class="col-md-4 col-sm-6" style="margin-top: 10px;">
                                        <p class="text-muted" style="margin-bottom: 2px;"><i class="fa-solid fa-user-shield"></i> Garante</p>
                                        <strong>{{ $loan->guarantor_id ? $loan->guarantor->first_name . ' ' . $loan->guarantor->last_name1 : 'SN' }}</strong>
                                    </div>
                                    <div class="col-md-4 col-sm-6" style="margin-top: 10px;">
                                        @if ($loan->recovery == 'si')
                                            <span class="label label-danger" style="font-size: 100%">EN RECUPERACIÓN</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="well well-sm" style="background-color: #f9f9f9; border: 1px solid #e5e5e5; border-radius: 5px;">
                                    <div class="row text-center">
                                        <div class="col-xs-6" style="border-right: 1px solid #ddd;">
                                            <small class="text-muted">DEUDA ACTUAL</small>
                                            <h3 class="text-danger" style="margin-top: 5px;">Bs. {{ number_format($loan->debt, 2, ',', '.') }}</h3>
                                        </div>
                                        <div class="col-xs-6">
                                            <small class="text-muted">PAGO DIARIO</small>
                                            <h3 class="text-primary" style="margin-top: 5px;">Bs. {{ number_format($loan->amountTotal / $loan->day, 2, ',', '.') }}</h3>
                                        </div>
                                    </div>
                                    <div style="margin-top: 10px;">
                                        @php
                                            $percent = ($loan->amountTotal > 0) ? (($loan->amountTotal - $loan->debt) / $loan->amountTotal) * 100 : 0;
                                        @endphp
                                        <div class="progress" style="height: 8px; margin-bottom: 5px;">
                                            <div class="progress-bar progress-bar-success" role="progressbar" style="width: {{ $percent }}%;" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <div class="text-center">
                                            <small class="text-muted">Progreso: {{ number_format($percent, 0) }}%</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Columna Izquierda: Calendario -->
                    <div class="col-md-8">
                        <div class="panel panel-bordered">
                            <div class="panel-heading">
                                <h3 class="panel-title"><i class="fa-solid fa-calendar-days"></i> Calendario de Pagos</h3>
                                <div class="panel-actions">
                                    <button type="button" class="btn btn-default btn-sm" title="Imprimir calendario" onclick="javascript:imprim1(imp1);">
                                        <i class="fa fa-print"></i> Imprimir
                                    </button>
                                </div>
                            </div>
                            <div class="panel-body" id="imp1">
                                @php
                                    $meses = [1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                                    
                                    $inicio = \Carbon\Carbon::parse($loan->loanDay->first()->date)->format('Y-m-d');
                                    $fin = \Carbon\Carbon::parse($loan->loanDay->last()->date)->format('Y-m-d');

                                    $loanDaysByDate = $loanday->keyBy(function($item) {
                                        return \Carbon\Carbon::parse($item->date)->format('Y-m-d');
                                    });
                                    
                                    $today = \Carbon\Carbon::now()->format('Y-m-d');
                                    
                                    $cantMeses = count($cantMes);
                                    $mes = 0;
                                @endphp

                                <div class="payment-calendar">
                                    @while ($mes < $cantMeses)
                                        <table class="calendar-table">
                                            <thead>
                                                <tr class="calendar-header">
                                                    <th colspan="7">
                                                        {{ $meses[intval($cantMes[$mes]->mes)] }} - {{ intval($cantMes[$mes]->ano) }}
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th>LUN</th>
                                                    <th>MAR</th>
                                                    <th>MIE</th>
                                                    <th>JUE</th>
                                                    <th>VIE</th>
                                                    <th>SAB</th>
                                                    <th>DOM</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $currentMonth = \Carbon\Carbon::create(intval($cantMes[$mes]->ano), intval($cantMes[$mes]->mes), 1);
                                                    $posicionPrimerFecha = $currentMonth->dayOfWeekIso; // 1 = Lunes, 7 = Domingo
                                                    $ultimoDia = $currentMonth->endOfMonth()->day;
                                                    $dia = 1;
                                                @endphp

                                                @for ($semana = 0; $semana < 6; $semana++)
                                                    @if ($dia > $ultimoDia)
                                                        @break
                                                    @endif
                                                    <tr>
                                                        @for ($diaSemana = 1; $diaSemana <= 7; $diaSemana++)
                                                            @if ($semana === 0 && $diaSemana < $posicionPrimerFecha)
                                                                <td class="day-cell empty"></td>
                                                            @elseif ($dia > $ultimoDia)
                                                                <td class="day-cell empty"></td>
                                                            @else
                                                                @php
                                                                    $currentDate = \Carbon\Carbon::create(intval($cantMes[$mes]->ano), intval($cantMes[$mes]->mes), $dia);
                                                                    $currentDateStr = $currentDate->format('Y-m-d');

                                                                    $classes = ['day-cell'];
                                                                    if ($diaSemana == 7) $classes[] = 'sunday';
                                                                    if ($currentDateStr == $today) $classes[] = 'today';

                                                                    $loanDayData = $loanDaysByDate->get($currentDateStr);
                                                                @endphp

                                                                <td class="{{ implode(' ', $classes) }}">
                                                                    <div class="day-number">{{ $dia }}</div>
                                                                    <div class="day-content">
                                                                        @if ($loanDayData)
                                                                            @if ($currentDateStr == $inicio)
                                                                                <span class="status-pill start-end">Inicio</span>
                                                                            @endif
                                                                            @if ($currentDateStr == $fin)
                                                                                <span class="status-pill start-end">Fin</span>
                                                                            @endif

                                                                            @if ($loanDayData->debt == 0)
                                                                                <span class="status-pill paid">Pagado</span>
                                                                            @elseif ($loanDayData->debt < $loanDayData->amount && $loanDayData->debt > 0)
                                                                                <span class="status-pill partial">Abono: {{ number_format($loanDayData->amount - $loanDayData->debt, 2) }}</span>
                                                                            @endif

                                                                            @if ($loanDayData->late == 1 && $loanDayData->debt > 0)
                                                                                <span class="status-pill late">Atraso</span>
                                                                            @endif
                                                                        @endif
                                                                    </div>
                                                                </td>
                                                                @php $dia++; @endphp
                                                            @endif
                                                        @endfor
                                                    </tr>
                                                @endfor
                                            </tbody>
                                        </table>
                                        @php $mes++; @endphp
                                        @if ($mes < $cantMeses)
                                            <br>
                                        @endif
                                    @endwhile
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna Derecha: Formulario y Detalles -->
                    <div class="col-md-4">
                        
                        <!-- Formulario de Pago -->
                        @if (auth()->user()->hasPermission('addMoneyDaily_loans') && $loan->debt != 0)
                        <div class="panel panel-success" style="border: 1px solid #2ecc71;">
                            <div class="panel-heading" style="background-color: #e2e2e2; color: white;">
                                <h3 class="panel-title"><i class="fa-solid fa-money-bill-wave"></i> Registrar Pago</h3>
                            </div>
                            <div class="panel-body">
                                    <form id="form-abonar-pago" action="{{ route('loans-daily-money.store') }}"
                                        method="POST">
                                        @csrf
                                            <input type="hidden" name="date" value="{{ $date }}">
                                            <input type="hidden" name="loan_id" value="{{ $loan->id }}">


                                            <div class="form-group">
                                                <label for="amount">Monto a Pagar</label>
                                                <div class="input-group">
                                                    <span class="input-group-addon">Bs.</span>
                                                    <input type="number" name="amount" id="amount" min="0.01" step=".01"
                                                        style="text-align: right; font-size: 20px; font-weight: bold;"
                                                        class="form-control" required placeholder="0.00">
                                                </div>
                                                <small class="text-danger" id="label-amount" style="display:none">El monto es incorrecto o excede la deuda.</small>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Agente</label>
                                                <select name="agent_id" id="agent_id" class="form-control select2" required>
                                                    <option value="{{ $register->id }}" selected>{{ $register->name }} -
                                                        {{ $register->role->name }}</option>
                                                </select>
                                            </div>
                                            
                                            <div class="form-group text-center">
                                                <div class="btn-group" data-toggle="buttons" style="width: 100%;">
                                                    <label class="btn btn-default active" style="width: 50%;">
                                                        <input type="radio" name="qr" value="Efectivo" checked autocomplete="off"> <i class="fa-solid fa-money-bill"></i> Efectivo
                                                    </label>
                                                    <label class="btn btn-default" style="width: 50%;">
                                                        <input type="radio" name="qr" value="Qr" autocomplete="off"> <i class="fa-solid fa-qrcode"></i> QR
                                                    </label>
                                                </div>
                                            </div>

                                        <input type="hidden" name="latitude" id="latitudeField">
                                        <input type="hidden" name="longitude" id="longitudeField">
                                        <input type="hidden" name="precision" id="precision">

                                        <button type="button" id="btn-sumit" disabled class="btn btn-success btn-lg btn-block btn-sumit">
                                            <i class="fa-solid fa-check-circle"></i> Pagar
                                        </button>
                                    </form>
                            </div>
                        </div>
                        @endif

                        <!-- Resumen Financiero -->
                        <div class="panel panel-bordered">
                            <div class="panel-heading">
                                <h3 class="panel-title">Resumen Financiero</h3>
                            </div>
                            <div class="panel-body">
                                <ul class="list-group">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Monto Prestado
                                        <span class="badge">Bs. {{ number_format($loan->amountLoan, 2) }}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Interés
                                        <span class="badge">Bs. {{ number_format($loan->amountPorcentage, 2) }}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: #f5f5f5;">
                                        <strong>Total a Pagar</strong>
                                        <span class="badge badge-primary" style="font-size: 1.1em;">Bs. {{ number_format($loan->amountTotal, 2) }}</span>
                                    </li>
                                </ul>

                                <div class="row text-center" style="margin-top: 20px;">
                                    <div class="col-xs-6" style="border-right: 1px solid #eee;">
                                        <small class="text-muted">PAGADO</small>
                                        <h4 class="text-success">{{ number_format($loan->amountTotal - $loan->debt, 2, ',', '.') }} <small>Bs.</small></h4>
                                    </div>
                                    <div class="col-xs-6">
                                        <small class="text-muted">DEUDA</small>
                                        <h4 class="text-danger">{{ number_format($loan->debt, 2, ',', '.') }} <small>Bs.</small></h4>
                                    </div>
                                </div>

                                <hr>
                                
                                <div class="text-center">
                                    <h5 class="text-warning" style="font-weight: bold;">ATRASO</h5>
                                    @php
                                        $dias_deuda = '';
                                        foreach ($loanday->where('debt', '>', 0)->where('late', 1) as $dia_deuda) {
                                            $dias_deuda .= date('d/m/Y', strtotime($dia_deuda->date)).', ';
                                        }
                                    @endphp
                                    <h4 style="cursor: pointer" @if($dias_deuda) title="{{ $dias_deuda }}" @endif>
                                        {{ $loanday->where('debt', '>', 0)->where('late', 1)->count() }} Días
                                        <br>
                                        <small>Bs. {{ number_format($loanday->where('debt', '>', 0)->where('late', 1)->sum('debt'), 2, ',', '.') }}</small>
                                    </h4>
                                </div>

                                <div style="margin-top: 20px;">
                                    <canvas id="myChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="panel panel-bordered">
                            <div class="panel-heading">
                                <h3 class="panel-title">Observaciones</h3>
                            </div>
                            <div class="panel-body">
                                <textarea name="observation" id="observation" disabled class="form-control text" cols="30" rows="3">{{ $loan->observation }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal de confirmación de pago -->
    <div class="modal fade" id="confirmPaymentModal" tabindex="-1" role="dialog" aria-labelledby="confirmPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background: #22A7F0; color: #fff; border-radius: 5px 5px 0 0;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #fff; opacity: 1;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title text-center" id="confirmPaymentModalLabel" style="font-weight: bold;">
                        <i class="fa-solid fa-check-to-slot"></i> Confirmar Transacción
                    </h4>
                </div>
                <div class="modal-body" style="padding: 25px;">
                    <!-- Step 1: Confirmation Details -->
                    <div id="confirmation-step">
                        <div class="text-center" style="margin-bottom: 20px;">
                            <div style="width: 80px; height: 80px; background: #f0f4f8; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                                <i class="fa-solid fa-user" style="font-size: 35px; color: #22A7F0;"></i>
                            </div>
                            <h4 style="font-weight: bold; margin-bottom: 5px;">{{ $loan->people->first_name }} {{ $loan->people->last_name1 }}</h4>
                            <p class="text-muted"><i class="fa-solid fa-id-card"></i> {{ $loan->people->ci }}</p>
                        </div>

                        <div class="payment-summary" style="background: #f8f9fa; border: 1px dashed #cbd5e0; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                            <div class="row" style="margin-bottom: 10px;">
                                <div class="col-xs-6 text-left">
                                    <span style="color: #6c757d; font-size: 14px;">Monto a Pagar</span>
                                </div>
                                <div class="col-xs-6 text-right">
                                    <span id="modal-payment-amount" style="font-size: 18px; font-weight: bold; color: #2ecc71;">Bs. 0.00</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-6 text-left">
                                    <span style="color: #6c757d; font-size: 14px;">Método de Pago</span>
                                </div>
                                <div class="col-xs-6 text-right">
                                    <span id="modal-payment-method" style="font-size: 16px; font-weight: 600; color: #4a5568;">Efectivo</span>
                                </div>
                            </div>
                        </div>

                        <div class="text-center">
                            <p class="text-muted" style="font-size: 13px;">
                                <i class="fa-solid fa-circle-info"></i> Por favor revisa los datos antes de confirmar.
                            </p>
                        </div>
                    </div>

                    <!-- Step 2: Loading Indicator -->
                    <div id="loading-step" style="display: none; text-align: center; padding: 20px 0;">
                        <div class="spinner-border" style="display: inline-block; width: 3rem; height: 3rem; vertical-align: text-bottom; border: .25em solid currentColor; border-right-color: transparent; border-radius: 50%; animation: spinner-border .75s linear infinite; color: #22A7F0;"></div>
                        <h4 style="margin-top: 20px; font-weight: 600; color: #4a5568;">Procesando pago...</h4>
                        <p class="text-muted">No cierres esta ventana.</p>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: none; text-align: center; padding-bottom: 25px;">
                    <button type="button" id="btn-cancel-payment" class="btn btn-default btn-lg" data-dismiss="modal" style="margin-right: 10px; font-size: 14px;">Cancelar</button>
                    <button type="button" id="btn-confirm-payment" class="btn btn-success btn-lg" style="background: #22A7F0; border-color: #22A7F0; font-size: 14px; padding-left: 30px; padding-right: 30px;">
                        Confirmar Pago
                    </button>
                </div>
            </div>
        </div>
    </div>


    @if (session('data'))
        <div id="popup-button">
            <div class="col-md-12" style="padding-top: 5px">
                <h4 class="text-muted">Desea imprimir el comprobante?</h4>
            </div>
            <div class="col-md-12 text-right">
                <button onclick="javascript:$('#popup-button').fadeOut('fast')" class="btn btn-default">Cerrar</button>
                <a id="btn-print" onclick="printTicket('{{ setting('servidores.print') }}',{{ json_encode(session('data')) }}, '{{ url('admin/loans/daily/money/print') }}', 'LoanPayment')" title="Imprimir" class="btn btn-danger">Imprimir <i
                        class="glyphicon glyphicon-print"></i></a>
                {{-- <button type="submit" id="btn-print" title="Imprimir" class="btn btn-danger" onclick="printDailyMoney()" class="btn btn-primary">Imprimir <i class="glyphicon glyphicon-print"></i></button> --}}

            </div>
        </div>
    @endif
@stop

@section('css')
    <style>
        /* Calendar Styles */
        .payment-calendar {
            /* border: 1px solid #ddd; */
            border-radius: 8px;
            overflow: hidden;
        }
        .calendar-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            border: 1px solid #ddd;
        }
        .calendar-table th {
            background-color: #f8f9fa;
            color: #666;
            font-weight: 500;
            padding: 10px 5px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        .calendar-table td {
            height: 100px;
            text-align: left;
            vertical-align: top;
            border: 1px solid #eee;
            padding: 4px;
            position: relative;
            transition: background-color 0.2s;
        }
        .calendar-table td:hover { background-color: #f5f5f5; }
        .day-number { font-size: 14px; font-weight: 500; color: #333; text-align: right; }
        .day-cell.empty { background-color: #fdfdfd; cursor: default; }
        .day-cell.empty:hover { background-color: #fdfdfd; }
        .day-cell.sunday .day-number { color: #e74c3c; }
        .day-cell.today .day-number { background-color: #337ab7; color: white; border-radius: 50%; width: 28px; height: 28px; line-height: 28px; text-align: center; display: inline-block; float: right; }
        .day-content { margin-top: 5px; display: flex; flex-direction: column; gap: 4px; }
        .status-pill { padding: 3px 6px; border-radius: 4px; font-size: 11px; font-weight: bold; color: white; text-align: center; }
        .status-pill.paid { background-color: #2ecc71; }
        .status-pill.partial { background-color: #f39c12; }
        .status-pill.late { background-color: #e74c3c; }
        .status-pill.start-end { background-color: #3498db; }
        .calendar-header { background-color: #337ab7; color: white; font-size: 1.5em; padding: 10px; text-align: center; }
        .calendar-header th { color: white; }

        @keyframes spinner-border {
            to { transform: rotate(360deg); }
        }

        /* ___________________________ */
        .form-group {
            margin-bottom: 10px !important;
        }

        .label-description {
            cursor: pointer;
        }

        #popup-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 400px;
            height: 100px;
            background-color: white;
            box-shadow: 5px 5px 15px grey;
            z-index: 1000;

            /* Mostrar/ocultar popup */
            /* @if (session('data'))
            */ animation: show-animation 1s;
            /* @else
            */ right: -500px;
            /* @endif
            */
        }

        @keyframes show-animation {
            0% {
                right: -500px;
            }

            100% {
                right: 20px;
            }
        }

        /* Estilos para selección de método de pago */
        .btn-group[data-toggle="buttons"] > .btn.active {
            background-color: #337ab7; /* Color primario de Bootstrap */
            color: white;
            border-color: #2e6da4;
            box-shadow: inset 0 3px 5px rgba(0, 0, 0, .125);
        }
        .btn-group[data-toggle="buttons"] > .btn.active .fa-solid {
            color: white;
        }
    </style>
@endsection

@section('javascript')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.1.4/Chart.bundle.min.js"></script>
    <script src="{{ asset('js/print.js') }}"></script>


    <script>
        // Funciones de Geolocalización (mantenerlas accesibles globalmente si es necesario)
        function obtenerUbicacionForzada() {
            if (!navigator.geolocation) {
                mostrarError("Tu navegador no soporta geolocalización");
                return;
            }
            const opcionesGPS = {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0
            };
            navigator.geolocation.getCurrentPosition(
                function(posicion) {
                    const campoPre = $('#precision');
                    if (posicion.coords.accuracy > 100) {
                        mostrarAdvertencia(`Precisión baja (${Math.round(posicion.coords.accuracy)}m).`);
                        campoPre.val(`Precisión baja (${Math.round(posicion.coords.accuracy)}m).`);
                    } else {
                        mostrarExito("¡Ubicación obtenida correctamente!");
                        campoPre.val(`Precisión: ${Math.round(posicion.coords.accuracy)}m`);
                    }
                    $('#latitudeField').val(posicion.coords.latitude.toFixed(6));
                    $('#longitudeField').val(posicion.coords.longitude.toFixed(6));
                },
                function(error) {
                    manejarErrorGPS(error);
                },
                opcionesGPS
            );
        }

        function manejarErrorGPS(error) {
            const errores = {
                1: "Permiso denegado. Debes activar la ubicación en los ajustes de tu dispositivo.",
                2: "No se puede obtener la ubicación. Verifica que el GPS esté activado.",
                3: "Tiempo de espera agotado. El GPS está respondiendo lentamente."
            };
            mostrarError(errores[error.code] || "Error desconocido al obtener la ubicación");
        }

        function mostrarExito(mensaje) {
            toastr.success(mensaje, 'GPS');
        }

        function mostrarAdvertencia(mensaje) {
            toastr.warning(mensaje, 'Advertencia GPS');
        }

        function mostrarError(mensaje) {
            toastr.error(mensaje, 'Error GPS');
            $('#precision').val(mensaje);
        }
        
        function agregarBotonReintento() {
            const boton = $('<button type="button" class="btn btn-info btn-sm btn-block"><i class="voyager-refresh"></i> Reintentar Ubicación</button>');
            boton.on('click', obtenerUbicacionForzada);
            // Insertar después del grupo de botones de tipo de pago
            $('.form-group.text-center').after($('<div class="form-group"></div>').append(boton));
        }

        // Función de impresión global
        function imprim1(imp1) {
             var printContents = document.getElementById('imp1').innerHTML;
             var w = window.open('', 'Imprimir Calendario', 'height=600,width=800');
             w.document.write('<html><head><title>Calendario de Pagos</title>');

             // Inject all necessary styles for a good print output
             var styles = `
                body { font-family: sans-serif; }
                .payment-calendar { border: 1px solid #ddd; border-radius: 8px; overflow: hidden; }
                .calendar-table { width: 100%; border-collapse: collapse; font-size: 10px; page-break-inside: auto; }
                .calendar-table th, .calendar-table .calendar-header th { background-color: #f1f1f1 !important; color: #333 !important; font-weight: 500; padding: 8px 5px; text-align: center; border: 1px solid #ccc; -webkit-print-color-adjust: exact; }
                .calendar-table td { height: 80px; text-align: left; vertical-align: top; border: 1px solid #ccc; padding: 4px; }
                .day-number { font-size: 12px; font-weight: 500; color: #333; text-align: right; }
                .day-cell.empty { background-color: #f9f9f9 !important; -webkit-print-color-adjust: exact; }
                .day-cell.sunday .day-number { color: #e74c3c !important; }
                .day-cell.today .day-number { background-color: #337ab7 !important; color: white !important; border-radius: 50%; width: 24px; height: 24px; line-height: 24px; text-align: center; display: inline-block; float: right; -webkit-print-color-adjust: exact; }
                .day-content { margin-top: 5px; display: flex; flex-direction: column; gap: 3px; }
                .status-pill { padding: 2px 5px; border-radius: 4px; font-size: 9px; font-weight: bold; color: white !important; text-align: center; -webkit-print-color-adjust: exact; }
                .status-pill.paid { background-color: #2ecc71 !important; }
                .status-pill.partial { background-color: #f39c12 !important; }
                .status-pill.late { background-color: #e74c3c !important; }
                .status-pill.start-end { background-color: #3498db !important; }
                .calendar-header { background-color: #337ab7 !important; color: white !important; font-size: 1.4em; padding: 10px; text-align: center; -webkit-print-color-adjust: exact; }
                .calendar-header th { color: white !important; }
            `;

             w.document.write('<style>' + styles + '</style>');
             w.document.write('</head><body>' + printContents + '</body></html>');
             w.document.close();
             w.focus();
             w.print();
             return true;
        }


        $(document).ready(function() {
            // =================================================================
            // Lógica de Gráfico y UI inicial
            // =================================================================
            
            $("#amount").on('paste', function(e) {
                e.preventDefault();
                toastr.warning('No se permite pegar en este campo.', 'Advertencia');
            });

            const debtData = {
                labels: ['Deuda Pendiente', 'Total Pagado'],
                datasets: [{
                    data: ["{{ $loan->debt }}", "{{ $loan->amountTotal - $loan->debt }}"],
                    backgroundColor: ['#e74c3c', '#2ecc71'],
                    hoverOffset: 4
                }]
            };
            new Chart($('#myChart'), {
                type: 'pie',
                data: debtData,
                options: {
                    legend: { position: 'bottom' }
                }
            });

            setTimeout(() => {
                $('#popup-button').fadeOut('fast');
            }, 8000);

            // =================================================================
            // Lógica de Validación y Pago
            // =================================================================
            const debt = parseFloat('{{ $loan->debt }}');
            const $amountInput = $('#amount');
            const $submitBtn = $('#btn-sumit');
            const $amountLabel = $('#label-amount');
            const $paymentModal = $('#confirmPaymentModal');

            function validateAmount() {
                let amount = parseFloat($amountInput.val());
                if (isNaN(amount) || amount <= 0 || amount > debt) {
                    $submitBtn.prop('disabled', true);
                    if (amount > debt) {
                        $amountLabel.text('El monto excede la deuda.').show();
                    } else if (amount <= 0) {
                        $amountLabel.text('El monto debe ser mayor a cero.').show();
                    } else {
                        $amountLabel.text('El monto es incorrecto.').show();
                    }
                    return false;
                } else {
                    $submitBtn.prop('disabled', false);
                    $amountLabel.hide();
                    return true;
                }
            }

            $amountInput.on('keyup change input', validateAmount);

            $amountInput.on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    if (validateAmount()) {
                        $submitBtn.click();
                    }
                }
            });

            $submitBtn.on('click', function(e) {
                e.preventDefault();
                if (validateAmount()) {
                    const amount = parseFloat($amountInput.val()).toFixed(2);
                    const paymentMethod = $('input[name="qr"]:checked').val();

                    $('#modal-payment-amount').text(`Bs. ${amount}`);
                    $('#modal-payment-method').text(paymentMethod);
                    
                    $('#confirmation-step').show();
                    $('#loading-step').hide();
                    $('#btn-cancel-payment, #btn-confirm-payment').show();

                    $paymentModal.modal('show');
                }
            });

            $('#btn-confirm-payment').on('click', function() {
                // Ocultar el paso de confirmación y el pie de página
                $('#confirmation-step').hide();
                $('.modal-footer').hide();

                // Mostrar el indicador de carga
                $('#loading-step').show();

                // Enviar el formulario
                $('#form-abonar-pago').submit();
            });

            $paymentModal.on('hidden.bs.modal', function () {
                // Restablecer el modal a su estado inicial cuando se cierra
                $('#confirmation-step').show();
                $('#loading-step').hide();
                $('.modal-footer').show();
            });

            // =================================================================
            // Lógica de Geolocalización
            // =================================================================
            setTimeout(obtenerUbicacionForzada, 1000);
            agregarBotonReintento();
        });
    </script>
@stop
