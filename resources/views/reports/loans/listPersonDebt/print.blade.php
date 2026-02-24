@extends('layouts.template-print')

@section('page_title', 'Reporte')

@section('content')
    @php
        $months = array('', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic');    
    @endphp

    <table width="100%">
        <tr>
            <td style="width: 15%"><img src="{{ asset('images/icon.png') }}" alt="CAPRESI" width="70px"></td>
            <td style="text-align: center;  width:70%">
                <h3 style="margin-bottom: 0px; margin-top: 5px">
                    EMPRESA "CAPRESI"<br>
                </h3>
                <h4 style="margin-bottom: 0px; margin-top: 5px">
                    LISTA DE COBRANZA
                    <br>
                    {{ $message }}
                </h4>
                <small style="margin-bottom: 0px; margin-top: 5px">
                    {{ date('d') }} de {{ $months[intval(date('m'))] }} de {{ date('Y') }}
                </small>
            </td>
            <td style="text-align: right; width:15%">
                <h3 style="margin-bottom: 0px; margin-top: 5px">
                   
                    <small style="font-size: 11px; font-weight: 100">Impreso por: {{ Auth::user()->name }} <br> {{ date('d/m/Y H:i:s') }}</small>
                </h3>
            </td>
        </tr>
    </table>
    <table style="width: 100%; font-size: 8px" border="1" cellspacing="0" cellpadding="4">
        <thead>
            <tr>
                <th rowspan="2" style="width:5px">N&deg;</th>
                <th rowspan="2" style="text-align: center; width:70px">CODIGO</th>
                <th rowspan="2" style="text-align: center">CLIENTE</th>
                <th rowspan="2" style="text-align: center">CELULAR</th>
                <th rowspan="2" style="text-align: center">MONTO PRESTADO</th>
                <th rowspan="2" style="text-align: center">DURACIÓN</th>
                <th rowspan="2" style="text-align: center; width: 50px">PAGO EN <br> EL DIA</th>
                <th rowspan="2" style="width: 80px">OBSERVACIONES</th>
                <th colspan="3" style="text-align: center">RETRASO</th>
            </tr>
            <tr>
                <th style="text-align: center; width:40px">DIAS</th>
                <th style="text-align: center; width:40px">TOTAL A PAGAR</th>
            </tr>
        </thead>
        <tbody>
            @php
                $count = 1;
                $pago_diario = 0;
                $pago_atrasado = 0;
                $pago_pospuestos = 0;
                $date = Illuminate\Support\Carbon::now();

            @endphp
            @forelse ($data as $item)
                @php
                    $view = true;
                    $amount =0;
                    $amountPeriod=0;
                    $cantPeriod=0;
                    if ($item->payments_period_id)
                    {
                        $period = \App\Models\PaymentsPeriod::where('id', $item->payments_period_id)->first();
                        $loans = \App\Models\Loan::where('id', $item->loan_id)->first();
                        // $date = Illuminate\Support\Carbon::now();
                        $date = date("Y-m-d", strtotime($date));
                        $period->name=='Semanal'?$cant=7:$cant=15;

                        $ultPayment = \App\Models\LoanDay::where('loan_id', $item->loan_id)
                        ->where('date', function($query) use ($item) {
                            $query->selectRaw('MAX(date)')
                                ->from('loan_days')
                                ->where('loan_id', $item->loan_id); // Asegúrate de filtrar por loan_id
                        })->first();
                        // $ultDate=true;

                        $start = date("Y-m-d",strtotime($loans->dateDelivered."+ ".$cant." days"));

                        $sundaysCount = 0;
                        $auxDate = $loans->dateDelivered;

                        while ($auxDate <= $start) {
                            $d = new DateTime($auxDate);
                            if ($d->format('w') == 0) { 
                                $sundaysCount++;
                            }
                            $auxDate = date("Y-m-d",strtotime($auxDate."+ 1 days"));

                        }
                        $start = date("Y-m-d",strtotime($start."+ ".$sundaysCount." days"));

                        while($start<$date )
                        {
                            $loanDay = \App\Models\LoanDay::where('loan_id', $item->loan_id)
                                ->where('debt', '>', 0)
                                ->where('date','>=', $loans->dateDelivered)
                                ->where('date','<=', $start)
                                ->get();
                            
                            $sundaysCount = 0;
                            $auxDate = $start;
                                
                            $start = date("Y-m-d",strtotime($start."+ ".$cant." days"));

                            while ($auxDate <= $start) {
                                $d = new DateTime($auxDate);
                                if ($d->format('w') == 0) { 
                                    $sundaysCount++;
                                }
                                $auxDate = date("Y-m-d",strtotime($auxDate."+ 1 days"));

                            }

                            $start = date("Y-m-d",strtotime($start."+ ".$sundaysCount." days"));
                           
                            if($date > $ultPayment)
                            {
                                $loanDay = \App\Models\LoanDay::where('loan_id', $item->loan_id)
                                ->where('debt', '>', 0)
                                ->where('date','>=', $loans->dateDelivered)
                                ->where('date','<=', $date)
                                ->get();
                            }
                            $amountPeriod = $loanDay->sum('debt');
                            $cantPeriod = $loanDay->count();                   
                        }
                        // $start = date("Y-m-d",strtotime($start."- ".$cant." days"));
                        //resta la cantidad del ultimo mas la cantidad de los domingos
                        $start = date("Y-m-d",strtotime($start."- ".$cant+$sundaysCount." days"));
                        if($amountPeriod == 0)
                        {
                            $view=false;
                        }
                    }

                    //Para obtener si tiene que pagar en el dia 
                    $day = Illuminate\Support\Facades\DB::table('loans as l')
                                    ->join('loan_days as ld', 'ld.loan_id', 'l.id')
                                    ->where('l.id', $item->loan_id)
                                    ->where('l.deleted_at', null)
                                    ->where('ld.deleted_at', null)
                                    ->where('ld.debt', '!=', 0)
                                    ->whereDate('ld.date', date('Y-m-d', strtotime($date)))
                                    ->select('ld.debt', 'ld.amount', 'ld.payment_day')
                                    ->first();
                    //Para obtener los dias y la cantidad de los dias atrazados
                    $atras = Illuminate\Support\Facades\DB::table('loans as l')
                                    ->join('loan_days as ld', 'ld.loan_id', 'l.id')
                                    ->where('l.deleted_at', null)
                                    ->where('ld.deleted_at', null)
                                    ->where('ld.debt', '!=', 0)
                                    ->where('ld.late', 1)
                                    ->where('l.id', $item->loan_id)
                                    ->select(
                                        DB::raw("SUM(ld.late) as diasAtrasado"), DB::raw("SUM(ld.debt) as montoAtrasado")
                                    )
                                    ->first();
                    if($item->bg){
                        list($r, $g, $b) = sscanf($item->bg, "#%02x%02x%02x");
                    }
                @endphp
                @if ($view && ($day || $atras->montoAtrasado > 0))   
                    {{-- para agregarlo a la lista si corresponde    --}}
                    @php
                        $no_paga_hoy = false;
                        if($day){
                            $no_paga_hoy = !$day->payment_day;
                        }
                    @endphp
                    <tr style="text-align: center;@if($item->bg && $no_paga_hoy) background-color: rgba({{ $r }}, {{ $g }}, {{ $b }}, .4); @endif">
                        {{-- <tr style="text-align: center;"> --}}
                        <td>{{ $count }}</td>
                        <td style="text-align: center"><b>{{ $item->code}}</b></td>
                        <td style="text-align: left">{{ $item->last_name1}} {{ $item->last_name2}} {{ $item->first_name}}</td>
                        <td style="text-align: center">
                            @if ($item->cell_phone)
                                {{ $item->cell_phone }}
                            @elseif($item->phone)
                                {{ $item->phone }}
                            @endif
                        </td>
                        <td style="text-align: right"><b>{{ $item->amountLoan}}</b></td>
                        <td>
                            @php
                                $dias = App\Models\LoanDay::where('loan_id', $item->loan_id)->get();
                                $inicio = $dias->sortBy('date')->first()->date;
                                $fin = $dias->sortByDesc('date')->first()->date;
                            @endphp
                            @if (date('Y', strtotime($inicio)) == date('Y', strtotime($fin)))
                                {{ date('d', strtotime($inicio)) }}/{{ $months[intval(date('m', strtotime($inicio)))] }} al {{ date('d', strtotime($fin)) }}/{{ $months[intval(date('m', strtotime($fin)))] }} de {{ date('Y', strtotime($fin)) }}
                            @else
                                {{ date('d', strtotime($inicio)) }}/{{ $months[intval(date('m', strtotime($inicio)))] }}/{{ date('Y', strtotime($inicio)) }} al {{ date('d', strtotime($fin)) }}/{{ $months[intval(date('m', strtotime($fin)))] }}/{{ date('Y', strtotime($fin)) }}
                            @endif
                        </td>
                        <td style="text-align: right">
                            <b>
                                {{ $item->payments_period_id?number_format($amountPeriod,2,',','.'):($day? number_format($day->amount,2, ',', '.'):'SN') }}
                            </b>
                        </td>
                        <td>
                            @if($item->bg)
                                Paga {{ $item->payments_period_name }}
                            @endif
                        </td>
                        <td @if($atras->montoAtrasado > 0)                                     
                                @if ($atras->diasAtrasado > 0 && $atras->diasAtrasado <= 5)
                                    style="text-align: right; background-color: #F4DAD7" 
                                @endif
                                @if ($atras->diasAtrasado >= 6 && $atras->diasAtrasado <= 10)
                                    style="text-align: right; background-color: #EEAEA7" 
                                @endif
                                @if ($atras->diasAtrasado >= 11)
                                    style="text-align: right; background-color: #E1786C" 
                                @endif
                            @else 
                                style="text-align: right"
                            @endif>
                            {{ $item->payments_period_id?($cantPeriod?$cantPeriod:'SN'):($atras->diasAtrasado?$atras->diasAtrasado:'SN') }}
                        </td>
                        <td @if($atras->montoAtrasado > 0)                                     
                                @if ($atras->diasAtrasado > 0 && $atras->diasAtrasado <= 5)
                                    style="text-align: right; background-color: #F4DAD7" 
                                    @endif
                                    @if ($atras->diasAtrasado >= 6 && $atras->diasAtrasado <= 10)
                                        style="text-align: right; background-color: #EEAEA7" 
                                    @endif
                                    @if ($atras->diasAtrasado >= 11)
                                        style="text-align: right; background-color: #E1786C" 
                                    @endif
                                @else 
                                    style="text-align: right"
                                @endif>
                            {{$item->payments_period_id?number_format($amountPeriod,2,',','.'):($atras->montoAtrasado?number_format($atras->montoAtrasado,2,',','.'):'SN') }}
                        </td>
                    </tr>
                    @php
                        $count++;
                        $pago_atrasado += $item->payments_period_id?($amountPeriod?$amountPeriod:0):($atras->montoAtrasado?$atras->montoAtrasado:0);
                        if($item->payments_period_id){
                            $pago_diario += $amountPeriod;   
                        }
                        else {
                            if($day){
                                $pago_diario += $day->amount;                                
                                    
                                // if($day->payment_day){
                                //     $pago_diario += $day->amount;
                                // }else{
                                //     $pago_pospuestos += $day->amount;
                                // }
                            }
                        }
                    @endphp
                @endif

               
            @empty
                <tr style="text-align: center">
                    <td colspan="10">No se encontraron registros.</td>
                </tr>
            @endforelse
            <tr>
                <td colspan="6" style="text-align: right"><b>Monto del Día</b></td>
                <td style="text-align: right"><b>Bs. {{ number_format($pago_diario,2,',','.') }}</b></td>
                <td colspan="2" style="text-align: right"><b>Monto Atrasado</b></td>
                <td style="text-align: right"><b>Bs. {{ number_format($pago_atrasado,2,',','.') }}</b></td>
            </tr>
            <tr>
                <td colspan="9" style="text-align: right"><b>TOTAL POR COBRAR</b></td>
                <td style="text-align: right"><b>Bs. {{ number_format($pago_diario+$pago_atrasado,2,',','.') }}</b></td>
            </tr>
            {{-- <tr>
                <td colspan="8" style="text-align: right"><b>OTROS PAGOS</b></td>
                <td style="text-align: right"><b>Bs. {{ $pago_pospuestos }}</b></td>
            </tr> --}}
        </tbody>
    </table>

@endsection

@section('css')
    <style>
        table, th, td {
            border-collapse: collapse;
        }
    </style>
@stop