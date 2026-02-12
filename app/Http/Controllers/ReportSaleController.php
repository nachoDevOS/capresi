<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;
use App\Models\Cashier;

class ReportSaleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function sale()
    {        
        // $route = Route::where('status', 1)->where('deleted_at', null)->get();
        $query_filter = 'id='.Auth::user()->id;
        if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('gerente') || auth()->user()->hasRole('administrador')){
            $query_filter=1;
        }
        $user = User::whereRaw($query_filter)->where('status', 1)->get();
        // return $user;
        return view('report.sales.sales.report', compact('user'));
    }

    public function saleList(Request $request)
    {
        $start = $request->start;
        $finish =$request->finish;
        $type = $request->type;
        $typePrint = $request->type;

        $type = ($type == 'Contado') ? "typeSale = 'Contado'" : $type;
        $type = ($type == 'Credito') ? "typeSale = 'Credito'" : $type;


        $cashier = Cashier::where('user_id', Auth::user()->id)
                    ->where('status', '!=', 'cerrada')
                    ->where('deleted_at', NULL)->count();

        $sales = Sale::with(['person', 'saleDetails.inventory.item', 'register', 'saleDetails.inventory.features', 'saleAgents'=>function($q){
                $q->where('deleted_at', null)->get();
            }])
            ->whereRaw($type?$type:1)
            ->where('registerUser_id', $request->agent_id)
            ->orderBy('id', 'ASC')
            ->get();

        if($request->print){
            $date = $request->date;
            return view('report.sales.sales..print', compact('start', 'finish', 'sales', 'typePrint'));
        }else{
            return view('report.sales.sales..list', compact('sales', 'cashier'));
        }
        
    }
}
