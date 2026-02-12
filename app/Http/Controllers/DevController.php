<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PawnRegister;
use App\Models\PawnRegisterMonth;
use Illuminate\Support\Carbon;
// use App\Http\Middleware\Auth;
use Illuminate\Support\Facades\Auth;
use App\Models\Cashier;
use App\Models\CashierMovement;

class DevController extends Controller
{
    
    public function moneyDeliver(Request $request, $id)
    {
        $pawn=$id;
        DB::beginTransaction();
        try {
            $pawnRegister = PawnRegister::with(['details'])
                ->where('id', $pawn)->first();

            $request['date'] = $pawnRegister->date;
            $request['cashier_id'] = 3;

            return $request;
            return $pawnRegister;

            if($pawnRegister->status== 'entregado')
            {
                DB::rollBack();
                return redirect()->route('pawn.index')->with(['message' => 'El Prestamo ya fue entregado', 'alert-type' => 'error']);
            }

            $aux =1;
            $date = $request->date?$request->date:Carbon::now();
            while ($aux <= $pawnRegister->cantMonth) {
                $date = $this->month_next($date);
                $aux++;
            }

            $pawnRegister->update([
                'cashier_id'=>$request->cashier_id,
                'date_limit'=>$date,
                'dateDelivered'=>$request->date?$request->date:Carbon::now(),
                'delivered_userId'=>Auth::user()->id,
                'delivered_userType' => Auth::user()->role->name,
                'status'=>'entregado'
            ]);

         
            // $movement = CashierMovement::where('cashier_id', $request->cashier_id)->where('deleted_at', null)->where('type', 'ingreso')->get();

            // $amountLoan=0;
            // foreach ($pawnRegister->details as $detail)
            // {
            //     $amountLoan += $detail->amountTotal;
            // }
            // $loan = $amountLoan;
            
            // if($amountLoan > $this->cashierOpenAmountBalance(Auth::user()->id)->getData()->balance)
            // {
            //     DB::rollBack();
            //     return redirect()->route('pawn.index')->with(['message' => 'No hay suficiente dinero en caja', 'alert-type' => 'error']);
            // }

            // foreach($movement as $item)
            // {
            //     if($item->balance > 0 && $amountLoan > 0)
            //     {
            //         if($item->balance >= $amountLoan)
            //         {
            //             $item->decrement('balance', $amountLoan);
            //             $amountLoan = 0;
            //         }
            //         else
            //         {
            //             $amountLoan = $amountLoan - $item->balance;
            //             $item->decrement('balance', $item->balance);
            //         }
            //     }
            // }       
           
            PawnRegisterMonth::create([
                    'pawnRegister_id' => $pawnRegister->id,
                    'start' => date('Y-m-d', strtotime($pawnRegister->dateDelivered.' + 1 days')) ,
                    'finish' => $this->month_next($pawnRegister->dateDelivered),
                    'interest' => $loan*($pawnRegister->interest_rate/100),
                    'debt' => $loan*($pawnRegister->interest_rate/100),
                ]);
            // sleep(2);
            DB::commit();
            return redirect()->route('pawn.index')->with(['message' => 'Dinero entregado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return 'Error';
            return redirect()->route('pawn.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

}
