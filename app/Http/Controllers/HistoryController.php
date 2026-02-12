<?php

namespace App\Http\Controllers;

use App\Models\HistoryReportDailyList;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class HistoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function indexDailyList()
    {  
        return view('report.history.dailyList.browse');
    }

    public function listDailyList(){
        $paginate = request('paginate') ?? 10;
        $search = request('search') ?? null;
        $status = request('status') ?? null;

        $data = HistoryReportDailyList::with(['route'])
            ->where(function($query) use ($search){
                    $query
                    ->OrwhereHas('route', function($query) use($search){
                        $query->whereRaw($search ? 'name like "%'.$search.'%"' : 1);                    
                    })
                    ->OrWhereRaw($search ? "id = '$search'" : 1);
                })
            ->whereRaw($status ? " type = '".$status."'" : 1)
            ->where('deleted_at', null)
            ->orderBy('id', 'DESC')
            ->paginate($paginate);


        return view('report.history.dailyList.list', compact('data'));
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $history = HistoryReportDailyList::where('id', $id)->first();

            $history->update([
                'deleted_at' => Carbon::now(),
                'deletedUser_id' => Auth::user()->id,
                'deletedRole' => Auth::user()->role->name
            ]);

            DB::commit();
            return redirect()->route('history-dailyList.index')->with(['message' => 'Registrado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('history-dailyList.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function printDailyList($id)
    {
        $data = HistoryReportDailyList::with(['route', 'details.loan.people'])
            ->where('id', $id)
            ->first();

        // return $data;
        return view('report.history.dailyList.print', compact('data'));
    }
}
