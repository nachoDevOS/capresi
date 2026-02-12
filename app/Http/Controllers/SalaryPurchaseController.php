<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\SalaryPurchase;
use App\Models\SalaryPurchaseMonth;
use App\Models\SalaryPurchaseMonthAgent;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use DateTime;

class SalaryPurchaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->custom_authorize('browse_salary_purchases');
        $this->ajax_verification($id=null);

        return view('salaryPurchases.browse');
    }

    public function list(){
        $paginate = request('paginate') ?? 10;
        $search = request('search') ?? null;
        $status = request('status') ?? null;

        // $status=='concluido'? $status="expiro":1;

        $data = SalaryPurchase::with(['person'])
                    ->where(function($query) use ($search){
                        if($search){
                            $query
                            ->OrwhereHas('person', function($query) use($search){
                                $query->whereRaw("(first_name like '%$search%' or last_name1 like '%$search%' or last_name2 like '%$search%' or ci like '%$search%' or phone like '%$search%' or CONCAT(first_name, ' ', last_name1, ' ', last_name2) like '%$search%')");
                            })
                            ->OrWhereRaw($search ? "code like '%$search%'" : 1);
                        }
                    })
                    ->whereRaw($status ? " status = '$status'" : 1)
                    ->where('deleted_at', null)

                    ->orderBy('id', 'desc')
                    ->paginate($paginate);
        // dump($data);
        // return $data;
        return view('salaryPurchases.list', compact('data'));
    }
    public function show($id)
    {
        $salary = SalaryPurchase::with(['person', 'register', 'salaryPurchaseMonths'=>function($q)
            {
                $q->where('deleted_at', null);
            }])
            ->where('id', $id)->first();
        $transaction = SalaryPurchaseMonthAgent::with(['transaction', 'agent'])
            ->where('salaryPurchase_id', $salary->id)
            ->where('deleted_at', null)
            ->select('transaction_id', 'agent_id', 'agentType','deleted_at', DB::raw('sum(amount) as amount'))
            ->groupBy('transaction_id')
            ->orderBy('transaction_id', 'DESC')
            ->get();
        $amortization = SalaryPurchaseMonthAgent::where('salaryPurchase_id', $salary->id)
            ->where('deleted_at', null)
            ->whereRaw('salaryPurchaseMonth_id is null')
            ->get()->sum('amount');
        
        return view('salaryPurchases.read', compact('salary', 'transaction', 'amortization'));
    }

    public function create()
    {
        $this->custom_authorize('add_salary_purchases');
        return view('salaryPurchases.edit-add');
    }

    public function store(Request $request)
    {
        $this->custom_authorize('add_salary_purchases');
        DB::beginTransaction();
        try {
            $salary = SalaryPurchase::create([
                'person_id'=>$request->people_id,
                'amount'=>$request->amount,
                'interest_rate'=>$request->interest_rate,
                'date'=>$request->date,

                'dollarTotal'=> $request->amount/setting('configuracion.dollar'),
                'dollarPrice'=>setting('configuracion.dollar'),
                'observations'=>$request->observations,
                'status'=>'pendiente',
                'registerUser_id'=>Auth::user()->id,
                'registerRole'=>Auth::user()->role->name
            ]);
            $salary->update(['code'=>'CS-'.str_pad($salary->id, 5, "0", STR_PAD_LEFT)]);

            DB::commit();
            return redirect()->route('salary-purchases.index')->with(['message' => 'Registrado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->route('salary-purchases.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function approveSalaryPuchase($id)
    {
        $SalaryPurchase = SalaryPurchase::where('id', $id)->where('deleted_at', null)->where('status', 'pendiente')->first();
        if(!$SalaryPurchase){
            return redirect()->route('salary-purchases.index')->with(['message' => 'Error, El registro no se encuentra disponible.', 'alert-type' => 'error']);
        }

        DB::beginTransaction();
        try {
            $SalaryPurchase->update([
                'status' => 'aprobado',
            ]); 
            DB::commit();
            return redirect()->route('salary-purchases.index')->with(['message' => 'Aprobado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('salary-purchases.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function destroy(Request $request, $id)
    {
        $SalaryPurchase = SalaryPurchase::where('id', $id)->where('deleted_at', null)->where('status','!=', 'vigente')->first();
        DB::beginTransaction();
        try {
            $SalaryPurchase->update([
                'deleted_at'=>Carbon::now(),
                'deleteUser_id' => Auth::user()->id,
                'deleteRole'=>Auth::user()->role->name,
                'deleteObservation'=>$request->deleteObservation,
            ]); 
            DB::commit();
            return redirect()->route('salary-purchases.index')->with(['message' => 'Eliminado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('salary-purchases.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function moneyDeliver(Request $request, $id)
    {
        $global_cashier = $this->availableMoney(Auth::user()->id, 'user')->original;
        if(!$global_cashier['cashier']){
            return redirect()->route('salary-purchases.index')->with(['message' => 'Error, La caja no se encuentra abierta.', 'alert-type' => 'error']);
        }

        $salary = SalaryPurchase::where('id', $id)
            ->where('deleted_at', null)
            ->where('status', 'aprobado')
            ->first();
        if(!$salary){
            return redirect()->route('salary-purchases.index')->with(['message' => 'El Registro no se encuentra disponible.', 'alert-type' => 'error']);
        }
        if($global_cashier['amountCashier'] < $salary->amount)
        {
            return redirect()->route('voyager.dashboard')->with(['message' => 'No tiene suficiente dinero disponible.', 'alert-type' => 'warning']);
        }
        DB::beginTransaction();
        try {
            $date = $request->date?date('Y-m-d', strtotime($request->date)):Carbon::now();
            // $date = $salary->date;
            $date = $this->month_next($date);

            $salary->update([
                'cashier_id'=>$global_cashier['cashier']->id,
                'date_limit'=>$date,
                'dateDelivered'=>$request->date?$request->date:Carbon::now(),
                'delivered_userId'=>Auth::user()->id,
                'delivered_userType' => Auth::user()->role->name,
                'status'=>'vigente'
            ]);   

            SalaryPurchaseMonth::create([
                'salaryPurchase_id' => $salary->id,
                'start' => date('Y-m-d', strtotime($salary->dateDelivered.' + 1 days')) ,
                'finish' => $this->month_next($salary->dateDelivered),
                'interest' => $salary->amount*($salary->interest_rate/100),
                'debt' => $salary->amount*($salary->interest_rate/100),
            ]);
            DB::commit();
            return redirect()->route('salary-purchases.index')->with(['message' => 'Registrado exitosamente.', 'alert-type' => 'success', 'salary_id' => $salary->id]);
        } catch (\Throwable $th) {
            DB::rollBack();
            
            return redirect()->route('salary-purchases.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function salaryPurchasePyment(Request $request)
    {
        // return $request;
        $global_cashier = $this->availableMoney(Auth::user()->id, 'user')->original;
        if(!$global_cashier['cashier']){
            return redirect()->route('salary-purchases.show', $request->salary)->with(['message' => 'Error, La caja no se encuentra abierta.', 'alert-type' => 'error']);
        }
        $cashier = $global_cashier['cashier'];
        $salary = SalaryPurchase::with(['salaryurchaseMonths'])
                ->where('id', $request->salary)->first();
        // return $salary;

        if(!isset($request->salary_id)  && ! isset($request->months)){
            return redirect()->route('salary-purchases.show', $request->salary)->with(['message' => 'Error, Envie correctamente los datos.', 'alert-type' => 'error']);
        }

        DB::beginTransaction();
        try {

            $transaction = Transaction::create(['type'=>$request->payment_type, 'category'=>'prestamos sueldo maestros']);
        
            $i = 1;
            $last = null;
            //Para pagar solos los meses de insteres
            while ($i <= ($request->months?count($request->months):0)) {

                $month = SalaryPurchaseMonth::where('id', $request->months[$i-1])->where('paid', 0)->where('debt', '>', 0)->first();

                $interesAux = $request->interest[$request->months[$i-1]];
                
                if($month)
                {
                    SalaryPurchaseMonthAgent::create([
                        'salaryPurchaseMonth_id' => $month->id,
                        'salaryPurchase_id'=>$salary->id,
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
                }
                $i++;
            }

                if($last)
                {
                    $aux=1;
                    $date = $salary->date_limit;
                    while ($aux <= $i-1) { //Para agregar meses a la fecha de limite
                        $date = $this->month_next($date);
                        $aux++;
                    }
                    $salary->update([
                        'date_limit'=>$date,
                    ]);
                }

            //para cuando se recojela prenda ya no se genera otro mes o para amortizar la prenda
            if($request->salary_id)
            {
                $amortization = SalaryPurchaseMonthAgent::where('salaryPurchase_id', $salary->id)
                    ->where('deleted_at', null)
                    ->whereRaw('salaryPurchaseMonth_id is null')
                    ->get()->sum('amount');

                if ($request->amountSalary < ($salary->amount - $amortization)) {
                    SalaryPurchaseMonthAgent::create([
                        'salaryPurchase_id'=>$salary->id,
                        'cashier_id' => $cashier->id,
                        'transaction_id'=>$transaction->id,
                        'type'=>'Amortizacion',
                        'amount' => $request->amountSalary,
                        'agent_id' => Auth::user()->id,
                        'agentType' => Auth::user()->role->name,

                        'dollarTotal'=> $request->amountSalary/setting('configuracion.dollar'),
                        'dollarPrice'=>setting('configuracion.dollar')
                    ]);                    
                } else {
                    $month = SalaryPurchaseMonth::where('salaryPurchase_id', $salary->id)->where('paid', 0)->where('debt', '>', 0)->get();
                    if(count($month)>0)
                    {
                        DB::rollBack();
                        return redirect()->route('salary-purchases.show', $salary->id)->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
                    }
                    $salary->update([
                        'status'=>'pagado'
                    ]);

                    SalaryPurchaseMonthAgent::create([
                        'salaryPurchase_id'=>$salary->id,
                        'cashier_id' => $cashier->id,
                        'transaction_id'=>$transaction->id,
                        'type'=>'Cancelacion',
                        'amount' => $request->amountSalary,
                        'agent_id' => Auth::user()->id,
                        'agentType' => Auth::user()->role->name,

                        'dollarTotal'=> $request->amountSalary/setting('configuracion.dollar'),
                        'dollarPrice'=>setting('configuracion.dollar')
                    ]);
                }
            }
            
            DB::commit();
            return redirect()->route('salary-purchases.show', $request->salary)->with(['message' => 'Pagado exitosamente.', 'alert-type' => 'success', 'salary_id' => $salary->id, 'transaction_id'=>$transaction->id]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('salary-purchases.show', $request->salary)->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function printTransaction($salary_id, $transaction_id)
    {
        $transaction_id =$transaction_id;
        $salary = SalaryPurchase::with(['person'])->where('id', $salary_id)->first();

        $salaryMonthAgent = DB::table('salary_purchase_month_agents as la')
            ->leftJoin('salary_purchase_months as lm', 'lm.id', 'la.salaryPurchaseMonth_id')
            ->join('users as u', 'u.id', 'la.agent_id')
            ->join('transactions as t', 't.id', 'la.transaction_id')
            ->where('la.salaryPurchase_id', $salary->id)
            ->where('t.id', $transaction_id)
            ->select('lm.id as prm_id', 'lm.start','lm.finish','la.salaryPurchase_id', 'la.amount', 'u.name', 'la.agentType', 'la.id as monthAgent')
            ->get();
        
        $transaction = Transaction::find($transaction_id);
        return view('salaryPurchases.print.print-transaction', compact('salary', 'transaction', 'salaryMonthAgent'));
    }


    public function ajax_verification($id)
    {
        $date = date('Y-m-d');   
        $salary = SalaryPurchase::with(['salaryPurchaseMonths'=>function($q){
                    $q->where('deleted_at', null)
                    ->where('debt', '!=', 0);
                }])
                ->whereIn('status', ['vigente'])
                // ->whereRaw($id ? " id = '$id'" : 1)
                ->where('deleted_at', null)
                ->get();  

        foreach($salary as $item)
        {
            $ok=true;
            
            while($ok)
            {
                $p = SalaryPurchase::with(['salaryPurchaseMonths'=>function($q){
                        $q->where('deleted_at', null);
                    }])
                    ->where('id', $item->id)
                    ->where('deleted_at', null)->first();    

                // $cant = count($p->salaryPurchaseMonths->where('debt', '!=', 0));

                $last = $p->salaryPurchaseMonths->last();
                $auxDate = new DateTime($last->finish);
                $auxDate = date('Y-m-d', strtotime($auxDate->format('Y-m-d').' + '.setting('configuracion.cantDayExpire').' days'));  

            
                if( $auxDate < $date && $ok)
                {
                    $interest_rate = ($item->amount) * ($item->interest_rate /100);
                    SalaryPurchaseMonth::create([
                        'salaryPurchase_id' => $item->id,
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

    
    
}
