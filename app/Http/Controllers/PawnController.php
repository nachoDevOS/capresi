<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

// Models
use App\Models\ItemFeature;
use App\Models\PawnRegister;
use App\Models\PawnRegisterDetail;
use App\Models\PawnRegisterDetailFeature;
use App\Models\PawnRegisterPayment;
use App\Models\ItemType;
use DateTime;

// Queues
use App\Jobs\SendRecipe;
use App\Models\Cashier;
use App\Models\CashierMovement;
use App\Models\LoanDay;
use App\Models\PawnRegisterAmountAditional;
use App\Models\PawnRegisterMonth;
use App\Models\PawnRegisterMonthAgent;
use App\Models\SalaryPurchase;
use App\Models\SalaryPurchaseMonth;
use App\Models\Transaction;
use Illuminate\Support\Facades\Date;

class PawnController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->custom_authorize('browse_pawn');
        
        $this->ajax_verification($id=null);

        return view('pawn.browse');
    }

    public function list(){
        $this->custom_authorize('browse_pawn');
        $paginate = request('paginate') ?? 10;
        $search = request('search') ?? null;
        $status = request('status') ?? null;

        $status=='concluido'? $status="expiro":1;

        $data = PawnRegister::with(['person', 'user', 'details.type.category', 'details.features_list.feature', 'payments'])
                    ->where(function($query) use ($search){
                        if($search){
                            $query
                            ->OrwhereHas('person', function($query) use($search){
                                $query->whereRaw("(first_name like '%$search%' or last_name1 like '%$search%' or last_name2 like '%$search%' or ci like '%$search%' or phone like '%$search%' or CONCAT(first_name, ' ', last_name1, ' ', last_name2) like '%$search%')");
                            })
                            ->OrWhereHas('details.type', function($query) use($search){
                                $query->whereRaw($search ? 'name like "%'.$search.'%"' : 1);
                            })
                            ->OrWhereHas('details.type.category', function($query) use($search){
                                $query->whereRaw($search ? 'name like "%'.$search.'%"' : 1);
                            })
                            ->OrWhereHas('details.features_list', function($query) use($search){
                                $query->whereRaw($search ? 'value like "%'.$search.'%"' : 1);
                            })
                            ->OrWhereRaw($search ? "dateDelivered like '%$search%'" : 1)
                            ->OrWhereRaw($search ? "codeManual like '%$search%'" : 1)
                            ->OrWhereRaw($search ? "code like '%$search%'" : 1);
                        }
                    })
                    ->whereRaw($status ? " status = '$status'" : 1)
                    ->where('deleted_at', null)

                    ->orderBy('id', 'desc')
                    ->paginate($paginate);
        return view('pawn.list', compact('data'));
    }


    public function create()
    {
        $this->custom_authorize('add_pawn');
        return view('pawn.edit-add');
    }


    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // Registrar empeño
            $pawn_register = PawnRegister::create([
                'user_id' => Auth::user()->id,
                'person_id' => $request->people_id,
                'codeManual'=>$request->codeManual,
                'date' => $request->date,
                'cantMonth' => $request->date_limit_months,
                // 'date_limit' => date('Y-m-d', strtotime($request->date.' +'.$request->date_limit_months.' months')),
                // 'date_limit' => $date,
                'interest_rate' => $request->interest_rate,
                'observations' => $request->observations,
                'status' => $request->validate ? 'por validar' : 'pendiente',
                'endeavor'=>$request->endeavor,
                'amountTotal'=>$request->amountTotals,
                'dollarTotal'=>$request->dollarTotals,
                'dollarPrice'=>setting('configuracion.dollar')
            ]);

            $pawn_register->update(['code'=>'PP-'.str_pad($pawn_register->id, 5, "0", STR_PAD_LEFT)]);

            // Registrar items del empeño
            for ($i=0; $i < count($request->item_type_id); $i++) { 
                $detail = PawnRegisterDetail::create([
                    'pawn_register_id' => $pawn_register->id,
                    'item_type_id' => $request->item_type_id[$i],
                    'price' => $request->price[$i],
                    'quantity' => $request->quantity[$i] - $request->quantity_discount[$i] ?? 0,
                    'amountTotal' => $request->subtotal[$i],
                    'dollarTotal'=> $request->subtotal[$i]/setting('configuracion.dollar'),
                    'image' => isset($request->image[$i]) ? $this->store_image($request->image[$i], 'pawn_register', 1000) : null
                ]);

                // Registrar características de cada item
                if (isset($request->{'features_'.$i})) {
                    for ($j=0; $j < count($request->{'features_'.$i}); $j++) { 
                        PawnRegisterDetailFeature::create([
                            'pawn_register_detail_id' => $detail->id,
                            'title' => $request->{'features_'.$i}[$j] ,
                            // 'item_feature_id' => is_numeric($request->{'features_'.$i}[$j]) ? $request->{'features_'.$i}[$j] : ItemFeature::create(['item_category_id' => $item_type->item_category_id, 'name' => ucfirst($request->{'features_'.$i}[$j])])->id,
                            'value' => ucfirst($request->{'features_value_'.$i}[$j])
                        ]);
                    }
                }
            }
            DB::commit();
            return redirect()->route('pawn.index')->with(['message' => 'Registrado exitosamente', 'alert-type' => 'success', 'pawn_register_id' => $pawn_register->id]);            
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->route('pawn.index')->with(['message' => 'Ocurrió un error', 'alert-type' => 'error']);
        }
    }

    public function codePawn(Request $request, $pawn)
    {
        DB::beginTransaction();
        try {
            PawnRegister::where('id', $pawn)->update([
                'codeManual'=>$request->codeManual
            ]);
            DB::commit();
            return redirect()->route('pawn.index')->with(['message' => 'Registrado exitosamente', 'alert-type' => 'success']);            
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('pawn.index')->with(['message' => 'Ocurrió un error', 'alert-type' => 'error']);
        }
    }

    public function show($id)
    {
        $this->custom_authorize('read_pawn');

        $this->ajax_verification($id);

        $pawn = PawnRegister::with(['month', 'amountAditional'=>function($q){
                $q->with(['register'])
                ->where('deleted_at', null);
            }])
            ->where('id', $id)
            ->first();

        $transaction = PawnRegisterMonthAgent::with(['transaction', 'agent'])
            ->where('pawnRegister_id', $pawn->id)
            ->where('deleted_at', null)
            ->select('transaction_id', 'agent_id', 'agentType','deleted_at', DB::raw('sum(amount) as amount'))
            ->groupBy('transaction_id')
            ->orderBy('transaction_id', 'DESC')
            ->get();

        $amortization = PawnRegisterMonthAgent::where('pawnRegister_id', $pawn->id)
            ->where('deleted_at', null)
            ->whereRaw('pawnRegisterMonth_id is null')
            ->get()->sum('amount');

        return view('pawn.read', compact('pawn', 'transaction', 'amortization'));
    }

    public function pawnPyment(Request $request)
    {
        $global_cashier = $this->availableMoney(Auth::user()->id, 'user')->original;
        if(!$global_cashier['cashier']){
            return redirect()->route('pawn.show', $request->pawn)->with(['message' => 'Error, La caja no se encuentra abierta.', 'alert-type' => 'error']);
        }
        $cashier = $global_cashier['cashier'];
        $pawn = PawnRegister::with(['details', 'amountAditional'=>function($q){
                    $q->where('deleted_at', null);
                }
                ])
                ->where('id', $request->pawn)->first();
        DB::beginTransaction();
        try {
            $code = Transaction::all()->max('id');
            $code = $code?$code:0;

            $transaction = Transaction::create(['type'=>$request->payment_type, 'transaction'=>$code+1, 'category'=>'prestamos prenda']);
        
            $i = 1;
            $last = null;
            //Para pagar solos los meses de insteres
            while ($i <= ($request->months?count($request->months):0)) {

                $month = PawnRegisterMonth::where('id', $request->months[$i-1])->where('paid', 0)->where('debt', '>', 0)->first();

                $interesAux = $request->interest[$request->months[$i-1]];
                
                if($month)
                {
                    PawnRegisterMonthAgent::create([
                        'pawnRegisterMonth_id' => $month->id,
                        'pawnRegister_id'=>$pawn->id,
                        'cashier_id' => $cashier->id,
                        'transaction_id'=>$transaction->id,
                        'type'=>'Interes',
                        'amount' => $interesAux,
                        'agent_id' => Auth::user()->id,
                        'agentType' => Auth::user()->role->name,

                        'dollarTotal'=> $interesAux/setting('configuracion.dollar'),
                        'dollarPrice'=>setting('configuracion.dollar')
                    ]);
                    $month->update([
                        'paid'=>1,
                        'debt'=>0,

                        'interest'=>$interesAux,

                        'dollarTotal'=>$interesAux/setting('configuracion.dollar'),
                        'dollarPrice'=>setting('configuracion.dollar')
                    ]);
                    $last = $month;
                    
                    $pawn->update([
                        'status'=>'entregado'
                    ]);
                }
                $i++;
            }

                if($last)
                {
                    $aux=1;
                    $date = $pawn->date_limit;
                    while ($aux <= $i-1) { //Para agregar meses a la fecha de limite
                        $date = $this->month_next($date);
                        $aux++;
                    }
                    $pawn->update([
                        'date_limit'=>$date,
                    ]);

                    $p = PawnRegister::with(['month'=>function($q){
                        $q->where('deleted_at', null)
                        ->where('debt', '!=', 0);
                    }, 'details'])
                    ->where('id', $pawn->id)
                    ->where('deleted_at', null)
                    ->first();    

                    $cant = count($p->month);
                    if($cant <= $p->cantMonth)
                    {
                        $pawn->update([
                            'status'=>'entregado',
                        ]);
                    }
                }

            //para cuando se recojela prenda ya no se genera otro mes o para amortizar la prenda
            if($request->pawn_id)
            {
                $amortization = PawnRegisterMonthAgent::where('pawnRegister_id', $pawn->id)
                    ->where('deleted_at', null)
                    ->whereRaw('pawnRegisterMonth_id is null')
                    ->get()->sum('amount');

                if ($request->amountPawn < ($pawn->amountTotal + $pawn->amountAditional->sum('amountTotal') - $amortization)) {
                    PawnRegisterMonthAgent::create([
                        'pawnRegister_id'=>$pawn->id,
                        'cashier_id' => $cashier->id,
                        'transaction_id'=>$transaction->id,
                        'type'=>'Amortizacion',
                        'amount' => $request->amountPawn,
                        'agent_id' => Auth::user()->id,
                        'agentType' => Auth::user()->role->name,

                        'dollarTotal'=> $request->amountPawn/setting('configuracion.dollar'),
                        'dollarPrice'=>setting('configuracion.dollar')
                    ]);                    
                } else {
                    $month = PawnRegisterMonth::where('pawnRegister_id', $pawn->id)->where('paid', 0)->where('debt', '>', 0)->get();
                    if(count($month)>0)
                    {
                        DB::rollBack();
                        return redirect()->route('pawn.show', $pawn->id)->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
                    }
                    $pawn->update([
                        'status'=>'recogida'
                    ]);

                    PawnRegisterMonthAgent::create([
                        'pawnRegister_id'=>$pawn->id,
                        'cashier_id' => $cashier->id,
                        'transaction_id'=>$transaction->id,
                        'type'=>'Cancelacion',
                        'amount' => $request->amountPawn,
                        'agent_id' => Auth::user()->id,
                        'agentType' => Auth::user()->role->name,

                        'dollarTotal'=> $request->amountPawn/setting('configuracion.dollar'),
                        'dollarPrice'=>setting('configuracion.dollar')
                    ]);
                }
            }
            
            DB::commit();
            return redirect()->route('pawn.show', $pawn->id)->with(['message' => 'Pagado exitosamente.', 'alert-type' => 'success', 'pawn_id' => $pawn->id, 'transaction_id'=>$transaction->id]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('pawn.show', $pawn->id)->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function printTransaction($pawn_id, $transaction_id)
    {
        $transaction_id =$transaction_id;
        $pawn = PawnRegister::with(['person'])->where('id', $pawn_id)->first();

        $pawnMonthAgent = DB::table('pawn_register_month_agents as la')
            ->leftJoin('pawn_register_months as lm', 'lm.id', 'la.pawnRegisterMonth_id')
            ->join('users as u', 'u.id', 'la.agent_id')
            ->join('transactions as t', 't.id', 'la.transaction_id')
            ->where('la.pawnRegister_id', $pawn_id)
            ->where('t.id', $transaction_id)
            ->select('lm.id as prm_id', 'lm.start','lm.finish','la.pawnRegister_id', 'la.amount', 'u.name', 'la.agentType', 'la.id as monthAgent')
            ->get();
        
        // return $pawnMonthAgent;
        $transaction = Transaction::find($transaction_id);
        return view('pawn.print.print-transaction', compact('pawn', 'transaction', 'pawnMonthAgent'));
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            PawnRegister::where('id', $id)->update([
                'deleted_at' => Carbon::now()
            ]);

            $prd = PawnRegisterDetail::where('pawn_register_id', $id)->where('deleted_at', null)->get();
            foreach ($prd as $item) {
                $prdf = PawnRegisterDetailFeature::where('pawn_register_detail_id', $item->id)->where('deleted_at', null)->get();
                foreach ($prdf as $itemf) {
                    $itemf->update([
                        'deleted_at' => Carbon::now()
                    ]);
                }
                $item->update([
                    'deleted_at' => Carbon::now()
                ]);
            }
            DB::commit();
            return redirect()->route('pawn.index')->with(['message' => 'Anulado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('pawn.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function destroyAux($id)
    {
        return "error";
        DB::beginTransaction();
        try {
            // $aux = PawnRegister::where('id', $id)->first();
            // $m = CashierMovement::where('cashier_id', $aux->cashier_id)->first();
            // $m->increment('balance', $aux->amountTotal);

            PawnRegister::where('id', $id)->update([
                'deleted_at' => Carbon::now()
            ]);

            // $prd = PawnRegisterDetail::where('pawn_register_id', $id)->where('deleted_at', null)->get();
            // foreach ($prd as $item) {
            //     $prdf = PawnRegisterDetailFeature::where('pawn_register_detail_id', $item->id)->where('deleted_at', null)->get();
            //     foreach ($prdf as $itemf) {
            //         $itemf->update([
            //             'deleted_at' => Carbon::now()
            //         ]);
            //     }
            //     $item->update([
            //         'deleted_at' => Carbon::now()
            //     ]);
            // }
            DB::commit();
            return redirect()->route('pawn.index')->with(['message' => 'Anulado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('pawn.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }
    public function rechazar($id)
    {
        try {
            PawnRegister::where('id', $id)->update([
                'status' => 'rechazado',
            ]);
            return redirect()->route('pawn.index')->with(['message' => 'Rechazado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            return redirect()->route('pawn.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    //Para aprobar un prestamo el gerente
    public function successPawn($pawn)
    {
        DB::beginTransaction();
        try {
            $ok = PawnRegister::with(['person'])->where('id', $pawn)->first();
         
            PawnRegister::where('id', $pawn)->update([
                'status' => 'aprobado',
            ]);
            DB::commit();
            return redirect()->route('pawn.index')->with(['message' => 'Prestamo aprobado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('pawn.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function amountAditional(Request $request, $id)
    {
        $pawn = PawnRegister::where('deleted_at', null)
            ->where('id', $id)
            ->where('status', 'entregado')
            ->first();

        if(!$pawn){
            return redirect()->route('pawn.show', $id)->with(['message' => 'Error, El prestamos no se encuentra disponible.', 'alert-type' => 'error']);
        }

        $global_cashier = $this->availableMoney(Auth::user()->id, 'user')->original;
        if(!$global_cashier['cashier']){
            return redirect()->route('pawn.show', $id)->with(['message' => 'Error, La caja no se encuentra abierta.', 'alert-type' => 'error']);
        }

        if($global_cashier['amountCashier'] < $request->amountTotal)
        {
            return redirect()->route('voyager.dashboard')->with(['message' => 'No tiene suficiente dinero disponible.', 'alert-type' => 'warning']);
        }

        DB::beginTransaction();

        try {
            
            PawnRegisterAmountAditional::create([
                'pawnRegister_id' => $pawn->id,
                'cashier_id'=>$global_cashier['cashier']->id,
                'amountTotal'=>$request->amountTotal,
                'dollarTotal'=> $request->amountTotal/setting('configuracion.dollar'),
                'dollarPrice'=>setting('configuracion.dollar'),
                'description'=>$request->description,

                'registerUser_id'=>Auth::user()->id,
                'registerRole'=>Auth::user()->role->name,
            ]);

            DB::commit();
            return redirect()->route('pawn.show', $pawn->id)->with(['message' => 'Pagado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('pawn.show', $id)->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function moneyDeliver(Request $request, $pawn)
    {
        $pawnRegister = PawnRegister::with([
                'details'=>function($q){
                    $q->where('deleted_at', null);
                }
            ])
            ->where('id', $pawn)
            ->where('deleted_at', null)
            ->first();

        if($pawnRegister->status != 'aprobado')
        {
            return redirect()->route('pawn.index')->with(['message' => 'Error al realizar el registro', 'alert-type' => 'warning']);
        }

        $global_cashier = $this->availableMoney(Auth::user()->id, 'user')->original;
        if($global_cashier['amountCashier'] < $pawnRegister->amountTotal)
        {
            return redirect()->route('voyager.dashboard')->with(['message' => 'No tiene suficiente dinero disponible.', 'alert-type' => 'warning']);
        }



        DB::beginTransaction();
        try {
            $aux =1;
            $date = $request->date?$request->date:Carbon::now();
            while ($aux <= $pawnRegister->cantMonth) {
                $date = $this->month_next($date);
                $aux++;
            }

            $pawnRegister->update([
                'cashier_id'=>$global_cashier['cashier']->id,
                'date_limit'=>$date,
                'dateDelivered'=>$request->date?$request->date:Carbon::now(),
                'delivered_userId'=>Auth::user()->id,
                'delivered_userType' => Auth::user()->role->name,
                'status'=>'entregado'
            ]);   
           
            PawnRegisterMonth::create([
                    'pawnRegister_id' => $pawnRegister->id,
                    'start' => date('Y-m-d', strtotime($pawnRegister->dateDelivered.' + 1 days')) ,
                    'finish' => $this->month_next($pawnRegister->dateDelivered),
                    'interest' => $pawnRegister->amountTotal*($pawnRegister->interest_rate/100),
                    'debt' => $pawnRegister->amountTotal*($pawnRegister->interest_rate/100),
                ]);
            // sleep(2);
            DB::commit();
            return redirect()->route('pawn.index')->with(['message' => 'Transacción registrada exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('pawn.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }



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


    public function ajax_verification($id)
    {
        $date = date('Y-m-d');   
        // $date = '2025-04-13'; //comentar en produccion solo para prueba
        $pawn = PawnRegister::with(['month'=>function($q){
                $q->where('deleted_at', null)
                ->where('debt', '!=', 0);
            }, 'details',
            'amountAditional'=>function($q){
                $q->where('deleted_at', null);
            }
        ])
        ->whereIn('status', ['entregado', 'expiro'])
        ->whereRaw($id ? " id = '$id'" : 1)
        ->where('deleted_at', null)
        ->get();  
    
        foreach($pawn as $item)
        {
            $ok=true;
            
            while($ok)
            {
                $p = PawnRegister::with(['month'=>function($q){
                        $q->where('deleted_at', null);
                    }, 'details'])
                    ->where('id', $item->id)
                    ->where('deleted_at', null)->first();    

                $cant = count($p->month->where('debt', '!=', 0));

                $last = $p->month->last();
                $auxDate = new DateTime($last->finish);
                $auxDate = date('Y-m-d', strtotime($auxDate->format('Y-m-d').' + '.setting('configuracion.cantDayExpire').' days'));  

                if($cant == $p->cantMonth && $auxDate < $date)
                {
                    $p->update([
                        'status'=>'expiro'
                    ]);
                    $ok=false;
                }
                if($cant < $p->cantMonth && $auxDate < $date && $ok)
                {
                    $interest_rate = ($item->amountTotal + $item->amountAditional->sum('amountTotal')) * ($item->interest_rate /100);
                    PawnRegisterMonth::create([
                        'pawnRegister_id' => $item->id,
                        'start' => date('Y-m-d', strtotime($last->finish.' + 1 days')),
                        'finish' => $this->month_next($last->finish),
                        'interest' => $interest_rate,
                        'debt' => $interest_rate
                    ]);
                }
                else
                {
                    $ok=false;
                }
            }
        }
    }


    public function print($id){
        $pawn = PawnRegister::find($id);
        return view('pawn.print.contract', compact('pawn'));
    }

    public function printV($id){
        $pawn = PawnRegister::find($id);
        return view('pawn.print.contractV', compact('pawn'));
    }

    public function printVoucher($id)
    {
        $pawn = PawnRegister::with(['person', 'user', 'details.type.category', 'details.features_list.feature', 'payments'])
            ->where('id', $id)->first();
        return view('pawn.print.print-voucher', compact('pawn')) ;
    }

    



}