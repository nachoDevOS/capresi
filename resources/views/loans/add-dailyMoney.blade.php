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
                            <div class="panel-body table-responsive" id="imp1">
                                <table width="100%" border="1" cellpadding="5" style="font-size: 12px">

                                    @php
                                        $meses = [
                                            1 => 'Enero',
                                            'Febrero',
                                            'Marzo',
                                            'Abril',
                                            'Mayo',
                                            'Junio',
                                            'Julio',
                                            'Agosto',
                                            'Septiembre',
                                            'Octubre',
                                            'Noviembre',
                                            'Diciembre',
                                        ];

                                        $fechaInicio = \Carbon\Carbon::parse($loan->loanDay[0]->date);
                                        $mesInicio = $fechaInicio->format('n'); //para saber desde que mes empiesa la cuota
                                        $diaInicio = $fechaInicio->format('d'); //para saber en que dia se paga la cuota
                                        $anoInicio = $fechaInicio->format('Y'); //para saber en que año empiesa la cuota
                                        // dd($diaInicio);
                                        $inicio =
                                            $anoInicio .
                                            '-' .
                                            ($mesInicio <= 9 ? '0' . $mesInicio : '' . $mesInicio) .
                                            '-' .
                                            $diaInicio;
                                        // dd($inicio);

                                        $fechaFin = \Carbon\Carbon::parse($loan->loanDay[count($loanday) - 1]->date);
                                        $mesFin = $fechaFin->format('n'); //para saber hasta que mes termina la cuota
                                        $diaFin = $fechaFin->format('d'); //para saber hasta que dia termina la cuota
                                        $anoFin = $fechaFin->format('Y'); //para saber hasta que año termina la cuota
                                        // dd($fechaFin);
                                        $fin =
                                            $anoFin . '-' . ($mesFin <= 9 ? '0' . $mesFin : '' . $mesFin) . '-' . $diaFin;

                                        // $aux <= 9 ? '-0'.$aux : '-'.$aux
                                        // dd($fin);

                                        $cantMeses = count($cantMes); //para la cantidad de meses que hay entre las dos fecha
                                        $mes = 0;

                                        $number = 0;
                                        $cantNumber = count($loanday);

                                        $okNumber = 0;
                                        // dd($cantNumber);
                                    @endphp

                                    @while ($mes < $cantMeses)
                                        <tr style="background-color: #666666; color: white; font-size: 18px">
                                            <td colspan="7" style="text-align: center">
                                                {{ $meses[intval($cantMes[$mes]->mes)] }} - {{ intval($cantMes[$mes]->ano) }}
                                            </td>
                                        </tr>
                                        <tr style="background-color: #666666; color: white; font-size: 15px">
                                            <td style="text-align: center; width: 15%">LUN</td>
                                            <td style="text-align: center; width: 15%">MAR</td>
                                            <td style="text-align: center; width: 15%">MIE</td>
                                            <td style="text-align: center; width: 15%">JUE</td>
                                            <td style="text-align: center; width: 15%">VIE</td>
                                            <td style="text-align: center; width: 15%">SAB</td>
                                            <td style="text-align: center; width: 10%">DOM</td>
                                        </tr>

                                        @php
                                            $primerDia = date(
                                                'd',
                                                mktime(
                                                    0,
                                                    0,
                                                    0,
                                                    intval($cantMes[$mes]->mes),
                                                    1,
                                                    intval($cantMes[$mes]->ano),
                                                ),
                                            ); //para obtener el primer dia del mes
                                            $primerFecha =
                                                intval($cantMes[$mes]->ano) .
                                                '-' .
                                                intval($cantMes[$mes]->mes) .
                                                '-' .
                                                $primerDia; // "20XX-XX-01"concatenamos el primer dia ma sel mes y el año del la primera cuota
                                            $posicionPrimerFecha = \Carbon\Carbon::parse($primerFecha);
                                            $posicionPrimerFecha = $posicionPrimerFecha->format('N'); //obtenemos la posicion de la fecha en que dia cahe pero en numero

                                            $ultimoDia = date(
                                                'd',
                                                mktime(
                                                    0,
                                                    0,
                                                    0,
                                                    intval($cantMes[$mes]->mes) + 1,
                                                    0,
                                                    intval($cantMes[$mes]->ano),
                                                ),
                                            ); //para obtener el ultimo dia del mes
                                            $ok = false;

                                            $dia = 0;
                                        @endphp

                                        @for ($x = 1; $x <= 6; $x++)
                                            <tr>
                                                @for ($i = 1; $i <= 7; $i++)
                                                    @if ($i == $posicionPrimerFecha && !$ok)
                                                        @php
                                                            $dia++;
                                                            $ok = true;
                                                            $fecha =
                                                                $cantMes[$mes]->ano .
                                                                '-' .
                                                                $cantMes[$mes]->mes .
                                                                ($dia <= 9 ? '-0' . $dia : '-' . $dia);
                                                            // dd($fecha);
                                                        @endphp
                                                        <td @if ($i == 7) style="height: 80px; text-align: center; background-color: #CCCFD2" @endif
                                                            @if (($fecha == $inicio || $fecha == $fin) && $i != 7) @php
                                                                        $okNumber++;
                                                                    @endphp
                                                                    style="height: 80px; text-align: center; background-color: #F8FF07;"
                                                                @else
                                                                    style="height: 80px; text-align: center" @endif>
                                                            {{-- ____________________________________________ --}}
                                                            <small style="font-size: 18px;">{{ $dia }}</small>
                                                            <br>


                                                            @if (($okNumber == 1 || $okNumber == 2) && $i != 7)
                                                                @php
                                                                    if ($okNumber == 2) {
                                                                        $okNumber++;
                                                                    }
                                                                    $number++;
                                                                @endphp

                                                                @if ($loan->loanDay[$number - 1]->late == 1)
                                                                    <img src="{{ asset('images/icon/atrazado.png') }}"
                                                                        width="15px">
                                                                @endif
                                                                @if ($loan->loanDay[$number - 1]->debt == 0)
                                                                    <img src="{{ asset('images/icon/pagado.png') }}"
                                                                        width="50px">
                                                                @endif

                                                                @if ($loan->loanDay[$number - 1]->debt != $loan->loanDay[$number - 1]->amount && $loan->loanDay[$number - 1]->debt > 0)
                                                                    <strong style="font-size: 20px; color:#440505">Bs.
                                                                        {{ $loan->loanDay[$number - 1]->amount - $loan->loanDay[$number - 1]->debt }}</strong>
                                                                @endif
                                                            @endif
                                                            {{-- <img src="{{ asset('images/icon/pagado.png') }}" width="80px"> --}}
                                                        </td>
                                                    @else
                                                        @if ($ok && $dia < $ultimoDia)
                                                            {{-- para que muestre hasta el ultimo dia del mes  --}}
                                                            @php
                                                                $dia++;
                                                                $fecha =
                                                                    $cantMes[$mes]->ano .
                                                                    '-' .
                                                                    $cantMes[$mes]->mes .
                                                                    ($dia <= 9 ? '-0' . $dia : '-' . $dia);
                                                            @endphp
                                                            <td @if ($i == 7) style="height: 80px; text-align: center; background-color: #CCCFD2" @endif
                                                                @if (($fecha == $inicio || $fecha == $fin) && $i != 7) @php
                                                                            $okNumber++;
                                                                        @endphp
                                                                        style="height: 80px; text-align: center; background-color: #F8FF07;"
                                                                    @else
                                                                        style="height: 80px; text-align: center;" @endif>
                                                                {{-- ____________________________________________ --}}
                                                                <small style="font-size: 18px;">{{ $dia }}</small>
                                                                <br>

                                                                @if (($okNumber == 1 || $okNumber == 2) && $i != 7)
                                                                    @php
                                                                        if ($okNumber == 2) {
                                                                            $okNumber++;
                                                                        }
                                                                        $number++;
                                                                    @endphp

                                                                    @if ($loan->loanDay[$number - 1]->late == 1)
                                                                        <img src="{{ asset('images/icon/atrazado.png') }}"
                                                                            width="15px">
                                                                    @endif
                                                                    @if ($loan->loanDay[$number - 1]->debt != $loan->loanDay[$number - 1]->amount && $loan->loanDay[$number - 1]->debt > 0)
                                                                        <strong style="font-size: 20px; color:#440505">Bs.
                                                                            {{ $loan->loanDay[$number - 1]->amount - $loan->loanDay[$number - 1]->debt }}</strong>
                                                                    @endif
                                                                    @if ($loan->loanDay[$number - 1]->debt == 0)
                                                                        <img src="{{ asset('images/icon/pagado.png') }}"
                                                                            width="50px">
                                                                    @endif
                                                                @endif
                                                            </td>
                                                        @else
                                                            <td style="height: 80px; text-align: center"></td>
                                                        @endif
                                                    @endif
                                                    @if ($dia == $ultimoDia)
                                                        @php
                                                            $x = 10;
                                                        @endphp
                                                    @endif
                                                @endfor
                                            </tr>
                                        @endfor
                                        @php
                                            $mes++;
                                        @endphp
                                    @endwhile
                                </table>
                            </div>
                        </div>

                    <!-- Columna Derecha: Formulario y Detalles -->
                    <div class="col-md-4">
                        
                        <!-- Formulario de Pago -->
                        @if (auth()->user()->hasPermission('addMoneyDaily_loans') && $loan->debt != 0)
                        <div class="panel panel-success" style="border: 1px solid #2ecc71;">
                            <div class="panel-heading" style="background-color: #2ecc71; color: white;">
                                <h3 class="panel-title"><i class="fa-solid fa-money-bill-wave"></i> Registrar Pago</h3>
                            </div>
                            <div class="panel-body">
                                    <form id="form-abonar-pago" action="{{ route('loans-daily-money.store') }}"
                                        method="POST" enctype="multipart/form-data">
                                        @csrf
                                            <input type="hidden" name="date" value="{{ $date }}">
                                            <input type="hidden" name="loan_id" value="{{ $loan->id }}">


                                            <div class="form-group">
                                                <label for="amount">Monto a Pagar</label>
                                                <div class="input-group">
                                                    <span class="input-group-addon">Bs.</span>
                                                    <input type="number" name="amount" id="amount" min="0.1" step=".01"
                                                        onchange="subTotal()" onkeyup="subTotal()" style="text-align: right; font-size: 20px; font-weight: bold;"
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
                                            <i class="fa-solid fa-check-circle"></i> CONFIRMAR PAGO
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


        <!-- Modal de confirmación -->
    <div class="modal fade" id="confirmModal" tabindex="-1" data-backdrop="static" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirmar Pago</h5>
                </div>
                <div class="modal-body">
                    <div id="step-confirmation">
                        <small style="font-size: 18px"><strong>{{ $loan->people->first_name }} {{ $loan->people->last_name1 }} {{ $loan->people->last_name2 }}</strong></small>
                        <br>
                        <small style="font-size: 20px"><strong id="modal-amount">0.00</strong></small>
                        <small><p>Medio de pago seleccionado:</p></small>
                        <h5 style="font-size: 20px" class="text-success"><strong id="modal-payment-method">Efectivo</strong></h5>
                    </div>
                    <div id="step-loading" style="display: none; text-align: center; padding: 20px;">
                        <i class="fa-solid fa-spinner fa-spin" style="font-size: 5em; color: #28a745;"></i>
                        <h4 class="mt-3" style="margin-top: 20px;">Procesando pago...</h4>
                        <p class="text-muted">Por favor espere, estamos registrando la transacción.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-submit-cancel-modal btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" id="btn-submit-confirm" class="btn-submit-confirm-modal btn btn-primary">Confirmar</button>
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
        /* Estilo de la cabecera */
        .modal-header {
            background-color: #f8f9fa;
            border-bottom: none;
            text-align: center;
            padding: 15px;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
            margin: 0 auto;
        }

        .modal-body {
            text-align: center;
            font-size: 1rem;
            color: #555;
            padding: 20px 30px;
        }

        /* Botones */
        .modal-footer {
            border-top: none;
            display: flex;
            justify-content: center;
            gap: 10px;
            padding-bottom: 20px;
        }

        .btn-custom {
            font-size: 1rem;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            width: 130px;
            transition: all 0.3s ease-in-out;
        }

        .btn-cancel {
            background-color: #e0e0e0;
            color: #333;
            border: none;
        }

        .btn-cancel:hover {
            background-color: #d6d6d6;
        }

        .btn-confirm {
            background-color: #007bff;
            color: white;
            border: none;
        }

        .btn-confirm:hover {
            background-color: #0056b3;
        }

        /* Ícono */
        .modal-icon {
            font-size: 3rem;
            color: #007bff;
            margin-bottom: 10px;
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
    </style>
@endsection

@section('javascript')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.1.4/Chart.bundle.min.js"></script>
{{-- 
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script> --}}

    <!-- Incluir el nuevo archivo JS de impresión -->
    <script src="{{ asset('js/print.js') }}"></script>


    <script>
        $(document).ready(function() {
            $("#amount").on('paste', function(e) {
                e.preventDefault();
            })

            $('#form-abonar-pago').submit(function(e) {
                $('.btn-sumit').attr('disabled', true);
            });
        })
        $(document).ready(function() {

            const data = {
                labels: [
                    'Deuda Total',
                    'Total Pagado'
                ],
                datasets: [{
                    label: 'My First Dataset',
                    data: ["{{ $loan->debt }}", "{{ $loan->amountTotal - $loan->debt }}"],
                    backgroundColor: [
                        'red',
                        'rgb(54, 205, 1)'
                    ],
                    hoverOffset: 4
                }]
            };
            const config = {
                type: 'pie',
                data: data,
            };
            var myChart = new Chart(
                document.getElementById('myChart'),
                config
            );
        });
        document.addEventListener("DOMContentLoaded", function() {
            const btnConfirm = document.getElementById("btn-sumit"); // Botón "Pagar"
            const btnSubmitConfirm = document.getElementById("btn-submit-confirm"); // Botón "Confirmar" en el modal
            const form = document.getElementById("form-abonar-pago"); // Formulario
            const amountInput = document.getElementById("amount"); // Input del monto
            const modalAmount = document.getElementById("modal-amount"); // Monto dentro del modal
            const modalPaymentMethod = document.getElementById("modal-payment-method"); // Método de pago en el modal
            const labelAmount = document.getElementById("label-amount"); // Mensaje de error

            // Mostrar el modal con el monto y el método de pago
            btnConfirm.addEventListener("click", function() {
                const amountValue = parseFloat(amountInput.value).toFixed(2);
                const selectedPaymentMethod = document.querySelector('input[name="qr"]:checked').value;

                // Validar si el monto es correcto antes de abrir el modal
                if (!amountValue || amountValue <= 0 || isNaN(amountValue)) {
                    labelAmount.style.display = "block";
                    return;
                }

                labelAmount.style.display = "none";
                modalAmount.textContent = `Bs. ${amountValue}`; // Mostrar el monto
                modalPaymentMethod.textContent = selectedPaymentMethod; // Mostrar el método de pago
                $("#confirmModal").modal("show");
            });

            // Enviar el formulario cuando se confirme el pago
            btnSubmitConfirm.addEventListener("click", function() {
                // Ocultar botones de acción
                btnSubmitConfirm.style.display = "none";
                document.querySelector(".btn-submit-cancel-modal").style.display = "none";

                // Mostrar animación de carga
                document.getElementById("step-confirmation").style.display = "none";
                document.getElementById("step-loading").style.display = "block";

                // Enviar formulario inmediatamente
                form.submit();
            });
        });


        function subTotal() {
            let amount = $(`#amount`).val() ? parseFloat($(`#amount`).val()) : 0;
            let debt = {{ $loan->debt }}
            if (amount <= 0 || amount > debt) {
                $('#btn-sumit').attr('disabled', 'disabled');
                $('#label-amount').css('display', 'block');
            } else {
                $('#btn-sumit').removeAttr('disabled');
                $('#label-amount').css('display', 'none');
            }
        }

        let loan_id = 0;
        let transaction_id = 0;
        $(document).ready(function() {

            // @if (session('data'))
            //     printTicket('{{ setting('servidores.print') }}', @json(json_decode(session('data'), true)), '{{ url('admin/loans/daily/money/print') }}', 'LoanPayment');
            // @endif

            
            

            // Ocultar popup de impresión
            setTimeout(() => {
                $('#popup-button').fadeOut('fast');
            }, 8000);
        });

        // function printDailyMoney() {
        //     window.open("{{ url('admin/loans/daily/money/print') }}/" + loan_id + "/" + transaction_id, "Recibo",
        //         `width=700, height=700`)
        // }

        function imprSelec(nombre) {
            var ficha = document.getElementById(nombre);
            var ventimp = window.open(' ', 'popimpr');
            ventimp.document.write(ficha.innerHTML);
            ventimp.document.close();
            ventimp.print();
            ventimp.close();
        }

        function imprim1(imp1) {
            var printContents = document.getElementById('imp1').innerHTML;
            w = window.open();
            w.document.write(printContents);
            w.document.close(); // necessary for IE >= 10
            w.focus(); // necessary for IE >= 10
            w.print();
            // w.close();
            return true;
        }
    </script>

    <script>
        function obtenerUbicacionForzada() {
            // 1. Verificar soporte de geolocalización
            if (!navigator.geolocation) {
                mostrarError("Tu navegador no soporta geolocalización");
                return;
            }
        
            // 2. Configuración estricta del GPS
            const opcionesGPS = {
                enableHighAccuracy: true,  // Forzar alta precisión (GPS)
                timeout: 15000,            // 15 segundos de espera
                maximumAge: 0              // No usar datos cacheados
            };
        
            // 3. Solicitar ubicación
            navigator.geolocation.getCurrentPosition(
                function(posicion) {
                    const campoPre = document.querySelector('[name="precision"], #precision, input.precision');
                    campoPre.value =`¡Ubicación obtenida correctamente!`;


                    // Validar precisión
                    if (posicion.coords.accuracy > 100) {
                        mostrarAdvertencia(`Precisión baja (${Math.round(posicion.coords.accuracy)}m). Usando igualmente los datos.`);
                        campoPre.value =`Precisión baja (${Math.round(posicion.coords.accuracy)}m).`;
                    }
                    // Asignar valores a los campos
                    const campoLat = document.querySelector('[name="latitude"], #latitude, input.latitude');
                    const campoLng = document.querySelector('[name="longitude"], #longitude, input.longitude');


                    // alert(campoLat)

                    
                    if (campoLat && campoLng) {
                        campoLat.value = posicion.coords.latitude.toFixed(6);
                        campoLng.value = posicion.coords.longitude.toFixed(6);
                        mostrarExito("¡Ubicación obtenida correctamente!");
                    } else {
                        mostrarError("No se encontraron campos para coordenadas");
                        campoPre.value =`No se encontraron campos para coordenadas.`;
                    }
                },
                function(error) {
                    manejarErrorGPS(error);
                },
                opcionesGPS
            );
        }
        
        // Funciones auxiliares
        function manejarErrorGPS(error) {
            const errores = {
                1: "Permiso denegado. Debes activar la ubicación en los ajustes de tu dispositivo.",
                2: "No se puede obtener la ubicación. Verifica que el GPS esté activado.",
                3: "Tiempo de espera agotado. El GPS está respondiendo lentamente."
            };
            
            mostrarError(errores[error.code] || "Error desconocido al obtener la ubicación");
        }
        
        function mostrarExito(mensaje) {
            // alert(mensaje); // Puedes reemplazar con un toast o notificación bonita
            toastr.success(mensaje, 'GPS');
            console.log("Éxito: " + mensaje);
        }
        
        function mostrarAdvertencia(mensaje) {
            // alert(mensaje);
            toastr.warning(mensaje, 'Advertencia GPS');
            console.warn(mensaje);
        }
        
        function mostrarError(mensaje) {
            // alert("ERROR: " + mensaje);
            toastr.error(mensaje, 'Error GPS');

            console.error(mensaje);
        }
        
        // Ejecutar al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            // Esperar 1 segundo para que Voyager cargue completamente los campos
            setTimeout(obtenerUbicacionForzada, 1000);
        });
        
        // Opcional: Botón para reintentar
        function agregarBotonReintento() {
            const boton = document.createElement('button');
            boton.textContent = 'Obtener Ubicación';
            boton.className = 'btn btn-primary';
            boton.style.margin = '10px 0';
            boton.onclick = obtenerUbicacionForzada;
            
            const contenedor = document.querySelector('.form-group.latitude') || 
                            document.querySelector('.form-content');
            if (contenedor) {
                contenedor.appendChild(boton);
            }
        }
        
        // Llamar a la función para agregar el botón
        agregarBotonReintento();
    </script>
@stop
