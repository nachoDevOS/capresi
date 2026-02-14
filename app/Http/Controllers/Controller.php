<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Cashier;
use App\Models\HistoryReportDailyList;
use App\Models\HistoryReportDailyListDetail;
use App\Models\Loan;
use App\Models\LoanDay;
use App\Models\PaymentsPeriod;
use App\Models\Route;
use App\Models\User;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use App\Http\Controllers\AjaxController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function custom_authorize($permission){
        if(!Auth::user()->hasPermission($permission)){
            abort(403, 'THIS ACTION IS UNAUTHORIZED.');
        }
    }
    

    // 
    // public function dailyList()
    // {
    //     DB::beginTransaction();
    //     try {
            
    //         $date = Carbon::now();

    //         $aux = HistoryReportDailyList::where('type', 'inicio')->whereDate('created_at', date("Y-m-d"))->get();
    //         if(count($aux)==0)
    //         {
    //             $routes = Route::with(['details'=>function($q){
    //                     $q->where('deleted_at', null)
    //                     ->where('status', 1);
    //                 }])
    //                 ->where('deleted_at', null)->get();
                
    //             foreach ($routes as $route) {
    //                 $history = HistoryReportDailyList::create(
    //                     [
    //                         'route_id'=>$route->id,
    //                         'agent_id'=>$route->details->first()->user_id??null,
    //                         'agentType'=>isset($route->details->first()->user_id)?Auth::user()->role->name:null,
    //                         'type'=>'inicio',
    //                         'dateTime'=>Carbon::now()
    //                     ]
    //                 );
    //                 $data = DB::table('loan_routes as lr')
    //                         ->join('loans as l', 'l.id', 'lr.loan_id')
    //                         ->join('people as p', 'p.id', 'l.people_id')
    //                         ->join('routes as r', 'r.id', 'lr.route_id')
    //                         ->leftJoin('payments_periods as pp', 'pp.id', 'l.payments_period_id')
    //                         ->where('l.deleted_at', null)
    //                         ->where('lr.deleted_at', null)
    //                         ->where('l.debt', '!=', 0)
    //                         ->where('l.status', 'entregado')
    //                         ->where('r.status', 1)
    //                         ->where('r.deleted_at', null)
    //                         ->where('lr.route_id', $route->id)
    //                         ->select('p.first_name', 'p.last_name1', 'last_name2', 'p.ci', 'l.code', 'l.dateDelivered', 'p.cell_phone', 'p.phone', 'p.street', 'p.home', 'p.zone',
    //                             'l.day', 'l.amountTotal', 'l.amountLoan', 'l.amountPorcentage', 'l.date', 'l.id as loan_id', 'l.payments_period_id', 'r.name as ruta', 'pp.color as bg', 'pp.name as payments_period_name', 'pp.days_quantity as payments_period_day'
    //                         )
    //                         ->orderBy('l.dateDelivered', 'ASC')
    //                         ->get();

    //                 foreach ($data as $item)
    //                 {
    //                     $view = true;
    //                     $amountPeriod=0;
    //                     $cantPeriod=0;
    //                     $loans = Loan::where('id', $item->loan_id)->first();

    //                     if ($item->payments_period_id)
    //                     {
    //                         $period = PaymentsPeriod::where('id', $item->payments_period_id)->first();
    //                         // $date = Illuminate\Support\Carbon::now();
    //                         $date = date("Y-m-d", strtotime($date));
    //                         $period->name=='Semanal'?$cant=7:$cant=15;

    //                         $ultPayment = LoanDay::where('loan_id', $item->loan_id)
    //                         ->where('date', function($query) use ($item) {
    //                             $query->selectRaw('MAX(date)')
    //                                 ->from('loan_days')
    //                                 ->where('loan_id', $item->loan_id); // Asegúrate de filtrar por loan_id
    //                         })->first();

    //                         $start = date("Y-m-d",strtotime($loans->dateDelivered."+ ".$cant." days"));
    //                         // Contador de domingos
    //                         $sundaysCount = 0;
    //                         $auxDate = $loans->dateDelivered;

    //                         while ($auxDate <= $start) {
    //                             $d = new DateTime($auxDate);
    //                             if ($d->format('w') == 0) { 
    //                                 $sundaysCount++;
    //                             }
    //                             $auxDate = date("Y-m-d",strtotime($auxDate."+ 1 days"));

    //                         }

    //                         $start = date("Y-m-d",strtotime($start."+ ".$sundaysCount." days"));

    //                         while($start<$date )
    //                         {
    //                             $loanDay = LoanDay::where('loan_id', $item->loan_id)
    //                                 ->where('debt', '>', 0)
    //                                 ->where('date','>=', $loans->dateDelivered)
    //                                 ->where('date','<=', $start)
    //                                 ->get();
                                
    //                             $sundaysCount = 0;
    //                             $auxDate = $start;
                                
    //                             $start = date("Y-m-d",strtotime($start."+ ".$cant." days"));

    //                             while ($auxDate <= $start) {
    //                                 $d = new DateTime($auxDate);
    //                                 if ($d->format('w') == 0) { 
    //                                     $sundaysCount++;
    //                                 }
    //                                 $auxDate = date("Y-m-d",strtotime($auxDate."+ 1 days"));

    //                             }

    //                             $start = date("Y-m-d",strtotime($start."+ ".$sundaysCount." days"));
                            
    //                             if($date > $ultPayment)
    //                             {
    //                                 $loanDay = LoanDay::where('loan_id', $item->loan_id)
    //                                 ->where('debt', '>', 0)
    //                                 ->where('date','>=', $loans->dateDelivered)
    //                                 ->where('date','<=', $date)
    //                                 ->get();
    //                             }
    //                             $amountPeriod = $loanDay->sum('debt');
    //                             $cantPeriod = $loanDay->count();                                  
    //                         }

    //                         $start = date("Y-m-d",strtotime($start."- ".$cant+$sundaysCount." days"));

    //                         if($amountPeriod == 0)
    //                         {
    //                             $view=false;
    //                         }
    //                     }

    //                     //Para obtener si tiene que pagar en el dia 
    //                     $day = DB::table('loans as l')
    //                                     ->join('loan_days as ld', 'ld.loan_id', 'l.id')
    //                                     ->where('l.id', $item->loan_id)
    //                                     ->where('l.deleted_at', null)
    //                                     ->where('ld.deleted_at', null)
    //                                     ->where('ld.debt', '!=', 0)
    //                                     ->whereDate('ld.date', date('Y-m-d', strtotime($date)))
    //                                     ->select('ld.debt', 'ld.amount', 'ld.payment_day')
    //                                     ->first();
    //                     //Para obtener los dias y la cantidad de los dias atrazados
    //                     $atras = DB::table('loans as l')
    //                                     ->join('loan_days as ld', 'ld.loan_id', 'l.id')
    //                                     ->where('l.deleted_at', null)
    //                                     ->where('ld.deleted_at', null)
    //                                     ->where('ld.debt', '!=', 0)
    //                                     ->where('ld.late', 1)
    //                                     ->where('l.id', $item->loan_id)
    //                                     ->select(
    //                                         DB::raw("SUM(ld.late) as diasAtrasado"), DB::raw("SUM(ld.debt) as montoAtrasado")
    //                                     )
    //                                     ->first();
    //                     if($item->bg){
    //                         list($r, $g, $b) = sscanf($item->bg, "#%02x%02x%02x");
    //                     }
                    
    //                 if ($view && ($day || $atras->montoAtrasado > 0))   
    //                 {
    //                     $no_paga_hoy = false;
    //                     if($day){
    //                             $no_paga_hoy = !$day->payment_day;
    //                     }
    //                     $color='#FFFFFF';

    //                     if($atras->montoAtrasado > 0)      
    //                     {                               
    //                         if($atras->diasAtrasado > 0 && $atras->diasAtrasado <= 5)
    //                         {
    //                             $color= '#F4DAD7';
    //                         }
    //                         if($atras->diasAtrasado >= 6 && $atras->diasAtrasado <= 10)
    //                         {
    //                             $color= '#EEAEA7';
    //                         }
    //                         if($atras->diasAtrasado >= 11)
    //                         {
    //                             $color= '#E1786C';
    //                         }
    //                     }
    //                     HistoryReportDailyListDetail::create(
    //                         [
    //                             'historyReport_id'=>$history->id,
    //                             'loan_id'=>$loans->id,
    //                             'dailyPayment'=>$item->payments_period_id?($amountPeriod?$amountPeriod:0):($day? $day->amount:0),
    //                             'typeLoan'=>$item->payments_period_name,
    //                             'lateDays'=>$item->payments_period_id?($cantPeriod?$cantPeriod:0):($atras->diasAtrasado?$atras->diasAtrasado:0),
    //                             'latePayment'=>$item->payments_period_id?($amountPeriod?$amountPeriod:0):($atras->montoAtrasado?($atras->montoAtrasado):0),
    //                             'color'=>$color
    //                         ]
    //                     );
    //                 }
    //             }
    //         }
            
            

    //         DB::commit();
    //     }
    //     } catch (\Throwable $th) {
    //         DB::rollBack();
    //     }
    // }
    
    // public function dailyListFinish()
    // {
    //     DB::beginTransaction();
    //     try {
    //         $date = Carbon::now();

    //         $aux = HistoryReportDailyList::where('type', 'fin')->whereDate('created_at', date("Y-m-d"))->get();
    //         if(count($aux)==0)
    //         {
    //             $routes = Route::with(['details'=>function($q){
    //                     $q->where('deleted_at', null)
    //                     ->where('status', 1);
    //                 }])
    //                 ->where('deleted_at', null)->get();
                
    //             foreach ($routes as $route) {
    //                 $history = HistoryReportDailyList::create(
    //                     [
    //                         'route_id'=>$route->id,
    //                         'agent_id'=>$route->details->first()->user_id??null,
    //                         'agentType'=>isset($route->details->first()->user_id)?Auth::user()->role->name:null,
    //                         'type'=>'fin',
    //                         'dateTime'=>Carbon::now()
    //                     ]
    //                 );
    //                 $data = DB::table('loan_routes as lr')
    //                         ->join('loans as l', 'l.id', 'lr.loan_id')
    //                         ->join('people as p', 'p.id', 'l.people_id')
    //                         ->join('routes as r', 'r.id', 'lr.route_id')
    //                         ->leftJoin('payments_periods as pp', 'pp.id', 'l.payments_period_id')
    //                         ->where('l.deleted_at', null)
    //                         ->where('lr.deleted_at', null)
    //                         ->where('l.debt', '!=', 0)
    //                         ->where('l.status', 'entregado')
    //                         ->where('r.status', 1)
    //                         ->where('r.deleted_at', null)
    //                         ->where('lr.route_id', $route->id)
    //                         ->select('p.first_name', 'p.last_name1', 'last_name2', 'p.ci', 'l.code', 'l.dateDelivered', 'p.cell_phone', 'p.phone', 'p.street', 'p.home', 'p.zone',
    //                             'l.day', 'l.amountTotal', 'l.amountLoan', 'l.amountPorcentage', 'l.date', 'l.id as loan_id', 'l.payments_period_id', 'r.name as ruta', 'pp.color as bg', 'pp.name as payments_period_name', 'pp.days_quantity as payments_period_day'
    //                         )
    //                         ->orderBy('l.dateDelivered', 'ASC')
    //                         ->get();


    //                 foreach ($data as $item)
    //                 {
    //                     $view = true;
    //                     $amountPeriod=0;
    //                     $cantPeriod=0;
    //                     $loans = Loan::where('id', $item->loan_id)->first();

    //                     if ($item->payments_period_id)
    //                     {
    //                         $period = PaymentsPeriod::where('id', $item->payments_period_id)->first();
    //                         // $date = Illuminate\Support\Carbon::now();
    //                         $date = date("Y-m-d", strtotime($date));
    //                         $period->name=='Semanal'?$cant=7:$cant=15;

    //                         $ultPayment = LoanDay::where('loan_id', $item->loan_id)
    //                         ->where('date', function($query) use ($item) {
    //                             $query->selectRaw('MAX(date)')
    //                                 ->from('loan_days')
    //                                 ->where('loan_id', $item->loan_id); // Asegúrate de filtrar por loan_id
    //                         })->first();

    //                         $start = date("Y-m-d",strtotime($loans->dateDelivered."+ ".$cant." days"));
    //                         // Contador de domingos
    //                         $sundaysCount = 0;
    //                         $auxDate = $loans->dateDelivered;

    //                         while ($auxDate <= $start) {
    //                             $d = new DateTime($auxDate);
    //                             if ($d->format('w') == 0) { 
    //                                 $sundaysCount++;
    //                             }
    //                             $auxDate = date("Y-m-d",strtotime($auxDate."+ 1 days"));

    //                         }

    //                         $start = date("Y-m-d",strtotime($start."+ ".$sundaysCount." days"));

    //                         while($start<$date )
    //                         {
    //                             $loanDay = LoanDay::where('loan_id', $item->loan_id)
    //                                 ->where('debt', '>', 0)
    //                                 ->where('date','>=', $loans->dateDelivered)
    //                                 ->where('date','<=', $start)
    //                                 ->get();
    //                             $sundaysCount = 0;
    //                             $auxDate = $start;
                                    
    //                             $start = date("Y-m-d",strtotime($start."+ ".$cant." days"));

    //                             while ($auxDate <= $start) {
    //                                 $d = new DateTime($auxDate);
    //                                 if ($d->format('w') == 0) { 
    //                                     $sundaysCount++;
    //                                 }
    //                                 $auxDate = date("Y-m-d",strtotime($auxDate."+ 1 days"));
    //                             }
    //                             $start = date("Y-m-d",strtotime($start."+ ".$sundaysCount." days"));
                            
    //                             if($date > $ultPayment)
    //                             {
    //                                 $loanDay = LoanDay::where('loan_id', $item->loan_id)
    //                                 ->where('debt', '>', 0)
    //                                 ->where('date','>=', $loans->dateDelivered)
    //                                 ->where('date','<=', $date)
    //                                 ->get();
    //                             }
    //                             $amountPeriod = $loanDay->sum('debt');
    //                             $cantPeriod = $loanDay->count();
    //                         }

    //                         $start = date("Y-m-d",strtotime($start."- ".$cant+$sundaysCount." days"));

    //                         if($amountPeriod == 0)
    //                         {
    //                             $view=false;
    //                         }
    //                     }

    //                     //Para obtener si tiene que pagar en el dia 
    //                     $day = DB::table('loans as l')
    //                                     ->join('loan_days as ld', 'ld.loan_id', 'l.id')
    //                                     ->where('l.id', $item->loan_id)
    //                                     ->where('l.deleted_at', null)
    //                                     ->where('ld.deleted_at', null)
    //                                     ->where('ld.debt', '!=', 0)
    //                                     ->whereDate('ld.date', date('Y-m-d', strtotime($date)))
    //                                     ->select('ld.debt', 'ld.amount', 'ld.payment_day')
    //                                     ->first();
    //                     //Para obtener los dias y la cantidad de los dias atrazados
    //                     $atras = DB::table('loans as l')
    //                                     ->join('loan_days as ld', 'ld.loan_id', 'l.id')
    //                                     ->where('l.deleted_at', null)
    //                                     ->where('ld.deleted_at', null)
    //                                     ->where('ld.debt', '!=', 0)
    //                                     ->where('ld.late', 1)
    //                                     ->where('l.id', $item->loan_id)
    //                                     ->select(
    //                                         DB::raw("SUM(ld.late) as diasAtrasado"), DB::raw("SUM(ld.debt) as montoAtrasado")
    //                                     )
    //                                     ->first();
    //                     if($item->bg){
    //                         list($r, $g, $b) = sscanf($item->bg, "#%02x%02x%02x");
    //                     }
                    
    //                 if ($view && ($day || $atras->montoAtrasado > 0))   
    //                 {
    //                     $no_paga_hoy = false;
    //                     if($day){
    //                             $no_paga_hoy = !$day->payment_day;
    //                     }
    //                     $color='#FFFFFF';

    //                     if($atras->montoAtrasado > 0)      
    //                     {                               
    //                         if($atras->diasAtrasado > 0 && $atras->diasAtrasado <= 5)
    //                         {
    //                             $color= '#F4DAD7';
    //                         }
    //                         if($atras->diasAtrasado >= 6 && $atras->diasAtrasado <= 10)
    //                         {
    //                             $color= '#EEAEA7';
    //                         }
    //                         if($atras->diasAtrasado >= 11)
    //                         {
    //                             $color= '#E1786C';
    //                         }
    //                     }
    //                     HistoryReportDailyListDetail::create(
    //                         [
    //                             'historyReport_id'=>$history->id,
    //                             'loan_id'=>$loans->id,
    //                             'dailyPayment'=>$item->payments_period_id?($amountPeriod?$amountPeriod:0):($day? $day->amount:0),
    //                             'typeLoan'=>$item->payments_period_name,
    //                             'lateDays'=>$item->payments_period_id?($cantPeriod?$cantPeriod:0):($atras->diasAtrasado?$atras->diasAtrasado:0),
    //                             'latePayment'=>$item->payments_period_id?($amountPeriod?$amountPeriod:0):($atras->montoAtrasado?($atras->montoAtrasado):0),
    //                             'color'=>$color
    //                         ]
    //                     );
    //                 }
    //             }
    //         }
            

    //         DB::commit();
    //     }
    //     } catch (\Throwable $th) {
    //         DB::rollBack();
    //     }
    // }
    

    // public function agent($id)
    // {
    //     return DB::table('users as u')
    //         ->join('roles as r', 'r.id', 'u.role_id')
    //         ->where('u.id', $id)
    //         ->select('u.id', 'u.name', 'r.name as role')
    //         ->first();
    // }


    // Funcion para ver la caja en estado abierta
    public function cashierOpen()
    {
        return Cashier::with(['movements' => function($q){
            $q->where('deleted_at', NULL);
        }])
        ->where('user_id', Auth::user()->id)
        ->where('status', 'abierta')
        ->where('deleted_at', NULL)->first();

    }

    public function cashierOpenAmountBalance($user_id)
    {
        $cashier = Cashier::with(['movements' => function($q){
            $q->where('deleted_at', NULL);
        }])
            // ->where('user_id', Auth::user()->id)
            ->where('user_id', $user_id)
            ->where('status', 'abierta')
            ->where('deleted_at', NULL)->first();
        $balance = 0;
        if($cashier)
        {
            $cashier_id = $cashier->id;
            $balance = $cashier->movements->where('type', 'ingreso')->where('deleted_at', NULL)->sum('balance') - $cashier->movements->where('type', 'egreso')->where('deleted_at', NULL)->sum('amount');
        }

        return response()->json([
            'return' => $cashier?true:false,
            'cashier_id' => $cashier?$cashier:null,
            'balance' => $balance,
        ]);
    }


    

    //Para obtener el detalle de cualquier caja y en cualquier estado que no se encuentre eliminada (id de la caja, Estado de la caja)
    public function cashierId($id, $status)
    {
        return Cashier::with([
            'movements',
            // 'details' => function($q){
            //     $q->where('deleted_at', NULL);
            // },
            'loan_payments' => function($q){                
                $q->whereHas('transaction', function($q) {
                    $q->whereIn('type', ['Efectivo', 'Qr']);
                })
                ->with(['loanDay.loan.people', 'agent']);
            },
            'loans' => function($q){
                $q->with(['people'])
                ->where('status', 'entregado');
            },
            'pawn' => function($q){
                $q->with(['person', 'details.featuresLists', 'details.type', 'user']); // Cargar la relación 'people' dentro de 'pawn'
            },            
            'pawnMoneyAditional' => function($q){          //Para los aumento en algunos prestamos     
                $q->with(['pawnRegister.person']);
            },
            'pawnPayment' => function($q) {
                $q->whereHas('transaction', function($q) {
                    $q->whereIn('type', ['Efectivo', 'Qr']);
                })
                ->with(['pawnRegister.person', 'agent']);
            },
            'salePayment' => function($q) {
                $q->whereHas('transaction', function($q) {
                        $q->whereIn('type', ['Efectivo', 'Qr']);
                    })
                    ->with(['sale.person', 'register']);
            },

            'salaryPurchase' => function($q){ //Para obtener los prestamos que se leentregan a los maestros
                $q->with(['person']);
            },

            'salaryPurchasePayment' => function($q) {
                $q->whereHas('transaction', function($q) {
                    $q->whereIn('type', ['Efectivo', 'Qr']);
                })
                ->with(['salaryPurchase.person', 'agent']);
            },

            // relaciones adicionales
            'user'
        ])
        ->where('id', $id)
        ->where('deleted_at', null)
        ->whereRaw($status?'status = "'.$status.'"':1)
        ->first();        
    }



    //Para obtener el Total de Dinero disponible en caja abierta mediante el ID del usuario de la caja que le corresponda
    public function availableMoney($id, $type)
    {
        if ($type == 'user') {
            $cashier = Cashier::where('user_id', $id)
                ->where('deleted_at', null)
                ->where('status', 'abierta')
                ->first();
        } else {
            $cashier = Cashier::where('id', $id)
                ->where('deleted_at', null)
                ->where('status', 'abierta')
                ->first();
        }
        

        if ($cashier) {

            $cashier = $this->cashierId($cashier->id, 'abierta');
            //Dinero asignado a caja
            $cashierIn = $cashier->movements->where('type', 'ingreso')->where('deleted_at', NULL)->where('status', 'Aceptado')->sum('amount');

            //gastos adicionales y transacciones a otras cajas
            $cashierOut = $cashier->movements->where('type', 'egreso')->where('deleted_at', NULL)->where('status','!=', 'Rechazado')->sum('amount');

            //::::::::::::Ingresos::::::::::
            $loanPaymentEfectivo = $cashier->loan_payments->where('deleted_at', NULL)->where('transaction.type', 'Efectivo')->sum('amount');
            $loanPaymentQr = $cashier->loan_payments->where('deleted_at', NULL)->where('transaction.type', 'Qr')->sum('amount');

            $pawnPaymentEfectivo = $cashier->pawnPayment->where('deleted_at', NULL)->where('transaction.type', 'Efectivo')->sum('amount');
            $pawnPaymentQr = $cashier->pawnPayment->where('deleted_at', NULL)->where('transaction.type', 'Qr')->sum('amount');

            $salaryPurchasePaymentEfectivo = $cashier->salaryPurchasePayment->where('deleted_at', NULL)->where('transaction.type', 'Efectivo')->sum('amount');
            $salaryPurchasePaymentQr = $cashier->salaryPurchasePayment->where('deleted_at', NULL)->where('transaction.type', 'Qr')->sum('amount');

            $salePaymentEfectivo = $cashier->salePayment->where('deleted_at', NULL)->where('transaction.type', 'Efectivo')->sum('amount');
            $salePaymentQr = $cashier->salePayment ->where('deleted_at', NULL)->where('transaction.type', 'Qr')->sum('amount');
            //::::::::::::::::::::::::::::::

                                
            $loans = $cashier->loans->where('deleted_at', NULL)->sum('amountLoan');
            $pawns = $cashier->pawn->where('deleted_at', NULL)->sum('amountTotal');
            $salaryPurchase = $cashier->salaryPurchase->where('deleted_at', NULL)->sum('amount');
            $pawnsMoneyAditional = $cashier->pawnMoneyAditional->where('deleted_at', NULL)->sum('amountTotal');


            //Egreso prestamos y gastos adicionales
            $amountEgres = $loans + $pawns + $pawnsMoneyAditional+$salaryPurchase;

            //Para obtener en efectivo 
            $amountEfectivo = $loanPaymentEfectivo + $pawnPaymentEfectivo + $salePaymentEfectivo + $salaryPurchasePaymentEfectivo;
            $amountQr = $loanPaymentQr + $pawnPaymentQr + $salePaymentQr + $salaryPurchasePaymentQr;

            $amountCashier = ($cashierIn + $loanPaymentEfectivo + $pawnPaymentEfectivo + $salePaymentEfectivo + $salaryPurchasePaymentEfectivo) - $cashierOut  - $loans -$pawns - $pawnsMoneyAditional-$salaryPurchase;
        }

        return response()->json([
            'return' => $cashier?true:false,
            'cashier' => $cashier?$cashier:null,
            // datos en valores
            'amountEfectivo' => $cashier?$amountEfectivo:null,//Para obtener el total de dinero en efectivo recaudado en general
            'amountQr' => $cashier?$amountQr:null, //Para obtener el total de dinero en QR recaudado en general
            'amountCashier'=>$cashier?$amountCashier:null, //dinero disponible en caja para su uso 'solo dinero que hay en la caja disponible y cobro solo en efectivos'

            'amountEgres' =>$cashier?$amountEgres:null, // dinero prestado de prenda y diario
            'cashierOut'=>$cashier?$cashierOut:null, //Gastos Adicionales

            'cashierIn'=>$cashier?$cashierIn:null// Dinero total abonado a las cajas
        ]);
    }


    // Para generar el mes correspondiente de interes
    public function month_next($date)
    {
        // $date = '2025-01-29';

        $finish = date('Y-m-d', strtotime($date.' + 1 months'));  //Para sumarle un mes

        $start = new DateTime($date);
        $finish = new DateTime($finish);

        $diferencia = $start->diff($finish);
        if($diferencia->days < 30)
        {
            $aux = 30-$diferencia->days;
            $finish = date('Y-m-d', strtotime($finish->format('Y-m-d').' + '.$aux.' days'));  
        }
        else
        {
            if(date('d', strtotime($start->format('Y-m-d')))==31 && date('m', strtotime($start->format('Y-m-d')))!=1)//si esta en fecha 31 del mes, menos el mes de enero
            {
                $p = date('Y-m-d', strtotime($start->format('Y-m-1').' + 1 months'));  //Para sumarle un mes

                $year = date('Y', strtotime($start->format('Y-m-1').' + 1 months'));  //Para sumarle un mes
                $month = date('m', strtotime($start->format('Y-m-1').' + 1 months'));  //Para sumarle un mes
                $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);

                if($days == 30)
                {
                    // $finish = ''.$p->format('Y').'-'.$p->format('m').'-30';
                    $finish = date('Y-m-d', strtotime($p.' + 29 days'));  //Para sumarle un mes
                }
                else
                {
                    // $finish = ''.$p->format('Y').'-'.$p->format('m').'-31';
                    $finish = date('Y-m-d', strtotime($p.' + 30 days'));  //Para sumarle un mes
                }
            }
            else
            {
                if(date('d', strtotime($start->format('Y-m-d')))==31 && date('m', strtotime($start->format('Y-m-d')))==1)//si esta en fecha 31 del mes, mes de enero
                {
                    $finish = date('Y-m-d', strtotime($finish->format('Y-m-d').' - 1 days'));  
                }
                else
                {
                    $finish = $finish->format('Y-m-d');
                }
            }
        }
        return $finish;
    }

    


    public function store_image($file, $folder, $size = 512){
        try {
            Storage::makeDirectory($folder.'/'.date('F').date('Y'));
            $base_name = Str::random(20);

            // imagen normal
            $filename = $base_name.'.'.$file->getClientOriginalExtension();
            $image_resize = Image::make($file->getRealPath())->orientate();
            $image_resize->resize($size, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $path =  $folder.'/'.date('F').date('Y').'/'.$filename;
            $image_resize->save(public_path('../storage/app/public/'.$path));

            // imagen cuadrada
            $filename_small = $base_name.'-cropped.'.$file->getClientOriginalExtension();
            $image_resize = Image::make($file->getRealPath())->orientate();
            $image_resize->resize(null, 256, function ($constraint) {
                $constraint->aspectRatio();
            });
            
            $image_resize->resizeCanvas(256, 256);
            $path_small = "$folder/".date('F').date('Y').'/'.$filename_small;
            $image_resize->save(public_path('../storage/app/public/'.$path_small));

            return $path;
        } catch (\Throwable $th) {
            return null;
        }
    }






}