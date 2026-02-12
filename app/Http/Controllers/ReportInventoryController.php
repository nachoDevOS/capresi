<?php

namespace App\Http\Controllers;

use App\Models\Cashier;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReportInventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function inventoryAdd()
    {        
        // $route = Route::where('status', 1)->where('deleted_at', null)->get();
        $query_filter = 'id='.Auth::user()->id;
        if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('gerente') || auth()->user()->hasRole('administrador')){
            $query_filter=1;
        }
        $user = User::whereRaw($query_filter)->where('status', 1)->get();
        // return $user;
        return view('report.inventories.inventoryAdd.report', compact('user'));
    }

    public function inventoryAddList(Request $request)
    {
        $start = $request->start;
        $finish =$request->finish;
        $type = $request->type;
        $typePrint = $request->type;

        // dump($type);

        $type = ($type == 'prendario') ? "typeRegister = 'Prendario'" : $type;
        $type = ($type == 'manual') ? "typeRegister = 'Manual'" : $type;

        $cashier = Cashier::where('user_id', Auth::user()->id)
                    ->where('status', '!=', 'cerrada')
                    ->where('deleted_at', NULL)->count();

        $inventories = Inventory::with(['features', 'item', 'register', 'pawnRegisterDetail'])
            ->where('deleted_at', null)
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $finish)
            ->where('registerUser_id', $request->agent_id)
            ->whereRaw($type?$type:1)
            ->orderBy('id', 'ASC')
            ->get();

        if($request->print){
            $date = $request->date;
            return view('report.inventories.inventoryAdd.print', compact('start', 'finish', 'inventories', 'typePrint'));
        }else{
            return view('report.inventories.inventoryAdd.list', compact('inventories', 'cashier'));
        }
        
    }
}
