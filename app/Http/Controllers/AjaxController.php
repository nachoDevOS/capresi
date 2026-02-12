<?php

namespace App\Http\Controllers;

use App\Jobs\SendNotification;
use App\Jobs\SendRecipe;
use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\LoanDay;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use App\Models\Cashier;
use App\Models\CashierMovement;
use App\Models\Garment;
use App\Models\GarmentsMonth;
use App\Models\HistoryReportDailyList;
use App\Models\HistoryReportDailyListDetail;
use App\Models\Notification;
use App\Models\PaymentsPeriod;
use App\Models\Route;
use DateTime;
use Illuminate\Support\Facades\Auth;

class AjaxController extends Controller
{
    // para poner en retrazado los dias de pagos de forma automatica
    public function dayLate()
    {
        $date = date('Y-m-d');
        $data = LoanDay::where('deleted_at', null)
            ->where('deleted_at', null)
            ->where('debt', '>', 0)
            ->where('late', 0)
            ->where('date', '<', $date)
            ->get();

        foreach ($data as $item) {
            $item->update(['late' => 1]);
        }

        $loans = Loan::with([
                    'loanDay' => function ($q) {
                        $q->where('deleted_at', null)
                        ->where('status', 1)
                        ->where('debt', '!=', 0);
                    },
                ])
                ->where('status', 'entregado')
                ->where('debt', '!=', 0)
                ->where('deleted_at', null)
                ->where('mora', 0)
                ->get();

        foreach ($loans as $loan) {
            $mora = false;
            foreach ($loan->loanDay as $item) {
                if ($date > $item->date) {
                    $mora = true;
                }
            }
            if ($mora) {
                $loan->update(['mora' => 1]);
            }
        }

        return response()->json(['status' => 'success', 'message' => 'Dias atrazados generado correctamente']); // ← RETORNO SUCCESS
    }
    

    // Para Generar las notificaciones atrazadas del dia anterior de los clientes que no han pagado
    public function notificationLate()
    {
        $data = DB::table('loans as l')
            ->join('loan_days as ld', 'ld.loan_id', 'l.id')
            ->join('people as p', 'p.id', 'l.people_id')
            ->where('l.deleted_at', null)
            ->where('l.notification', 'si')
            ->where('ld.late', 1)
            // ->where('p.cell_phone', '=', null)
            ->where('ld.debt', '>', 0)
            ->where('l.notificationDate', '<', date('Y-m-d'))
            ->select('l.id as loan', 'l.notificationDate', 'l.dateDelivered', 'p.id as people', 'p.first_name', 'p.last_name1', 'p.last_name2', 'p.cell_phone', 'p.ci', 'l.code', 'ld.id as day', DB::raw('COUNT(day) as cant'), 'ld.debt as amount', DB::raw('SUM(amount) as amount'))
            ->groupBy('loan')
            // ->limit(5)
            ->get();
        foreach ($data as $item) {
            SendNotification::dispatch($item);
        }
    }

