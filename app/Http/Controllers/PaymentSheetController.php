<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Bonu;
use App\Models\BonuDetail;
use App\Models\Contract;
use App\Models\ContractPayment;
use App\Models\ContractShift;
use Illuminate\Http\Request;
use App\Models\People;
use App\Models\Shifts;
use App\Models\ShiftsHour;
use DateTime;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\LatePenalty;
use Illuminate\Support\Facades\Auth;
use App\Models\CashierMovement;
use App\Models\ContractAdvancement;
use App\Models\Spreadsheet;
use App\Models\SpreadsheetContract;

class PaymentSheetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        return view('paymentSheet.browse');
    }

    public function list(Request $request)
    {
        $cashier = $this->cashierOpen();
        // dump(json_encode($contract, JSON_PRETTY_PRINT));
        $dayWeekList = [
            'Monday' => 'Lunes',
            'Tuesday' => 'Martes',
            'Wednesday' => 'Miercoles',
            'Thursday' => 'Jueves',
            'Friday' => 'Viernes',
            'Saturday' => 'Sabado',
            'Sunday' => 'Domingo'
        ];
        $people_id = $request->people_id;

        if($request->type == 'aguinaldo')
        {
            $bonuses = Bonu::with(['bonuDetail'=>function($query) use ($people_id){
                    $query->where('paid', 0)
                    ->whereHas('people', function ($query) use ($people_id) {
                        $query->where('id', $people_id); 
                    });
                }, 'bonuDetail.people'])
                ->whereHas('bonuDetail', function ($query) use ($people_id) {
                    $query->where('paid', 0)
                    ->whereHas('people', function ($query) use ($people_id) {
                        $query->where('id', $people_id); 
                    });
                })
                ->where('deleted_at', null)
                ->get();

            // dump(json_encode($bonuses, JSON_PRETTY_PRINT));


            return view('paymentSheet.list-bonuses', compact('bonuses', 'cashier'));
        }
        else
        {
            $spreadsheets = Spreadsheet::with([
                    'spreadsheetContract' => function ($query) use ($people_id) {
                        $query->where('paid', 0) 
                            ->whereHas('contract.people', function ($query) use ($people_id) {
                                $query->where('id', $people_id); 
                            });
                    },
                    'spreadsheetContract.contract.people'
                ])
                ->where('deleted_at', null) 
                ->where('status', 'aprobado') 
                ->whereHas('spreadsheetContract', function ($query) use ($people_id) {
                    $query->where('paid', 0)
                        ->whereHas('contract.people', function ($query) use ($people_id) {
                            $query->where('id', $people_id); 
                        });
                })
                
                ->get();
            return view('paymentSheet.list', compact('spreadsheets', 'cashier'));
        }
    }


    public function save_spreadsheet($type, $id)
    {        
        $cashier = $this->cashierOpen();
        if (!$cashier) {
            return response()->json([
                'message' => 'No cuenta con caja abierta para poder realizar la operacion.',
                'type'=> 'Error',
                'viewclose' => true
            ]);
        }
        $user = Auth::user();
        DB::beginTransaction();
        try {
            $total_caja = 0;
            foreach ($cashier->movements as $movement) {
                if($movement->type == 'ingreso'){
                    $total_caja += $movement->balance;
                }elseif($movement->type == 'egreso'){
                    $total_caja -= $movement->amount;
                }
            }
            if($type== 'periodo')
            {
                $spreadsheetContract = SpreadsheetContract::with(['contract.people', 'spreadsheet'])
                    ->whereHas('spreadsheet', function($query) {
                        $query->where('deleted_at', null); 
                    })
                    ->where('id', $id)
                    ->where('paid', 0)
                    ->where('deleted_at', null)
                    ->first();
                

                if ($total_caja < $spreadsheetContract->liquidPaid) {
                    return response()->json([
                        'message' => 'No cuenta con suficiente saldo en caja para poder realizar la operacion.',
                        'type'=> 'Error',
                        'viewclose' => true
                    ]);
                }
                // return $view;
                //:::::::::                
                
                $movement = CashierMovement::create([
                    'user_id' => Auth::user()->id,
                    'cashier_id' => $cashier->id,
                    'amount' => $spreadsheetContract->liquidPaid,
                    'description' => 'Pagos de sueldo',
                    'type' => 'egreso',
                    'status'=>'Aceptado'
                ]);
                $spreadsheetContract->update([
                    'paid'=>1,
                    'cashier_id'=>$cashier->id,
                    'cashierMovement_id'=>$movement->id,
                    'paidDate'=>Carbon::now(),
                    'paid_userId'=>Auth::user()->id,
                    'paid_agentType'=>Auth::user()->role->name,
                ]);

                $ok = SpreadsheetContract::where('spreadsheet_id', $spreadsheetContract->spreadsheet_id)->where('deleted_at', null)
                    ->where('paid', 0)
                    ->get();

                $spreadsheet =Spreadsheet::where('id', $spreadsheetContract->spreadsheet_id)
                    ->where('deleted_at', null)
                    ->first();

                if(count($ok)==0)
                {
                    $spreadsheet->update([
                        'paid' => 1,
                        'status'=>'finalizado'
                    ]);
                }

                $contract = Contract::where('id', $spreadsheetContract->contract_id)
                    ->where('deleted_at', null)
                    ->first();

                $dateFinish = new DateTime($contract->dateFinish);
                $dateFinishMonth = $dateFinish->format('m');
                $dateFinishYear = $dateFinish->format('Y');
                
                if($spreadsheet->month == $dateFinishMonth && $spreadsheet->year == $dateFinishYear)
                {
                    $contract->update([
                        'status'=>'finalizado'
                    ]);
                }
            }
            else
            {
                $bonuDetail = BonuDetail::where('id', $id)
                    ->where('paid', 0)
                    ->where('deleted_at', null)
                    ->first();
                

                if ($total_caja < $bonuDetail->payment	) {
                    return response()->json([
                        'message' => 'No cuenta con suficiente saldo en caja para poder realizar la operacion.',
                        'type'=> 'Error',
                        'viewclose' => true
                    ]);
                }

                //:::::::::                
                
                $movement = CashierMovement::create([
                    'user_id' => Auth::user()->id,
                    'cashier_id' => $cashier->id,
                    'amount' => $bonuDetail->payment,
                    'description' => 'Pagos de Aguinaldo',
                    'type' => 'egreso',
                    'status'=>'Aceptado'
                ]);
                
                $bonuDetail->update([
                    'paid'=>1,
                    'cashier_id'=>$cashier->id,
                    'cashierMovement_id'=>$movement->id,
                    'paidDate'=>Carbon::now(),
                    'paid_userId'=>Auth::user()->id,
                    'paid_agentType'=>Auth::user()->role->name,
                ]);

                //para poner la planilla en pago
                $ok = BonuDetail::where('bonu_id', $bonuDetail->bonu_id)->where('deleted_at', null)
                    ->where('paid', 0)
                    ->get();

                $bonu =Bonu::where('id', $bonuDetail->bonu_id)
                    ->where('deleted_at', null)
                    ->first();

                if(count($ok)==0)
                {
                    $bonu->update([
                        'paid' => 1,
                        'status'=>'finalizado'
                    ]);
                }
            }
 
            DB::commit();
            return response()->json([
                'message' => 'La operación ha sido procesada correctamente.',
                'type'=> 'Información',
                'viewclose' => false
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Ocurrió un error..',
                'type'=> 'Error',
                'viewclose' => true
            ]);
        }
    }

    public function print_payment($type, $id)
    {
        if($type == 'periodo')
        {
            $spreadsheetContract = SpreadsheetContract::with(['contract.people', 'spreadsheet'])
                ->where('id', $id)
                ->first();
            return view('paymentSheet.print-payment', compact('spreadsheetContract'));
        }
        else
        {
            // return 1010;
            $bonuDetail = BonuDetail::with(['people', 'bonu'])
                ->where('id', $id)
                ->first();
            return view('paymentSheet.print-payment-bonu', compact('bonuDetail'));
        }
    }

}
