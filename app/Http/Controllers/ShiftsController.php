<?php

namespace App\Http\Controllers;

use App\Models\Hour;
use App\Models\Shifts;
use App\Models\ShiftsHour;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use PhpParser\Node\Stmt\Return_;

class ShiftsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('adm-attendances.shifts.browse');
    }

    public function list($search = null){
        $user = Auth::user();
        $paginate = request('paginate') ?? 10;

        
 
        $data = Shifts::where(function($query) use ($search){
                    $query->OrWhereRaw($search ? "id = '%$search%'" : 1)
                    ->OrWhereRaw($search ? "name like '%$search%'" : 1);            
                })
        ->where('deleted_at', NULL)->orderBy('id', 'DESC')->paginate($paginate);

        // dump($data);

        return view('adm-attendances.shifts.list', compact('data'));        
    }

    public function create()
    {
        $id = 0;
        $shift = NULL;
        $shiftsHour= NULL;


        $hour = Hour::where('deleted_at', null)->get();
        return view('adm-attendances.shifts.add', compact('shift', 'hour', 'shiftsHour'));
    }

    public function nameStore($id, $name, $description)
    {
        $user = Auth::user();
        if($id == 0)
        {
            $id = Shifts::create([
                'name'=>$name?$name:'',
                'description' => $description?$description:'',

                'register_userId'=>$user->id,
                'register_agentType' =>$user->role->name
            ]);
            return $id->id;
        }
        else
        {
            $id = Shifts::where('id', $id)->first();
            $id->update([
                'name'=>$name!=0?$name:'',
                'description' => $description?$description:'',

            ]);
            return $id->id;
        }
    }



    public function storeShiftsHour(Request $request)
    {
        // return $request;
        DB::beginTransaction();

        $week=[
            'Lunes','Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'
        ];
        $user = Auth::user();

        // return $week;ere
        try {
            if($request->id1 == 0)
            {
                $shifts = Shifts::create([
                    'name'=> 'Ejemplo',
                    'description'=>'ejemplo'
                ]);
                $shifts = $shifts->id;
            }
            else{
                $shifts = $request->id1;
            }

            foreach ($request->hour as $id) {
                $hour = Hour::where('id', $id)->first();
                // dump($hour);
                $shiftsHour = ShiftsHour::where('shifts_id', $shifts)->where('deleted_at', null)->get();
                if($shiftsHour->count()>0)
                {
                    foreach ($request->day as $day) {
                        $ok=true;
                        $shiftsHour = ShiftsHour::where('shifts_id', $shifts)->where('dayWeekNumber', $day)->where('deleted_at', null)->get();
                        foreach ($shiftsHour as $sh) {
                            if($sh->dayWeekNumber == $day)//comparacion
                            {
                                $hourStart = date("H:i", strtotime($hour->hourStart));
                                $hourFinish = date("H:i", strtotime($hour->hourFinish));

                                $shStart = date("H:i", strtotime($sh->hourStart)); // 16:45
                                $shFinish = date("H:i", strtotime($sh->hourFinish)); // 16:45

                                if($hourStart >= $shStart && $hourStart <= $shFinish)
                                {
                                    $ok=false;
                                }

                                if($hourFinish >= $shStart && $hourStart <= $shFinish)
                                {
                                    $ok=false;
                                }
                            }
                        }
                        //si es verdadero y no se interceptan las horas se registra
                        if($ok)
                        {
                            ShiftsHour::create([
                                'shifts_id'=>$shifts,
                                'hour_id'=>$hour->id,

                                'dayWeekNumber'=>$day,
                                'dayWeekName'=>$week[$day-1],

                                'name'=>$hour->name,
                                'hourStart'=>$hour->hourStart,
                                'hourFinish'=>$hour->hourFinish,

                                'minuteLate'=>$hour->minuteLate,
                                'minuteEarly'=>$hour->minuteEarly,
                                
                                'rangeStartInput'=>$hour->rangeStartInput,
                                'rangeStartOutput'=>$hour->rangeStartOutput,

                                'rangeFinishInput'=>$hour->rangeFinishInput,
                                'rangeFinishOutput'=>$hour->rangeFinishOutput,

                                'day'=>$hour->day,

                                'register_userId'=>$user->id,
                                'register_agentType' =>$user->role->name
            
                            ]);
                            $shiftsHour = ShiftsHour::where('shifts_id', $shifts)->where('deleted_at', null)->get();
                        }
                        else
                        {
                            DB::rollBack();
                            return redirect()->route('shifts.show', ['shift'=>$shifts])->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
                        }
                    }

                }
                else
                {
                    foreach($request->day as $day)
                    {
                        ShiftsHour::create([
                            'shifts_id'=>$shifts,
                            'hour_id'=>$hour->id,

                            'dayWeekNumber'=>$day,
                            'dayWeekName'=>$week[$day-1],

                            'name'=>$hour->name,
                            'hourStart'=>$hour->hourStart,
                            'hourFinish'=>$hour->hourFinish,

                            'minuteLate'=>$hour->minuteLate,
                            'minuteEarly'=>$hour->minuteEarly,
                            
                            'rangeStartInput'=>$hour->rangeStartInput,
                            'rangeStartOutput'=>$hour->rangeStartOutput,

                            'rangeFinishInput'=>$hour->rangeFinishInput,
                            'rangeFinishOutput'=>$hour->rangeFinishOutput,

                            'day'=>$hour->day,

                            'register_userId'=>$user->id,
                            'register_agentType' =>$user->role->name
        
                        ]);
                    }
                    
                }
            }

            DB::commit();
            return redirect()->route('shifts.show', ['shift'=>$shifts])->with(['message' => 'Horario registrado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('shifts.show',['shift'=>$shifts])->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }

    }


    public function show($id)
    {
        
        // $id = $id;
        $shift = Shifts::where('id', $id)->where('deleted_at')->first();
        $shiftsHour = ShiftsHour::Where('shifts_id', $id)->where('deleted_at')->orderBy('dayWeekNumber', 'ASC')->get();

        // return $shiftsHour;

        $hour = Hour::where('deleted_at', null)->get();
        return view('adm-attendances.shifts.add', compact('hour', 'shift', 'shiftsHour'));
    }

    public function destroyShiftsHour($shifts, $shiftsHour)
    {
        $user = Auth::user();
        DB::beginTransaction();
        try {
            $aux = ShiftsHour::where('id', $shiftsHour)->where('shifts_id', $shifts)->update([
                'deleted_at' => Carbon::now(),
                'deleted_userId' => $user->id,
                'deleted_agentType' => $user->role->name
            ]);
            DB::commit();
            return redirect()->route('shifts.show', ['shift'=>$shifts])->with(['message' => 'Horario eliminado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('shifts.show', ['shift'=>$shifts])->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function save(Request $request)
    {
        DB::beginTransaction();
        try {
            Shifts::where('id', $request->shift)->where('deleted_at', null)->update(['status'=>'aprobado']);
            DB::commit();
            return redirect()->route('shifts.show', ['shift'=>$request->shift])->with(['message' => 'Turno registrado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('shifts.show', ['shift'=>$request->shift])->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function decline($id)
    {
        DB::beginTransaction();
        try {
            Shifts::where('id', $id)->where('deleted_at', null)->update(['status'=>'rechazado','deleted_at'=>Carbon::now()]);
            DB::commit();
            return redirect()->route('shifts.index')->with(['message' => 'Turno descartado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('shifts.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function destroy(Request $request ,$id)
    {
        DB::beginTransaction();
        try {
            Shifts::where('id', $id)->where('deleted_at', null)->update(['deleted_at'=>Carbon::now(), 'deletedObservation'=>$request->deletedObservation]);
            DB::commit();
            return redirect()->route('shifts.index')->with(['message' => 'Turno eliminado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('shifts.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    
}