    // Para generar un historial de lista diaria desde el inicio del dia hasta finalizar el dia
    public function dailyListHistory()
    {
        DB::beginTransaction();
        try {
            $date = Carbon::now();
            // $aux = HistoryReportDailyList::where('type', 'inicio')->whereDate('created_at', date("Y-m-d"))->get();
            $aux = HistoryReportDailyList::whereDate('created_at', date('Y-m-d'))->get();
           
            $routes = Route::with([
                    'details' => function ($q) {
                        $q->where('deleted_at', null)->where('status', 1);
                    },
                ])
                ->where('deleted_at', null)
                ->get();
            // return $routes;

            foreach ($routes as $route) {
                $history = HistoryReportDailyList::create([
                    'route_id' => $route->id,
                    // 'agent_id' => $route->details->first()->user_id ?? null,
                    // 'agentType' => isset($route->details->first()->user_id) ? Auth::user()->role->name : null,
                    'type' => count($aux) == 0 ? 'inicio' : 'fin',
                    'dateTime' => Carbon::now(),
                ]);
                $data = DB::table('loan_routes as lr')->join('loans as l', 'l.id', 'lr.loan_id')->join('people as p', 'p.id', 'l.people_id')->join('routes as r', 'r.id', 'lr.route_id')->leftJoin('payments_periods as pp', 'pp.id', 'l.payments_period_id')->where('l.deleted_at', null)->where('lr.deleted_at', null)->where('l.debt', '!=', 0)->where('l.status', 'entregado')->where('r.status', 1)->where('r.deleted_at', null)->where('lr.route_id', $route->id)->select('p.first_name', 'p.last_name1', 'last_name2', 'p.ci', 'l.code', 'l.dateDelivered', 'p.cell_phone', 'p.phone', 'p.street', 'p.home', 'p.zone', 'l.day', 'l.amountTotal', 'l.amountLoan', 'l.amountPorcentage', 'l.date', 'l.id as loan_id', 'l.payments_period_id', 'r.name as ruta', 'pp.color as bg', 'pp.name as payments_period_name', 'pp.days_quantity as payments_period_day')->orderBy('l.dateDelivered', 'ASC')->get();

                foreach ($data as $item) {
                    $view = true;
                    $amountPeriod = 0;
                    $cantPeriod = 0;
                    $loans = Loan::where('id', $item->loan_id)->first();

                    if ($item->payments_period_id) {
                        $period = PaymentsPeriod::where('id', $item->payments_period_id)->first();
                        // $date = Illuminate\Support\Carbon::now();
                        $date = date('Y-m-d', strtotime($date));
                        $period->name == 'Semanal' ? ($cant = 7) : ($cant = 15);

                        $ultPayment = LoanDay::where('loan_id', $item->loan_id)
                            ->where('date', function ($query) use ($item) {
                                $query->selectRaw('MAX(date)')->from('loan_days')->where('loan_id', $item->loan_id); // Asegúrate de filtrar por loan_id
                            })
                            ->first();

                        $start = date('Y-m-d', strtotime($loans->dateDelivered . '+ ' . $cant . ' days'));
                        // Contador de domingos
                        $sundaysCount = 0;
                        $auxDate = $loans->dateDelivered;

                        while ($auxDate <= $start) {
                            $d = new DateTime($auxDate);
                            if ($d->format('w') == 0) {
                                $sundaysCount++;
                            }
                            $auxDate = date('Y-m-d', strtotime($auxDate . '+ 1 days'));
                        }

                        $start = date('Y-m-d', strtotime($start . '+ ' . $sundaysCount . ' days'));

                        while ($start < $date) {
                            $loanDay = LoanDay::where('loan_id', $item->loan_id)->where('debt', '>', 0)->where('date', '>=', $loans->dateDelivered)->where('date', '<=', $start)->get();

                            $sundaysCount = 0;
                            $auxDate = $start;

                            $start = date('Y-m-d', strtotime($start . '+ ' . $cant . ' days'));

                            while ($auxDate <= $start) {
                                $d = new DateTime($auxDate);
                                if ($d->format('w') == 0) {
                                    $sundaysCount++;
                                }
                                $auxDate = date('Y-m-d', strtotime($auxDate . '+ 1 days'));
                            }

                            $start = date('Y-m-d', strtotime($start . '+ ' . $sundaysCount . ' days'));

                            if ($date > $ultPayment) {
                                $loanDay = LoanDay::where('loan_id', $item->loan_id)->where('debt', '>', 0)->where('date', '>=', $loans->dateDelivered)->where('date', '<=', $date)->get();
                            }
                            $amountPeriod = $loanDay->sum('debt');
                            $cantPeriod = $loanDay->count();
                        }

                        $start = date('Y-m-d', strtotime($start . '- ' . $cant + $sundaysCount . ' days'));

                        if ($amountPeriod == 0) {
                            $view = false;
                        }
                    }

                    //Para obtener si tiene que pagar en el dia
                    $day = DB::table('loans as l')
                        ->join('loan_days as ld', 'ld.loan_id', 'l.id')
                        ->where('l.id', $item->loan_id)
                        ->where('l.deleted_at', null)
                        ->where('ld.deleted_at', null)
                        ->where('ld.debt', '!=', 0)
                        ->whereDate('ld.date', date('Y-m-d', strtotime($date)))
                        ->select('ld.debt', 'ld.amount', 'ld.payment_day')
                        ->first();
                    //Para obtener los dias y la cantidad de los dias atrazados
                    $atras = DB::table('loans as l')->join('loan_days as ld', 'ld.loan_id', 'l.id')->where('l.deleted_at', null)->where('ld.deleted_at', null)->where('ld.debt', '!=', 0)->where('ld.late', 1)->where('l.id', $item->loan_id)->select(DB::raw('SUM(ld.late) as diasAtrasado'), DB::raw('SUM(ld.debt) as montoAtrasado'))->first();
                    if ($item->bg) {
                        [$r, $g, $b] = sscanf($item->bg, '#%02x%02x%02x');
                    }

                    if ($view && ($day || $atras->montoAtrasado > 0)) {
                        $no_paga_hoy = false;
                        if ($day) {
                            $no_paga_hoy = !$day->payment_day;
                        }
                        $color = '#FFFFFF';

                        if ($atras->montoAtrasado > 0) {
                            if ($atras->diasAtrasado > 0 && $atras->diasAtrasado <= 5) {
                                $color = '#F4DAD7';
                            }
                            if ($atras->diasAtrasado >= 6 && $atras->diasAtrasado <= 10) {
                                $color = '#EEAEA7';
                            }
                            if ($atras->diasAtrasado >= 11) {
                                $color = '#E1786C';
                            }
                        }

                        HistoryReportDailyListDetail::create([
                            'historyReport_id' => $history->id,
                            'loan_id' => $loans->id,
                            'dailyPayment' => $item->payments_period_id ? ($amountPeriod ? $amountPeriod : 0) : ($day ? $day->amount : 0),
                            'typeLoan' => $item->payments_period_name,
                            'lateDays' => $item->payments_period_id ? ($cantPeriod ? $cantPeriod : 0) : ($atras->diasAtrasado ? $atras->diasAtrasado : 0),
                            'latePayment' => $item->payments_period_id ? ($amountPeriod ? $amountPeriod : 0) : ($atras->montoAtrasado ? $atras->montoAtrasado : 0),
                            'color' => $color,
                        ]);
                    }
                }                
            }
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Lista diaria generada correctamente']); // ← RETORNO SUCCESS
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Error al generar lista diaria: ' . $th->getMessage()]); // ← RETORNO ERROR
        }
    }

}
