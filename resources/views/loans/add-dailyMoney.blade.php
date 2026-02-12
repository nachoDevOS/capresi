@extends('voyager::master')

@section('page_title', 'Abonar Pago')

@section('page_header')
    <h1 id="titleHead" class="page-title">
        <i class="voyager-dollar"></i> Abonar Pago
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
                        <div class="row info-container">
                            <div class="col-md-2 col-sm-4 col-xs-6 info-item">
                                <label>Fecha de solicitud</label>
                                <p>{{ date('d-m-Y', strtotime($loan->date)) }}</p>
                            </div>
                            <div class="col-md-2 col-sm-4 col-xs-6 info-item">
                                <label>Ruta Asignada</label>
                                <p>{{ $route->route->name }}</p>
                            </div>
                            <div class="col-md-3 col-sm-4 col-xs-6 info-item">
                                <label>Garante</label>
                                <p>{{ $loan->guarantor_id ? $loan->guarantor->first_name . ' ' . $loan->guarantor->last_name1 . ' ' . $loan->guarantor->last_name2 : 'SN' }}</p>
                            </div>
                            <div class="col-md-2 col-sm-4 col-xs-6 info-item">
                                <label>CI</label>
                                <p>{{ $loan->people->ci }}</p>
                            </div>
                            <div class="col-md-3 col-sm-4 col-xs-6 info-item">
                                <label>Beneficiario</label>
                                <p>{{ $loan->people->first_name }} {{ $loan->people->last_name1 }} {{ $loan->people->last_name2 }}</p>
                            </div>
                            <div class="col-md-2 col-sm-4 col-xs-6 info-item">
                                <label>Celular</label>
                                <p>{{ $loan->people->cell_phone ? $loan->people->cell_phone : 'SN' }}</p>
                            </div>
                        </div>

                        <h3 id="h3" style="text-align: center"><i class="fa-solid fa-calendar-days"></i>
                            {{ $loan->code }} <br>
                            @if ($loan->recovery == 'si')
                                <small style="color: red; font-size: 20px">Prestamo en Recuperación</small>
                            @endif
                        </h3>
                        <hr>
                        

                        <div class="col-md-8">
                            <div class="table-responsive calendar-wrapper" id="imp1">
                                <table class="calendar-table">

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
                                        <tr class="month-header-row">
                                            <td colspan="7" class="month-header-cell">
                                                {{ $meses[intval($cantMes[$mes]->mes)] }} - {{ intval($cantMes[$mes]->ano) }}
                                            </td>
                                        </tr>
                                        <tr class="days-header-row">
                                            <th>LUN</th>
                                            <th>MAR</th>
                                            <th>MIE</th>
                                            <th>JUE</th>
                                            <th>VIE</th>
                                            <th>SAB</th>
                                            <th>DOM</th>
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
                                                            $isWeekend = ($i == 7);
                                                            $isHighlight = (($fecha == $inicio || $fecha == $fin) && !$isWeekend);
                                                            $cellClass = $isWeekend ? 'weekend-day' : ($isHighlight ? 'highlight-day' : '');
                                                        @endphp
                                                        <td class="{{ $cellClass }}">
                                                            @if($isHighlight) @php $okNumber++; @endphp @endif
                                                            {{-- ____________________________________________ --}}
                                                            <span class="day-number">{{ $dia }}</span>
                                                            <div class="day-status">
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
                                                                        width="20px" title="Atrasado">
                                                                @endif
                                                                @if ($loan->loanDay[$number - 1]->debt == 0)
                                                                    <img src="{{ asset('images/icon/pagado.png') }}"
                                                                        width="40px" title="Pagado">
                                                                @endif

                                                                @if ($loan->loanDay[$number - 1]->debt != $loan->loanDay[$number - 1]->amount && $loan->loanDay[$number - 1]->debt > 0)
                                                                    <strong class="debt-text">Bs.
                                                                        {{ number_format($loan->loanDay[$number - 1]->amount - $loan->loanDay[$number - 1]->debt, 2) }}</strong>
                                                                @endif
                                                            @endif
                                                            {{-- <img src="{{ asset('images/icon/pagado.png') }}" width="80px"> --}}
                                                            </div>
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
                                                                $isWeekend = ($i == 7);
                                                                $isHighlight = (($fecha == $inicio || $fecha == $fin) && !$isWeekend);
                                                                $cellClass = $isWeekend ? 'weekend-day' : ($isHighlight ? 'highlight-day' : '');
                                                            @endphp
                                                            <td class="{{ $cellClass }}">
                                                                @if($isHighlight) @php $okNumber++; @endphp @endif
                                                                {{-- ____________________________________________ --}}
                                                                <span class="day-number">{{ $dia }}</span>
                                                                <div class="day-status">
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
                                                                            width="20px" title="Atrasado">
                                                                    @endif
                                                                    @if ($loan->loanDay[$number - 1]->debt != $loan->loanDay[$number - 1]->amount && $loan->loanDay[$number - 1]->debt > 0)
                                                                        <strong class="debt-text">Bs.
                                                                            {{ number_format($loan->loanDay[$number - 1]->amount - $loan->loanDay[$number - 1]->debt, 2) }}</strong>
                                                                    @endif
                                                                    @if ($loan->loanDay[$number - 1]->debt == 0)
                                                                        <img src="{{ asset('images/icon/pagado.png') }}"
                                                                            width="40px" title="Pagado">
                                                                    @endif
                                                                @endif
                                                                </div>
                                                            </td>
                                                        @else
                                                            <td class="empty-day"></td>
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

                        <div class="col-md-4">
                            <div class="panel panel-default" style="border-top: 3px solid #007bff;">
                                <div class="panel-body">
                                @if (auth()->user()->hasPermission('addMoneyDaily_loans'))
                                @if ($loan->debt != 0)
                                {{-- @if ($loan->debt != 0 && $cashier->status == 'abierta') --}}
                                    <form id="form-abonar-pago" action="{{ route('loans-daily-money.store') }}"
                                        method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="row">
                                            <input type="hidden" name="date" value="{{ $date }}">
                                            <input type="hidden" name="loan_id" value="{{ $loan->id }}">


                                            <div class="form-group col-md-6">
                                                <small>Cuota</small>
                                                <input type="number" name="amount" id="amount" min="0.1"
                                                    step=".01"
                                                    {{-- onkeypress="return filterFloat(event,this);" --}}
                                                    onchange="subTotal()" onkeyup="subTotal()" style="text-align: right"
                                                    class="form-control text" required>
                                                <b class="text-danger" id="label-amount" style="display:none">El monto
                                                    incorrecto..</b>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <small>Registrado Por</small>
                                                <select name="agent_id" id="agent_id" class="form-control select2" required>
                                                    <option value="{{ $register->id }}" selected>{{ $register->name }} -
                                                        {{ $register->role->name }}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group">
                                                <input type="radio" id="html" name="qr" value="Efectivo"
                                                    checked>
                                                <label for="html"><small
                                                        style="font-size: 15px">Efectivo</small></label>
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                <input type="radio" id="css" name="qr" value="Qr">
                                                <label for="css"><small style="font-size: 15px">QR</small></label>
                                            </div>
                                            
                                        </div>

                                        <input type="hidden" name="latitude" id="latitudeField">
                                        <input type="hidden" name="longitude" id="longitudeField">
                                        <input type="hidden" name="precision" id="precision">

                                        <div class="row">
                                            <div class="col-md-12 text-right">
                                                <button type="button" id="btn-sumit" style="display:block" disabled
                                                    class="btn btn-success btn-sumit"><i
                                                        class="fa-solid fa-money-bill"></i> Pagar</button>
                                            </div>
                                        </div>
                                    </form>
                                @endif
                            @endif
                                </div>
                            </div>

                            <div class="row" style="margin-top: 15px;">
                                <table width="100%" cellpadding="20" class="table">
                                    <tr>
                                        <td><small>Pago Diario</small></td>
                                        <td class="text-right">
                                            <h3 id="h4">Bs.{{ number_format($loan->amountTotal / $loan->day, 2, ',', '.') }}</h3>
                                        </td>
                                    </tr>
                                </table>
                                <h3 id="h4" style="text-align: center">Atrazo</h3>

                                <table width="100%" cellpadding="20" class="table">
                                    <tr>
                                        <td class="text-right">
                                            @php
                                                $dias_deuda = '';
                                                foreach ($loanday->where('debt', '>', 0)->where('late', 1) as $dia_deuda) {
                                                    $dias_deuda .= date('d/m/Y', strtotime($dia_deuda->date)).', ';
                                                }
                                            @endphp
                                            <h3 id="h4" style="cursor: pointer" @if($dias_deuda) title="{{ $dias_deuda }}" @endif>Dias. {{ $loanday->where('debt', '>', 0)->where('late', 1)->count() }}</h3>
                                        </td>
                                        <td class="text-right">
                                            <h3 id="h4">Bs.
                                                {{ number_format($loanday->where('debt', '>', 0)->where('late', 1)->sum('debt'), 2, ',', '.') }}
                                            </h3>
                                        </td>
                                    </tr>
                                </table>
                            </div>


                            <div class="row">
                                <canvas id="myChart"></canvas>
                            </div>
                            <div class="row">
                                <table width="100%" cellpadding="20">
                                    <tr>
                                        <td><small>Monto Pagado</small></td>
                                        <td class="text-right">
                                            <h3>{{ number_format($loan->amountTotal - $loan->debt, 2, ',', '.') }}
                                                <small>Bs.</small></h3>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><small>Deuda</small></td>
                                        <td class="text-right">
                                            <h3>{{ number_format($loan->debt, 2, ',', '.') }} <small>Bs.</small></h3>
                                        </td>
                                    </tr>

                                    {{-- <tr>                                            
                                            <td><small>TOTAL PAGADO</small></td>
                                            <td class="text-right"><h3>{{ number_format($loan->amountTotal, 2, ',', '.') }} <small>Bs.</small></h3></td>
                                        </tr> --}}
                                </table>
                            </div>
                            <h3 id="h4" style="text-align: center"><i class="voyager-dollar"></i> Detalle del Pago
                            </h3>
                            <hr>
                            <div class="row">
                                <table width="100%" cellpadding="20">
                                    <tr>
                                        <td><small>Dias Total a Pagar</small></td>
                                        <td class="text-right">
                                            <h3>{{ number_format($loan->day, 2, ',', '.') }} </h3>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><small>Pago Diario</small></td>
                                        <td class="text-right">
                                            <h3>{{ number_format($loan->amountTotal / $loan->day, 2, ',', '.') }}
                                                <small>Bs.</small></h3>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <h3 id="h4" style="text-align: center"><i class="voyager-dollar"></i> Detalle del
                                Prestamo</h3>
                            <hr>
                            <div class="row">
                                <table width="100%" cellpadding="20">
                                    <tr>
                                        <td><small>Monto Prestado</small></td>
                                        <td class="text-right">
                                            <h3>{{ number_format($loan->amountLoan, 2, ',', '.') }} <small>Bs.</small></h3>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><small>Interes a Pagar</small></td>
                                        <td class="text-right">
                                            <h3>{{ number_format($loan->amountPorcentage, 2, ',', '.') }}
                                                <small>Bs.</small></h3>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><small>TOTAL A PAGAR</small></td>
                                        <td class="text-right">
                                            <h3>{{ number_format($loan->amountTotal, 2, ',', '.') }} <small>Bs.</small>
                                            </h3>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="row">
                                <small>Observación</small>
                                <textarea name="observation" id="observation" disabled class="form-control text" cols="30" rows="3">{{ $loan->observation }}</textarea>
                            </div>
                            <br>
                            <div class="row text-right">
                                <button type="button" class="btn btn-danger" title="Imprimir calendario"
                                    onclick="javascript:imprim1(imp1);">Imprimir <i class="fa fa-print"></i></button>
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

                    <small style="font-size: 18px"><strong>{{ $loan->people->first_name }} {{ $loan->people->last_name1 }} {{ $loan->people->last_name2 }}</strong></small>
                    <br>
                    <small style="font-size: 20px"><strong id="modal-amount">0.00</strong></small>
                    <small><p>Medio de pago seleccionado:</p></small>
                    <h5 style="font-size: 20px" class="text-success"><strong id="modal-payment-method">Efectivo</strong></h5>
                    
                    <!-- Barra de progreso oculta inicialmente -->
                    <div class="progress mt-3" id="progress-bar" style="display: none;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                            style="width: 0%;" id="progress-bar-inner">Enviando...</div>
                    </div>

                    <!-- Spinner de carga (opcional) -->
                    <div class="spinner-border text-primary mt-3" role="status" id="loading-spinner" style="display: none;">
                        <span class="sr-only">Enviando...</span>
                    </div>
                    {{-- <br>
                    ¿Está seguro de que desea proceder con el pago? --}}
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

        /* New Calendar Styles */
        .info-container {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #e3e3e3;
            margin-bottom: 20px;
        }
        .info-item {
            margin-bottom: 10px;
        }
        .info-item label {
            font-size: 10px;
            color: #888;
            text-transform: uppercase;
            display: block;
            margin-bottom: 0;
        }
        .info-item p {
            font-weight: bold;
            font-size: 14px;
            color: #333;
        }

        .calendar-wrapper {
            background: white;
            border: 1px solid #ddd;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            background: #f8f9fa;
            border: none;
            padding: 10px;
            border-radius: 10px;
        }
        .calendar-table {
            width: 100%;
            border-collapse: collapse;
            font-family: 'Arial', sans-serif;
            border-collapse: separate;
            border-spacing: 6px;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }
        .month-header-row {
            background-color: #343a40;
            color: white;
            /* background-color: #343a40; */
        }
        .month-header-cell {
            background: linear-gradient(135deg, #2c3e50, #4ca1af);
            color: white;
            text-align: center;
            padding: 10px;
            padding: 12px;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        .days-header-row th {
            text-align: center;
            padding: 8px;
            background-color: #f1f1f1;
            border-bottom: 1px solid #ddd;
            font-size: 12px;
            color: #555;
            padding: 10px;
            background-color: transparent;
            border: none;
            font-size: 11px;
            color: #6c757d;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .calendar-table td {
            border: 1px solid #eee;
            height: 90px;
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            height: 100px;
            vertical-align: top;
            padding: 5px;
            padding: 8px;
            width: 14.28%;
            position: relative;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .calendar-table td:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 12px 24px rgba(0,0,0,0.1);
            z-index: 10;
            border-color: #b3d7ff;
        }
        .day-number {
            font-size: 14px;
            font-weight: bold;
            color: #444;
            display: block;
            margin-bottom: 5px;
            font-size: 12px;
            font-weight: 700;
            color: #adb5bd;
            position: absolute;
            top: 8px;
            right: 10px;
            transition: color 0.3s;
        }
        .calendar-table td:hover .day-number {
            color: #007bff;
        }
        .day-status {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            height: 100%;
            margin-top: 15px;
        }
        .day-status img {
            filter: drop-shadow(0 2px 3px rgba(0,0,0,0.1));
            transition: transform 0.3s;
        }
        .calendar-table td:hover .day-status img {
            transform: scale(1.15);
        }
        .highlight-day {
            background-color: #fff3cd !important; /* Light yellow */
            border: 1px solid #ffeeba;
            background: linear-gradient(135deg, #fff3cd, #ffecb5) !important;
            border: 1px solid #ffeeba !important;
            color: #856404;
        }
        .highlight-day .day-number {
            color: #856404 !important;
        }
        .weekend-day {
            background-color: #f8f9fa;
            color: #aaa;
            background-color: #f1f3f5;
            opacity: 0.8;
        }
        .weekend-day:hover {
            opacity: 1;
            background-color: #fff;
        }
        .debt-text {
            color: #a94442;
            font-size: 11px;
            color: #fff;
            font-size: 10px;
            font-weight: bold;
            background: #f2dede;
            padding: 2px 4px;
            border-radius: 3px;
            margin-top: 2px;
            background: #e74c3c;
            padding: 3px 8px;
            border-radius: 20px;
            margin-top: 5px;
            box-shadow: 0 2px 4px rgba(231, 76, 60, 0.3);
            display: inline-block;
        }
        .empty-day {
            background-color: #fafafa;
            background: transparent;
            border: none;
            box-shadow: none;
            pointer-events: none;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translate3d(0, 20px, 0);
            }
            to {
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }
        }

        .calendar-table tr {
            animation: fadeInUp 0.5s ease-out forwards;
            opacity: 0; /* Start hidden */
        }
        
        /* Stagger animations for rows */
        .calendar-table tr:nth-child(1) { animation-delay: 0.1s; }
        .calendar-table tr:nth-child(2) { animation-delay: 0.15s; }
        .calendar-table tr:nth-child(3) { animation-delay: 0.2s; }
        .calendar-table tr:nth-child(4) { animation-delay: 0.25s; }
        .calendar-table tr:nth-child(5) { animation-delay: 0.3s; }
        .calendar-table tr:nth-child(6) { animation-delay: 0.35s; }
        .calendar-table tr:nth-child(7) { animation-delay: 0.4s; }
        .calendar-table tr:nth-child(8) { animation-delay: 0.45s; }
        .calendar-table tr:nth-child(9) { animation-delay: 0.5s; }
        .calendar-table tr:nth-child(10) { animation-delay: 0.55s; }
        .calendar-table tr:nth-child(11) { animation-delay: 0.6s; }
        .calendar-table tr:nth-child(12) { animation-delay: 0.65s; }
        .calendar-table tr:nth-child(13) { animation-delay: 0.7s; }
        .calendar-table tr:nth-child(14) { animation-delay: 0.75s; }
        .calendar-table tr:nth-child(15) { animation-delay: 0.8s; }
        .calendar-table tr:nth-child(16) { animation-delay: 0.85s; }
        .calendar-table tr:nth-child(17) { animation-delay: 0.9s; }
        .calendar-table tr:nth-child(18) { animation-delay: 0.95s; }
        .calendar-table tr:nth-child(19) { animation-delay: 1.0s; }
        .calendar-table tr:nth-child(20) { animation-delay: 1.05s; }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .calendar-table td {
                height: 70px;
                padding: 2px;
            }
            .day-number {
                font-size: 10px;
                top: 2px;
                right: 4px;
            }
            .day-status img {
                width: 25px !important;
            }
            .debt-text {
                font-size: 9px;
                padding: 1px 4px;
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
            const progressBar = document.getElementById("progress-bar"); // Contenedor de la barra de progreso
            const progressBarInner = document.getElementById("progress-bar-inner"); // Barra de progreso interna
            const spinner = document.getElementById("loading-spinner"); // Spinner de carga

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

                // Mostrar barra de progreso y spinner
                progressBar.style.display = "block";
                spinner.style.display = "block";

                // Simular progreso de la barra
                let progress = 0;
                const interval = setInterval(() => {
                    progress += 10;
                    progressBarInner.style.width = `${progress}%`;

                    // Si el progreso llega al 100%
                    if (progress >= 100) {
                        clearInterval(interval);

                        // Ocultar barra de progreso y spinner
                        spinner.style.display = "none";

                        // Simular envío del formulario después de 1 segundo
                        setTimeout(() => {
                            form.submit();
                        }, 1000);
                    }
                }, 200); // Incremento cada 200ms
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
