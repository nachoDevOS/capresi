<?php

namespace App\Http\Controllers;

use App\Models\Hour;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class HourController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        return view('adm-attendances.hours.browse');
    }

    public function list($search = null){
        $user = Auth::user();
        $paginate = request('paginate') ?? 10;

 
        $data = Hour::where(function($query) use ($search){
                    $query->OrWhereRaw($search ? "id = '%$search%'" : 1)
                    ->OrWhereRaw($search ? "name like '%$search%'" : 1);            
                })
        ->where('deleted_at', NULL)
        ->where('status', 'normal')
        ->orderBy('id', 'DESC')->paginate($paginate);

        return view('adm-attendances.hours.list', compact('data'));        
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        // return $request;
        try {

            $user = Auth::user();
            Hour::create([
                'name'=>$request->name,
                'hourStart'=>$request->hourStart,
                'hourFinish'=>$request->hourFinish,

                'minuteLate'=>$request->minuteLate,
                'minuteEarly'=>$request->minuteEarly,
                
                'rangeStartInput'=>$request->rangeStartInput,
                'rangeStartOutput'=>$request->rangeStartOutput,

                // 'rangeFinishInput'=>$request->rangeFinishInput,
                'rangeFinishInput'=>$request->hourFinish,
                'rangeFinishOutput'=>$request->rangeFinishOutput,

                'description'=>$request->description,
                'day'=>$request->day,



                'register_userId'=>$user->id,
                'register_agentType' =>$user->role->name
            ]);
            DB::commit();
            return redirect()->route('hours.index')->with(['message' => 'Horario registrado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('hours.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function destroyHour($hour)
    {
        DB::beginTransaction();
        try {
            $hour = Hour::where('id', $hour)->first();
            $hour->update([
                'deleted_at'=>Carbon::now()
            ]);
            DB::commit();
            return redirect()->route('hours.index')->with(['message' => 'Eliminado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('hours.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }
}
