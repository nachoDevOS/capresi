<?php

namespace App\Http\Controllers;

use App\Models\Cashier;
use App\Models\CashierMovement;
use App\Models\Inventory;
use App\Models\People;
use App\Models\Sale;
use App\Models\SaleAgent;
use App\Models\SaleDetail;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\FuncCall;
use Ramsey\Uuid\FeatureSet;

class SaleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->custom_authorize('browse_sales');
        return view('sales.browse');
    }

    public function list(){
        $this->custom_authorize('browse_pawn');
        $paginate = request('paginate') ?? 10;
        $search = request('search') ?? null;
        $status = request('status') ?? null;

        $status = ($status == 'pagado') ? "debt = 0" : $status;
        $status = ($status == 'en pago') ? "debt > 0" : $status;
        
        $data = Sale::with(['saleDetails.inventory.item', 'register', 'saleDetails.inventory.features'])
                    ->where(function($query) use ($search){
                        if($search){
                            $query
                            ->OrWhereHas('register', function($query) use($search){
                                $query->whereRaw($search ? 'name like "%'.$search.'%"' : 1);
                            })
                            ->OrWhereHas('saleDetails.inventory.item', function($query) use($search){
                                $query->whereRaw($search ? 'name like "%'.$search.'%"' : 1);
                            })
                            ->OrWhereHas('saleDetails.inventory.features', function($query) use($search){
                                $query->whereRaw($search ? 'value like "%'.$search.'%"' : 1);
                            })
                            ->OrWhereHas('saleDetails.inventory.features', function($query) use($search){
                                $query->whereRaw($search ? 'title like "%'.$search.'%"' : 1);
                            })
                            ->OrWhereRaw($search ? "code like '%$search%'" : 1);
                        }
                    })
                    ->whereRaw($status?$status:1)
                    ->where('deleted_at', null)
                    ->orderBy('id', 'desc')
                    ->paginate($paginate);
                    
        return view('sales.list', compact('data'));
    }

    public function create()
    {
        return view('sales.edit-add');
    }

    public function generarNumeroFactura() {
        $fecha = now()->format('Ymd');
        $ultimoRegistro = Sale::orderBy('id', 'desc')
                                ->first();
        $secuencia = $ultimoRegistro ? ($ultimoRegistro->id + 1) : 1;
        return sprintf("%s%06d", $fecha, $secuencia);
    }
    
    public function store(Request $request)
    {

        $global_cashier = $this->availableMoney(Auth::user()->id, 'user')->original;

        if(!$global_cashier['cashier']){
            return redirect()->route('loans-daily.money', ['loan' => $request->loan_id])->with(['message' => 'Error, La caja no se encuentra abierta.', 'alert-type' => 'error']);
        }
        if(count($request->product_id)==0){
            return redirect()->route('sales.create')->with(['message' => 'Detalle de ventas vacío.', 'alert-type' => 'warning']);
        }

        DB::beginTransaction();
        try {
            $transaction = Transaction::create([
                'type'=>$request->payment_type, 
                'category'=>'venta']);
            
            $sale = Sale::create([
                'code'=>$this->generarNumeroFactura(),
                'person_id' => $request->person_id?$request->person_id:null,

                'amount'=>$request->amountTotalSale + $request->discount,
                'discount'=>$request->discount,
                'amountTotal'=> $request->amountTotalSale,
                'dollarTotal'=> $request->amountTotalSale/setting('configuracion.dollar'),
                'dollarPrice'=>setting('configuracion.dollar'), 

                'debt'=> $request->amountTotalSale - $request->amountReceived,
                'typeSale'=>$request->typeSale,

                'saleDate'=> $request->dateSale,

                'datePayment'=> $request->next_payment?$request->next_payment:null,

                'description'=>$request->observations,
                'status' =>1,

                'registerUser_id'=>Auth::user()->id,
                'registerRole'=>Auth::user()->role->name,
            ]);
            // return 1;

            $total = 0;
            for ($i=0; $i < count($request->product_id); $i++) { 
                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'inventory_id'=> $request->product_id[$i],

                    'price' => $request->price[$i],
                    'quantity' => $request->quantity[$i],

                    'amountTotal' => $request->quantity[$i] * $request->price[$i],

                    'dollarTotal'=> ($request->quantity[$i] + $request->price[$i])/setting('configuracion.dollar'),
                    'dollarPrice'=>setting('configuracion.dollar'),
                ]);
                
                $total += $request->quantity[$i] * $request->price[$i];

                $inventory = Inventory::findOrFail($request->product_id[$i]);
                $inventory->stock -= $request->quantity[$i];
                $inventory->update();

                if($inventory->stock == 0)
                {
                    $inventory->update(['status'=>'vendido']);
                }
            }

            if($total != ($request->amountTotalSale + $request->discount)){
                return redirect()->route('sales.create')->with(['message' => 'Error.', 'alert-type' => 'warning']);
            }

            SaleAgent::create([
                'sale_id'=>$sale->id,
                'cashier_id' => $global_cashier['cashier']->id,
                'transaction_id'=>$transaction->id,

                'amount' => $request->amountReceived,
                'agent_id' => Auth::user()->id,
                'agentType' => Auth::user()->role->name,

                'dollarTotal'=> $request->amountReceived/setting('configuracion.dollar'),
                'dollarPrice'=>setting('configuracion.dollar')
            ]);

            DB::commit();
            return redirect()->route('sales.index')->with(['message' => 'Registrado Exitosamente.', 'alert-type' => 'success', 'sale_id' => $sale->id, 'transaction_id'=>$transaction->id]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('sales.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function show($sale)
    {
        $sale = Sale::with(['saleDetails.inventory.item', 'saleAgents.transaction', 'person'])
            ->where('id', $sale)
            ->first();
        
        // return $sale;
        return view('sales.read', compact('sale'));
    }

    public function printTransaction($id, $transaction_id)
    {
        $sale = Sale::with(['person'])->where('id', $id)->first();
       

        $saleAgent = SaleAgent::where('deleted_at', null)
            ->where('transaction_id', $transaction_id)
            ->first();
        $transaction = Transaction::find($transaction_id);
        return view('sales.print.print-transaction', compact('sale', 'saleAgent', 'transaction'));
    }

    public function prinf($id)
    {
        // return $id;
        $sale = Sale::with(['saleDetails.inventory.item', 'saleAgents.transaction', 'person'])
            ->where('id', $id)
            ->where('deleted_at', null)
            ->first();
        // return $sale;
        return view('sales.print.print', compact('sale'));
    }

    public function salePyment(Request $request, $id)
    {
        $sale = Sale::where('deleted_at', null)->where('id', $id)->first();
        if(!$sale){
            return redirect()->route('sales.index')->with(['message' => 'El registro no se encuentra disponible.', 'alert-type' => 'warning']);
        }
        if($request->payment_amount == 0){
            return redirect()->route('sales.show',['sale'=>$sale->id])->with(['message' => 'Ingrese un monto mayor a cero.', 'alert-type' => 'warning']);
        }
        if(!$this->cashierOpen()){
            return redirect()->route('sales.show',['sale'=>$sale->id])->with(['message' => 'Debe abrir caja.', 'alert-type' => 'warning']);
        }
        DB::beginTransaction();
        try {
            $code = Transaction::all()->max('id');
            $code = $code?$code:0;
            $transaction = Transaction::create(['type'=>$request->payment_type, 'transaction'=>$code+1, 'category'=>'venta']);

            SaleAgent::create([
                'sale_id'=>$sale->id,
                'cashier_id' => $this->cashierOpen()->id,
                'transaction_id'=>$transaction->id,

                'amount' => $request->payment_amount,
                'agent_id' => Auth::user()->id,
                'agentType' => Auth::user()->role->name,

                'dollarTotal'=> $request->payment_amount/setting('configuracion.dollar'),
                'dollarPrice'=>setting('configuracion.dollar')
            ]);

            $sale->debt -= $request->payment_amount;
            $sale->datePayment=$request->next_payment_date;
            $sale->update();


            CashierMovement::where('cashier_id', $this->cashierOpen()->id)->where('deleted_at', null)->first()->increment('balance', $request->payment_amount);

            DB::commit();
            return redirect()->route('sales.show',['sale'=>$sale->id])->with(['message' => 'Registrado Exitosamente.', 'alert-type' => 'success', 'sale_id' => $sale->id, 'transaction_id'=>$transaction->id]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('sales.show', ['sale'=>$sale->id])->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }



}
