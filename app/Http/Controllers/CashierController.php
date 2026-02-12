<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Vault;
use App\Models\VaultClosure;
use App\Models\VaultClosureDetail;
use App\Models\Cashier;
use App\Models\VaultDetail;
use App\Models\VaultDetailCash;
use App\Models\CashierMovement;
use App\Models\CashierDetail;
use App\Models\Inventory;
use Psy\CodeCleaner\ReturnTypePass;
use App\Models\Loan;
use App\Models\LoanDay;
use App\Models\LoanDayAgent;
use App\Models\PawnRegister;
use App\Models\PawnRegisterAmountAditional;
use App\Models\PawnRegisterMonth;
use App\Models\PawnRegisterMonthAgent;
use App\Models\SalaryPurchase;
use App\Models\SalaryPurchaseMonth;
use App\Models\SalaryPurchaseMonthAgent;
use App\Models\Sale;
use App\Models\SaleAgent;
use App\Models\Transaction;
use App\Models\User;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\Return_;

class CashierController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index(){
        $vault = Vault::where('deleted_at', null)->first();
        return view('cashier.browse', compact('vault'));
    }

    public function list(){
        $paginate = request('paginate') ?? 10;
        $search = request('search') ?? null;
        $status = request('status') ?? null;
        $cashier = Cashier::with(['vault_detail' => function($q){
                $q->where('type', 'egreso')->where('deleted_at', NULL);
            },  'movements' => function($q){
                $q->where('deleted_at', NULL);
            }])
            ->where(function($query) use ($search){
                if($search){
                    $query->OrwhereHas('user', function($query) use($search){
                        $query->whereRaw("name like '%$search%'");
                    });
                }
            })
            ->where('deleted_at', NULL)->orderBy('id', 'DESC')->paginate($paginate);
        return view('cashier.list', compact('cashier'));
    }

    public function create()
    {
        $vault = Vault::first();
        return view('cashier.add' , compact('vault'));
    }

    public function store(Request $request)
    {
        // return $request;
        $cashier = Cashier::where('user_id', $request->user_id)->where('status', '!=', 'cerrada')->where('deleted_at', NULL)->first();
        if(!$cashier){
            // if($request->amount == null){
            //     return redirect()->route('cashiers.create')->with(['message' => 'Sin monto asignado a la caja.', 'alert-type' => 'warning']);
            // }
            DB::beginTransaction();
            try {
                $cashier = Cashier::create([
                    'vault_id' => $request->vault_id,
                    'user_id' => $request->user_id,
                    'title' => $request->title,
                    'observations' => $request->observations,
                    'status' => 'apertura pendiente'
                ]);

                if($request->amount){
                    CashierMovement::create([
                        'user_id' => Auth::user()->id,
                        'cashier_id' => $cashier->id,
                        'cashier_movement_category_id' => $request->cashier_movement_category_id,
                        'balance' => $request->amount,
                        'amount' => $request->amount,
                        'description' => 'Monto de apertura de caja.',
                        'type' => 'ingreso',
                        'status'=>'Aceptado'
                    ]);

                    // Registrar detalle de bóveda
                    $cashier = Cashier::with('user')->where('id', $cashier->id)->first();
                    $detail = VaultDetail::create([
                        'user_id' => Auth::user()->id,
                        'vault_id' => $request->vault_id,
                        'cashier_id' => $cashier->id,
                        'description' => 'Traspaso a '.$cashier->title,
                        'type' => 'egreso',
                        'status' => 'aprobado'
                    ]);

                    for ($i=0; $i < count($request->cash_value); $i++) { 
                        VaultDetailCash::create([
                            'vault_detail_id' => $detail->id,
                            'cash_value' => $request->cash_value[$i],
                            'quantity' => $request->quantity[$i],
                        ]);
                    }
                }
                else
                {
                    //Para que inicie con 0 de caja 
                    CashierMovement::create([
                        'user_id' => Auth::user()->id,
                        'cashier_id' => $cashier->id,
                        'cashier_movement_category_id' => $request->cashier_movement_category_id,
                        'balance' => $request->amount??0,
                        'amount' => $request->amount??0,
                        'description' => 'Monto de apertura de caja.',
                        'type' => 'ingreso',
                        'status'=>'Aceptado'
                    ]);
                }
                $user = User::where('id',  $request->user_id)->first();

                DB::commit();
    
                return redirect()->route('cashiers.index')->with(['message' => 'Registro guardado exitosamente.', 'alert-type' => 'success', 'cashier_id'=>$cashier->id, 'user'=>$user]);
            } catch (\Throwable $th) {
                DB::rollback();
                
                return redirect()->route('cashiers.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
            }
        }else{
            return redirect()->route('cashiers.index')->with(['message' => 'El usuario seleccionado tiene una caja que no ha sido cerrada.', 'alert-type' => 'warning']);
        }
    }

    public function show($id)
    {
        // $cashier = Cashier::where('id', $id)->first();
        $cashier = $this->cashierId($id, null);
        // return $cashier;

        
        // $loan = Loan::with(['people'])->where('status', 'entregado')->where('cashier_id', $cashier->id)->get();

        // $trans = DB::table('loans as l')
        //                             ->join('loan_days as ld', 'ld.loan_id', 'l.id')
        //                             ->join('loan_day_agents as lda', 'lda.loanDay_id', 'ld.id')
        //                             ->join('transactions as t', 't.id', 'lda.transaction_id')
        //                             ->join('users as u', 'u.id', 'lda.agent_id')
        //                             ->join('people as p', 'p.id', 'l.people_id')
        //                             ->where('lda.status', 1)
        //                             // ->where('lda.deleted_at', null)
        //                             ->where('lda.cashier_id', $cashier->id)

        //                             // ->where('ld.deleted_at', null)
        //                             // ->where('ld.status', 1)

        //                             // ->where('l.deleted_at', null)

        //                             ->select('l.id as loan', 'l.code as code', 'l.deleted_at as eliminado', 'l.amountLoan', 'amountTotal', DB::raw('SUM(lda.amount)as amount'), 'u.name', 'lda.agentType', 'p.ci',
        //                                     'p.id as people', 'p.first_name', 'p.last_name1', 'p.last_name2', 'lda.transaction_id', 't.transaction', 't.created_at', 't.deleted_at', 'lda.deleteObservation', 't.type as transaction_type')
        //                             ->groupBy('loan', 'transaction_id')
        //                             ->orderBy('transaction_id', 'ASC')
        //                             ->get();


        $saleAgent = SaleAgent::with(['sale.person', 'transaction', 'register'])->where('cashier_id', $cashier->id)->get();
        
        // return $saleAgent;       
        // :::::::::::::::::::::::::::::::::::::::::::::::::::::  PRENDARIO  ::::::::::::::::::::::::::::::::
        
        return view('cashier.read' , compact('cashier', 'saleAgent'));
    }

    //para abrir la vista de abonar dinero a una caja que este en estado ABIERTA
    public function amount($id)
    {
        $cashier = Cashier::findOrFail($id);
        // return $cashier;
        if($cashier->status == 'abierta'){
            return view('cashier.add-amount', compact('id'));
        }else{
            return redirect()->route('voyager.cashiers.index')->with(['message' => 'La caja seleccionada ya no se encuentra abierta.', 'alert-type' => 'warning']);
        }
    }

    //para guardar el dinero abonado a la caja ABIERTA
    public function amount_store(Request $request){
        DB::beginTransaction();
        try {
            $cashier = Cashier::with('user')->where('id', $request->cashier_id)->where('status', 'abierta')->first();
            if(!$cashier){
                return redirect()->route($request->redirect ?? 'voyager.cashiers.index')->with(['message' => 'La caja seleccionada ya no se encuentra abierta.', 'alert-type' => 'warning']);
            }

            // Registrar traspaso a la caja
            $movement = CashierMovement::create([
                'user_id' => Auth::user()->id,
                'cashier_id' => $request->cashier_id,
                'cashier_movement_category_id' => $request->cashier_movement_category_id,
                'balance' => $request->amount,
                'amount' => $request->amount,
                'description' => $request->description,
                'type' => 'ingreso',
                'status'=>'Aceptado'
            ]);

            $id_transfer = $movement->id;

            if($request->amount){
                // Registrar detalle de bóveda
                $detail = VaultDetail::create([
                    'user_id' => Auth::user()->id,
                    'vault_id' => $request->vault_id,
                    'cashier_id' => $request->cashier_id,
                    'description' => 'Traspaso a '.$cashier->title,
                    'type' => 'egreso',
                    'status' => 'aprobado'
                ]);

                for ($i=0; $i < count($request->cash_value); $i++) { 
                    VaultDetailCash::create([
                        'vault_detail_id' => $detail->id,
                        'cash_value' => $request->cash_value[$i],
                        'quantity' => $request->quantity[$i],
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('cashiers.index')->with(['message' => 'Registro guardado exitosamente.', 'alert-type' => 'success', 'id_transfer' => $id_transfer]);
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->route('cashiers.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }


    public function expense_store(Request $request){
        $global_cashier = $this->availableMoney(Auth::user()->id, 'user')->original;
        if(!$global_cashier['cashier']){
            return redirect()->route('loans.index')->with(['message' => 'Error, La caja no se encuentra abierta.', 'alert-type' => 'error']);
        }
        if($global_cashier['amountCashier'] < $request->amount)
        {
            return redirect()->route('voyager.dashboard')->with(['message' => 'No tiene suficiente dinero disponible.', 'alert-type' => 'warning']);
        }
        // return $global_cashier['cashier']->id
        DB::beginTransaction();
        try {
            // Registrar egreso a la caja
            CashierMovement::create([
                'user_id' => Auth::user()->id,
                'cashier_id' => $global_cashier['cashier']->id,
                'cashier_movement_category_id' => $request->cashier_movement_category_id,
                'amount' => $request->amount,
                'description' => $request->description,
                'type' => 'egreso',
                'status'=>'Aceptado'
            ]);
            DB::commit();
            return redirect()->to($_SERVER['HTTP_REFERER'])->with(['message' => 'Registrado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->to($_SERVER['HTTP_REFERER'])->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    //*** Para que los cajeros Acepte o rechase el dinero dado por Boveda o gerente
    public function change_status($id, Request $request){
        DB::beginTransaction();
        try {
            if($request->status == 'abierta'){
                $message = 'Caja aceptada exitosamente.';
                Cashier::where('id', $id)->update([
                    'status' => $request->status,
                    'view' => Carbon::now()
                ]);
            }else{
                $cashier = Cashier::with(['vault_details.cash' => function($q){
                    $q->where('deleted_at', NULL);
                }])->where('id', $id)->first();

                $message = 'Caja rechazada exitosamente.';
                Cashier::where('id', $id)->update([
                    'status' => 'Rechazada',
                    'deleted_at' => Carbon::now()
                ]);

                $vault_detail = VaultDetail::create([
                    'user_id' => Auth::user()->id,
                    'vault_id' => $cashier->vault_details->vault_id,
                    'cashier_id' => $cashier->id,
                    'description' => 'Rechazo de apertura de caja de '.$cashier->title.'.',
                    'type' => 'ingreso',
                    'status' => 'aprobado'
                ]);

                foreach ($cashier->vault_details->cash as $item) {
                    if($item->quantity > 0){
                        VaultDetailCash::create([
                            'vault_detail_id' => $vault_detail->id,
                            'cash_value' => $item->cash_value,
                            'quantity' => $item->quantity
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('voyager.dashboard')->with(['message' => $message, 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->route('voyager.dashboard')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    //***para cerrar la caja el cajero vista 
    public function close($id)
    {
        $cashier = $this->cashierId($id, 'abierta');

        if (!$cashier) {
            return redirect()->route('voyager.dashboard')->with(['message' => 'La caja no se encuentra abierta.', 'alert-type' => 'warning']);
        }
        if (count($cashier->movements->where('deleted_at', null)->where('status', 'Pendiente'))>0) {
            return redirect()->route('voyager.dashboard')->with(['message' => 'La caja no puede ser cerrada, tiene transacciones pendiente.', 'alert-type' => 'warning']);
        }        
        return view('cashier.close', compact('cashier'));
    }

    public function close_store($id, Request $request){
        // dd($request->all());
        DB::beginTransaction();
        try {
            $cashier = Cashier::findOrFail($id);
            if($cashier->status != 'cierre pendiente'){
                $cashier->amount = $request->amount_cashier;
                $cashier->amount_real = $request->amount_real;
                $cashier->balance = $request->amount_real - $request->amount_cashier;
                $cashier->closed_at = Carbon::now();
                $cashier->status = 'cierre pendiente';
                $cashier->save();

                for ($i=0; $i < count($request->cash_value); $i++) { 
                    CashierDetail::create([
                        'cashier_id' => $id,
                        'cash_value' => $request->cash_value[$i],
                        'quantity' => $request->quantity[$i],
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('voyager.dashboard')->with(['message' => 'Caja cerrada exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->route('voyager.dashboard')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function close_revert($id, Request $request){
        DB::beginTransaction();
        try {
            $cashier = Cashier::findOrFail($id);
            if($cashier->status == 'cierre pendiente'){
                $cashier->amount = NULL;
                $cashier->balance = NULL;
                $cashier->closed_at = NULL;
                $cashier->status = 'abierta';
                $cashier->save();

                CashierDetail::where('cashier_id', $id)->update([
                    'deleted_at' => Carbon::now()
                ]);

                DB::commit();
                return redirect()->route('voyager.dashboard')->with(['message' => 'Caja reabierta exitosamente.', 'alert-type' => 'success']);
            }

            return redirect()->route('voyager.dashboard')->with(['message' => 'Lo siento, su caja ya fué cerrada.', 'alert-type' => 'warning']);
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->route('voyager.dashboard')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function confirm_close($id)
    {
        $cashier = Cashier::with([
            'details' => function($q){
                $q->where('deleted_at', NULL);
            },
            'loan_payments' => function($q){
                $q->where('type', 'Efectivo')->where('deleted_at', NULL);
            },
            'loans' => function($q){
                $q->where('status', 'entregado')->where('deleted_at', NULL);
            },
            'pawn' => function($q){
                $q->where('deleted_at', NULL);
            },
            'pawnPayment' => function($q) {
                $q->whereHas('transaction', function($q) {
                    $q->where('type', 'Efectivo')->where('deleted_at', NULL);
                });
            },
            'salePayment' => function($q) {
                $q->whereHas('transaction', function($q) {
                    $q->where('type', 'Efectivo')->where('deleted_at', NULL);
                });
            } 
            ])
            ->where('id', $id)->first();
        // return $cashier;

        if($cashier->status == 'cierre pendiente'){
            return view('cashier.confirm_close', compact('cashier'));
        }else{
            return redirect()->route('cashiers.index')->with(['message' => 'La caja ya no está abierta.', 'alert-type' => 'warning']);
        }
    }

    public function confirm_close_store($id, Request $request)
    {
        
        DB::beginTransaction();
        try {
            $cashier = Cashier::findOrFail($id);
            $cashier->status = 'cerrada';
            $cashier->closeUser_id= Auth::user()->id;
            $cashier->save();
            
            $detail = VaultDetail::create([
                'user_id' => Auth::user()->id,
                'cashier_id' => $id,
                'vault_id' => $request->vault_id,
                'description' => 'Devolución de la caja '.$cashier->title,
                'type' => 'ingreso',
                'status' => 'aprobado'
            ]);

            for ($i=0; $i < count($request->cash_value); $i++) { 
                VaultDetailCash::create([
                    'vault_detail_id' => $detail->id,
                    'cash_value' => $request->cash_value[$i],
                    'quantity' => $request->quantity[$i],
                ]);
            }

            DB::commit();
            return redirect()->route('cashiers.index')->with(['message' => 'Caja cerrada exitosamente.', 'alert-type' => 'success', 'id_cashier_close' => $id]);
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->route('cashiers.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function print_open($id){
        $vaultDeatil = VaultDetail::where('id', $id)->where('deleted_at', null)->first();
        $aux = $id;
        $cashier = Cashier::with(['user', 'vault_details' => function($q) use($aux){
                $q->where('id', $aux)->where('deleted_at', NULL);
            }, 'vault_details.cash' => function($q){
                $q->where('deleted_at', NULL);
            }, 'movements' => function($q){
                $q->where('deleted_at', NULL);
            }])
            ->where('id', $vaultDeatil->cashier_id)->first();


        return view('cashier.print-open', compact('cashier'));
    }

    public function print_close($id){
        $cashier = Cashier::with(['user','userclose',
                    'movements' => function($q){
                        $q->where('deleted_at', NULL);
                    }, 'details' => function($q){
                        $q->where('deleted_at', NULL);
                    }])->where('id', $id)->first();

        return view('cashier.print-close', compact('cashier'));
    }

    public function print($id){
        $cashier = Cashier::with(['details' => function($q){
                        $q->where('deleted_at', NULL);
                    }, 'loan_payments' => function($q){
                        $q->where('type', 'Efectivo')->where('deleted_at', NULL);
                    }, 'loans' => function($q){
                        $q->where('status', 'entregado')->where('deleted_at', NULL);
                    }])->where('id', $id)->first();
        $trans = DB::table('loans as l')
                    ->join('loan_days as ld', 'ld.loan_id', 'l.id')
                    ->join('loan_day_agents as lda', 'lda.loanDay_id', 'ld.id')
                    ->join('transactions as t', 't.id', 'lda.transaction_id')
                    ->join('users as u', 'u.id', 'lda.agent_id')
                    ->join('people as p', 'p.id', 'l.people_id')
                    ->where('lda.status', 1)
                    ->where('lda.cashier_id', $cashier->id)
                    ->select('l.id as loan', 'l.code as code', 'l.deleted_at as eliminado', 'l.amountLoan', 'amountTotal', DB::raw('SUM(lda.amount)as amount'), 'u.name', 'lda.agentType', 'p.ci',
                            'p.id as people', 'p.first_name', 'p.last_name1', 'p.last_name2', 'lda.transaction_id', 't.transaction', 't.created_at', 't.deleted_at', 'lda.deleteObservation', 't.type as transaction_type')
                    ->groupBy('loan', 'transaction')
                    ->orderBy('transaction', 'ASC')
                    ->get();
        $loan = Loan::with(['people'])->where('status', 'entregado')->where('cashier_id', $cashier->id)->get();

        return view('cashier.print', compact('cashier', 'trans', 'loan'));
    }


    public function amountTransferStore(Request $request)
    {        
        // return $request;
        $validatedData = $request->validate([
            'transferCashier' => 'required|numeric|min:1',
        ]);
        $moneyTranfer = $validatedData['transferCashier'];
        $cashierDestination = Cashier::with(['movements' => function($q){
                $q->where('deleted_at', NULL);
            }])
            ->where('id', $request->transferCashier_id)
            ->where('status', 'abierta')
            ->where('deleted_at', NULL)
            ->first();
        if(!$cashierDestination){
            return redirect()->route('voyager.dashboard')->with(['message' => 'La caja de destino no se encuentra activa.', 'alert-type' => 'warning']);
        }

        //que la caja de origen este abierta para realizar la transaccion
        if(!$this->cashierOpen()){
            return redirect()->route('voyager.dashboard')->with(['message' => 'Debe abrir caja.', 'alert-type' => 'warning']);
        }

        //Verificamos si hay dinero disponible en caja para poder continuar con la transaccion
        $global_cashier = $this->availableMoney(Auth::user()->id, 'user')->original;
        if($global_cashier['amountCashier'] < $moneyTranfer)
        {
            return redirect()->route('voyager.dashboard')->with(['message' => 'No tiene suficiente dinero disponible.', 'alert-type' => 'warning']);
        }
        DB::beginTransaction();
        try {
            $movement = CashierMovement::create([
                'user_id' => Auth::user()->id,
                'cashier_id' => $this->cashierOpen()->id,
                'amount' => $moneyTranfer,
                'description' => $request->description,
                'transferCashier_id'=>$request->transferCashier_id, //Para la caja de destino
                'type' => 'egreso',
                'status'=>'Pendiente'
            ]);
            DB::commit();
            return redirect()->route('voyager.dashboard')->with(['message' => 'Transacción registrada exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('voyager.dashboard')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function cashierAmountTransferDetele($cashier_id, $transfer_id)
    {
        if(!$this->cashierId($cashier_id, 'abierta')){
            return redirect()->to($_SERVER['HTTP_REFERER'])->with(['message' => 'La caja no se encuentra abierta.', 'alert-type' => 'warning']);
        }
        $transfer = CashierMovement::where('id', $transfer_id)->where('deleted_at', null)->where('status', 'Pendiente')->first();
        if(!$transfer){
            return redirect()->to($_SERVER['HTTP_REFERER'])->with(['message' => 'La transferencia no se encuentra disponible.', 'alert-type' => 'warning']);
        }
        DB::beginTransaction();
        try {
            $transfer->update([
                'deleted_at'=>Carbon::now()
            ]);
            DB::commit();
            return redirect()->to($_SERVER['HTTP_REFERER'])->with(['message' => 'Eliminado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->to($_SERVER['HTTP_REFERER'])->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }

        // return redirect()->to($_SERVER['HTTP_REFERER'])->with(['message' => 'El monto supero el dinero en caja', 'alert-type' => 'warning']);   

    }

    public function amountTransferSuccess($cashier_id, $transfer_id)
    {
        $transfer = CashierMovement::where('id', $transfer_id)->where('deleted_at', null)->where('status', 'Pendiente')->first();
        if(!$transfer){
            return redirect()->route('voyager.dashboard')->with(['message' => 'La transferencia no se encuentra disponible.', 'alert-type' => 'warning']);
        }

        if(!$this->cashierId($cashier_id, 'abierta')){
            return redirect()->route('voyager.dashboard')->with(['message' => 'La caja no se encuentra abierta.', 'alert-type' => 'warning']);
        }

        DB::beginTransaction();
        try {
            CashierMovement::create([
                'user_id' => Auth::user()->id,
                'cashier_id' => $cashier_id,
                'amount' => $transfer->amount,
                'balance' => $transfer->amount,
                'description' => "Transferencias de caja",
                'transferCashier_id'=>$transfer->cashier_id, //Para la caja de destino
                'type' => 'ingreso',
                'status'=>'Aceptado'
            ]);

            $transfer->update([
                'status'=>'Aceptado'
            ]);


            DB::commit();
            return redirect()->route('voyager.dashboard')->with(['message' => 'Transacción aceptada exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('voyager.dashboard')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }



    public function amountTransferDecline($cashier_id, $transfer_id)
    {
        $transfer = CashierMovement::where('id', $transfer_id)->where('deleted_at', null)->where('status', 'Pendiente')->first();
        if(!$transfer){
            return redirect()->route('voyager.dashboard')->with(['message' => 'La transferencia no se encuentra disponible.', 'alert-type' => 'warning']);
        }

        if(!$this->cashierId($cashier_id, 'abierta')){
            return redirect()->route('voyager.dashboard')->with(['message' => 'La caja no se encuentra abierta.', 'alert-type' => 'warning']);
        }

        DB::beginTransaction();
        try {

            $transfer->update([
                'status'=>'Rechazado'
            ]);


            DB::commit();
            return redirect()->route('voyager.dashboard')->with(['message' => 'Transacción rechazada exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('voyager.dashboard')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }



    // ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::DIARIO::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

    public function deleteLoan(Request $request, $id, $loan){
        $cashier = $this->cashierId($id, 'abierta');
        if (!$cashier) {
            return redirect()->route('cashiers.show', ['cashier'=>$id])->with(['message' => 'La caja no se encuentra abierta.', 'alert-type' => 'warning']);
        }

        $loan = Loan::where('deleted_at', null)->where('id', $loan)->first();
        if (!$loan) {
            return redirect()->route('cashiers.show', ['cashier'=>$id])->with(['message' => 'El prestamo no se encuentra disponible.', 'alert-type' => 'warning']);
        }

        if($loan->debt != $loan->amountTotal)
        {
            return redirect()->route('cashiers.show', ['cashier'=>$id])->with(['message' => 'Elimine las transacciones del prestamo.', 'alert-type' => 'warning']);
        }
        
        DB::beginTransaction();
        try {
            $loan->update([
                'deleted_at' => Carbon::now(),
                'deleted_userId' => Auth::user()->id,
                'deleted_agentType' => Auth::user()->role->name,
                'deleteObservation' => $request->deleteObservation,
            ]);
            DB::commit();
            return redirect()->route('cashiers.show', ['cashier'=>$cashier->id])->with(['message' => 'Eliminado exitosamente...', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('cashiers.show', ['cashier'=>$id])->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }


    }
    //Para eliminar transacciones activas con cajas activas
    public function deleteTransaction(Request $request, $id, $transaction)
    {
        $cashier = $this->cashierId($id, 'abierta');
        if (!$cashier) {
            return redirect()->route('cashiers.show', ['cashier'=>$id])->with(['message' => 'La caja no se encuentra abierta.', 'alert-type' => 'warning']);
        }
        $transaction = Transaction::with(['payments'=>function($q){
                    $q->where('deleted_at', null);
                }
            ])
            ->where('deleted_at', null)->where('id', $transaction)->first();
        // return $transaction;
        if (!$transaction) {
            return redirect()->route('cashiers.show', ['cashier'=>$id])->with(['message' => 'El prestamo no se encuentra disponible.', 'alert-type' => 'warning']);
        }

        $amountAux = $transaction->payments->sum('amount');
        if ($amountAux > $this->availableMoney($cashier->id, 'cashier')->original['amountCashier'] && $transaction->type!='Qr') {
            return redirect()->route('cashiers.show', ['cashier'=>$cashier->id])->with(['message' => 'La caja no cuenta con dinero disponible.', 'alert-type' => 'warning']);
        }
        DB::beginTransaction();
        try {
            $loanDayAgent = LoanDayAgent::where('transaction_id', $transaction->id)->get();

            $auxDay = LoanDay::where('id', $loanDayAgent->first()->loanDay_id)->first()->loan_id;
            
            foreach($transaction->payments as $item){
                $agent = LoanDayAgent::where('id', $item->id)->first();
                $day = LoanDay::where('id', $item->loanDay_id)->first();
                $day->increment('debt',$agent->amount);

                $item->update([
                    'deleted_at'=>Carbon::now(), 
                    'deleted_userId'=>Auth::user()->id, 
                    'deleted_agentType'=>Auth::user()->role->name,
                    'deleteObservation'=> $request->deleteObservation
                ]);
            }

            $auxLoan = Loan::where('id', $auxDay)->first();
            $auxLoan->increment('debt', $amountAux);

            $transaction->update([
                'deleted_at'=>Carbon::now()
            ]);
            DB::commit();
            return redirect()->route('cashiers.show', ['cashier'=>$cashier->id])->with(['message' => 'Eliminado exitosamente...', 'alert-type' => 'success']);
        }catch (\Throwable $th) {
            DB::rollBack();  
            return redirect()->route('cashiers.show', ['cashier'=>$id])->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }


    //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::PRENDARIO::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

    
    public function pawnDelete(Request $request, $id, $pawn)
    {
        $cashier = $this->cashierId($id, 'abierta');
        if (!$cashier) {
            return redirect()->route('cashiers.show', ['cashier'=>$id])->with(['message' => 'La caja no se encuentra abierta.', 'alert-type' => 'warning']);
        }

        $pawn = PawnRegister::where('deleted_at', null)->where('id', $pawn)->first();
        if (!$pawn) {
            return redirect()->route('cashiers.show', ['cashier'=>$id])->with(['message' => 'El prestamo no se encuentra disponible.', 'alert-type' => 'warning']);
        }
        DB::beginTransaction();
        try {
            $pawn->update([
                'deleted_at'=>Carbon::now(),
                'deleteUser_id'=>Auth::user()->id,
                'deleteRole'=>Auth::user()->role->name,
                'deleteObservation'=>$request->deleteObservation
            ]);

            DB::commit();
            return redirect()->route('cashiers.show', ['cashier'=>$id])->with(['message' => 'Eliminado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('cashiers.show', ['cashier'=>$id])->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }

    }
    public function pawnTransactionDelete(Request $request, $id, $transaction)
    {
        $cashier = $this->cashierId($id, 'abierta');

        if (!$cashier) {
            return redirect()->to($_SERVER['HTTP_REFERER'])->with(['message' => 'La caja no se encuentra abierta.', 'alert-type' => 'warning']);
        }
        $pawnAgent = PawnRegisterMonthAgent::where('cashier_id', $cashier->id)->where('transaction_id', $transaction)->where('deleted_at',null)->get();

        $amountAux = $pawnAgent->sum('amount');

        if ($amountAux > $this->availableMoney($cashier->id, 'cashier')->original['amountCashier']) {
            return redirect()->route('cashiers.show', ['cashier'=>$cashier->id])->with(['message' => 'La caja no cuenta con dinero disponible.', 'alert-type' => 'warning']);
        }


        DB::beginTransaction();
        try {

            $auxPawn = PawnRegister::where('id', $pawnAgent->first()->pawnRegister_id)->first();
            $cantMonth = 0;

            foreach($pawnAgent as $item){

                if($item->pawnRegisterMonth_id)
                {
                    PawnRegisterMonth::where('id', $item->pawnRegisterMonth_id)->update(['paid'=>0, 'debt'=>$item->amount]);
                    $cantMonth++;
                }
                $item->update([
                    'deleted_at'=>Carbon::now(),
                    'deleteUser_id'=>Auth::user()->id,
                    'deleteRole'=>Auth::user()->role->name,
                    'deleteObservation'=>$request->deleteObservation
                ]);
            }

            $date = PawnRegisterMonth::where('id', $pawnAgent->first()->pawnRegisterMonth_id)->first()->finish;

            $i=1;
            while($i <= $cantMonth)
            {
                $date = $this->month_next($date);
                $i++;
            }

            Transaction::where('id', $transaction)->update(['deleted_at'=>Carbon::now()]);
            $auxPawn->update([
                'status'=>'entregado',
                'date_limit'=>$date
            ]);

            DB::commit();
            return redirect()->route('cashiers.show', ['cashier'=>$cashier->id])->with(['message' => 'Transacción eliminada exitosamente...', 'alert-type' => 'success']);

        }catch (\Throwable $th) {
            DB::rollBack();  
            return redirect()->route('cashiers.show', ['cashier'=>$id])->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function pawnAmountAditionalDelete(Request $request, $id, $aditional)
    {
        $cashier = $this->cashierId($id, 'abierta');
        if (!$cashier) {
            return redirect()->route('cashiers.show', ['cashier'=>$id])->with(['message' => 'La caja no se encuentra abierta.', 'alert-type' => 'warning']);
        }

        $aditional = PawnRegisterAmountAditional::where('deleted_at', null)->where('id', $aditional)->first();
        if (!$aditional) {
            return redirect()->route('cashiers.show', ['cashier'=>$id])->with(['message' => 'El registro no se encuentra disponible.', 'alert-type' => 'warning']);
        }
        DB::beginTransaction();
        try {
            $aditional->update([
                'deleted_at'=>Carbon::now(),
                'deletedUser_id'=>Auth::user()->id,
                'deletedRole'=>Auth::user()->role->name,
                'deletedObservation'=>$request->deleteObservation
            ]);

            DB::commit();
            return redirect()->route('cashiers.show', ['cashier'=>$id])->with(['message' => 'Eliminado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('cashiers.show', ['cashier'=>$id])->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::GASTOS ADICIONALES O EXTRAS::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

    public function cashierExpenseDelete($id, $expense)
    {
        $cashier = $this->cashierId($id, 'abierta');
        if (!$cashier) {
            return redirect()->to($_SERVER['HTTP_REFERER'])->with(['message' => 'La caja no se encuentra abierta.', 'alert-type' => 'warning']);
        }
        $expense = CashierMovement::where('deleted_at', null)->where('id', $expense)->first();
        if (!$expense) {
            return redirect()->to($_SERVER['HTTP_REFERER'])->with(['message' => 'El registro no se encuentra disponible.', 'alert-type' => 'warning']);
        }
        DB::beginTransaction();
        try {
            $expense->update([
                'deleted_at'=>Carbon::now()
            ]);
            DB::commit();
            return redirect()->to($_SERVER['HTTP_REFERER'])->with(['message' => 'Eliminado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->to($_SERVER['HTTP_REFERER'])->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }


    //:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::VENTAS INVENTATIOS:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

    public function cashierSaleDelete(Request $request, $id, $saleAgent)
    {
        $cashier = $this->cashierId($id, 'abierta');
        if (!$cashier) {
            return redirect()->to($_SERVER['HTTP_REFERER'])->with(['message' => 'La caja no se encuentra abierta.', 'alert-type' => 'warning']);
        }

        $saleAgent= SaleAgent::where('deleted_at', null)->where('id', $saleAgent)->first();
        if (!$saleAgent) {
            return redirect()->to($_SERVER['HTTP_REFERER'])->with(['message' => 'El registro no se encuentra disponible.', 'alert-type' => 'warning']);
        }
        if ($saleAgent->amount > $this->availableMoney($cashier->id, 'cashier')->original['amountCashier']) {
            return redirect()->route('cashiers.show', ['cashier'=>$cashier->id])->with(['message' => 'La caja no cuenta con dinero disponible.', 'alert-type' => 'warning']);
        }

        $sale = Sale::with((['saleDetails.inventory',
                'saleAgents'=>function($q)
                {
                    $q->where('deleted_at', null);
                }
            ]))
            ->where('deleted_at', null)
            ->where('id', $saleAgent->sale_id)
            ->first();

        DB::beginTransaction();
        try {

            $count = count($sale->saleAgents);
            if($count==1)
            {
                foreach ($sale->saleDetails as $detail) {
                    $inventory = Inventory::findOrFail($detail->inventory_id);
                    $inventory->stock += $detail->quantity;
                    $inventory->status = 'disponible';
                    $inventory->update();
                }
                
                $sale->update([
                    'deleted_at'=>Carbon::now(),
                    'deletedUser_id'=>Auth::user()->id,
                    'deletedRole'=>Auth::user()->role->name,
                    'deletedObservation'=>$request->deleteObservation
                ]);
            }
            else
            {
                $sale->debt += $saleAgent->amount;
                $sale->update();
            }

            $saleAgent->update([
                'deleted_at'=>Carbon::now(),
                'deleteUser_id'=>Auth::user()->id,
                'deleteRole'=>Auth::user()->role->name,
                'deleteObservation'=>$request->deleteObservation
            ]);
            DB::commit();
            return redirect()->to($_SERVER['HTTP_REFERER'])->with(['message' => 'Eliminado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->to($_SERVER['HTTP_REFERER'])->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }


    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: Prestamos de Sueldo a los Profesores :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

    public function salaryPurchaseDelete(Request $request, $id, $salary)
    {
        $cashier = $this->cashierId($id, 'abierta');
        if (!$cashier) {
            return redirect()->route('cashiers.show', ['cashier'=>$id])->with(['message' => 'La caja no se encuentra abierta.', 'alert-type' => 'warning']);
        }

        $salary = SalaryPurchase::where('deleted_at', null)->where('id', $salary)->first();
        if (!$salary) {
            return redirect()->route('cashiers.show', ['cashier'=>$id])->with(['message' => 'El registro no se encuentra disponible.', 'alert-type' => 'warning']);
        }
        DB::beginTransaction();
        try {
            $salary->update([
                'deleted_at'=>Carbon::now(),
                'deleteUser_id'=>Auth::user()->id,
                'deleteRole'=>Auth::user()->role->name,
                'deleteObservation'=>$request->deleteObservation
            ]);

            DB::commit();
            return redirect()->route('cashiers.show', ['cashier'=>$id])->with(['message' => 'Eliminado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('cashiers.show', ['cashier'=>$id])->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }

    }

    public function salaryPurchaseTransactionDelete(Request $request, $id, $transaction)
    {
        // return $request;
        $cashier = $this->cashierId($id, 'abierta');

        if (!$cashier) {
            return redirect()->to($_SERVER['HTTP_REFERER'])->with(['message' => 'La caja no se encuentra abierta.', 'alert-type' => 'warning']);
        }
        $salaryAgent = SalaryPurchaseMonthAgent::where('cashier_id', $cashier->id)->where('transaction_id', $transaction)->where('deleted_at',null)->get();

        $amountAux = $salaryAgent->sum('amount');

        if ($amountAux > $this->availableMoney($cashier->id, 'cashier')->original['amountCashier']) {
            return redirect()->route('cashiers.show', ['cashier'=>$cashier->id])->with(['message' => 'La caja no cuenta con dinero disponible.', 'alert-type' => 'warning']);
        }

        DB::beginTransaction();
        try {

            $auxSalary = SalaryPurchase::where('id', $salaryAgent->first()->salaryPurchase_id)->first();
            // return $salaryAgent;
            $cantMonth = 0;

            foreach($salaryAgent as $item){

                if($item->salaryPurchaseMonth_id)
                {
                    SalaryPurchaseMonth::where('id', $item->salaryPurchaseMonth_id)->update(['paid'=>0, 'debt'=>$item->amount]);
                    $cantMonth++;
                }
                $item->update([
                    'deleted_at'=>Carbon::now(),
                    'deleteUser_id'=>Auth::user()->id,
                    'deleteRole'=>Auth::user()->role->name,
                    'deleteObservation'=>$request->deleteObservation
                ]);
            }

            Transaction::where('id', $transaction)->update(['deleted_at'=>Carbon::now()]);
            $auxSalary->update([
                'status'=>'vigente',
                // 'date_limit'=>$date
            ]);

            DB::commit();
            return redirect()->route('cashiers.show', ['cashier'=>$cashier->id])->with(['message' => 'Transacción eliminada exitosamente...', 'alert-type' => 'success']);

        }catch (\Throwable $th) {
            DB::rollBack();  
            return redirect()->route('cashiers.show', ['cashier'=>$id])->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }



}
