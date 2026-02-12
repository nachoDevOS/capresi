<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Contract;
use App\Models\ContractAdvancement;
use App\Models\ContractDay;
use App\Models\ContractDayAttendance;
use App\Models\Hour;
use App\Models\LatePenalty;
use App\Models\ShiftsHour;
use Illuminate\Http\Request;
use App\Models\Spreadsheet;
use App\Models\SpreadsheetContract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use PhpParser\Builder\Function_;
use PhpParser\Node\Expr\FuncCall;
use DateTime;
use Spatie\LaravelIgnition\Recorders\DumpRecorder\Dump;

class SpreadsheetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('administration.spreadsheets.browse');
    }

    public function list($type, $search = null){
        $user = Auth::user();
        $paginate = request('paginate') ?? 10;

        switch($type)
        {
            case 'pendiente':
                $data = Spreadsheet::
                    where(function($query) use ($search){
                        if($search){
                            // $query->OrwhereHas('people', function($query) use($search){
                            //     $query->whereRaw("(ci like '%$search%' or first_name like '%$search%' or last_name1 like '%$search%' or last_name2 like '%$search%' or CONCAT(first_name, ' ', last_name1, ' ', last_name2) like '%$search%')");
                            // })
                            $query->OrWhereRaw($search ? "id = '%$search%'" : 1)
                            ->OrWhereRaw($search ? "description like '%$search%'" : 1);
                        }
                    })
                    ->where('status', 'pendiente')->where('deleted_at', NULL)->orderBy('id', 'DESC')->paginate($paginate);

                return view('administration.spreadsheets.list', compact('data'));
                break;
      
                    
            case 'aprobado':
                $data = Spreadsheet::
                    where(function($query) use ($search){
                        if($search){
                            $query->OrWhereRaw($search ? "id = '%$search%'" : 1)
                            ->OrWhereRaw($search ? "description like '%$search%'" : 1);
                        }
                    })
                    // ->where('status', 'aprobado')
                    ->whereIn('status', ['aprobado', 'finalizado'])

                    ->where('deleted_at', NULL)->orderBy('id', 'DESC')->paginate($paginate);

                return view('administration.spreadsheets.list', compact('data'));
                break;
            case 'eliminado':
                $data = Spreadsheet::
                    where(function($query) use ($search){
                        if($search){
                            // $query->OrwhereHas('people', function($query) use($search){
                            //     $query->whereRaw("(ci like '%$search%' or first_name like '%$search%' or last_name1 like '%$search%' or last_name2 like '%$search%' or CONCAT(first_name, ' ', last_name1, ' ', last_name2) like '%$search%')");
                            // })
                            $query->OrWhereRaw($search ? "id = '%$search%'" : 1)
                            ->OrWhereRaw($search ? "description like '%$search%'" : 1);
                        }
                    })
                    ->where('deleted_at', '!=', null)->orderBy('id', 'DESC')->paginate($paginate);

                return view('administration.spreadsheets.list', compact('data'));
                break;
            case 'todo':
                $data = Spreadsheet::
                    where(function($query) use ($search){
                        if($search){
                            // $query->OrwhereHas('people', function($query) use($search){
                            //     $query->whereRaw("(ci like '%$search%' or first_name like '%$search%' or last_name1 like '%$search%' or last_name2 like '%$search%' or CONCAT(first_name, ' ', last_name1, ' ', last_name2) like '%$search%')");
                            // })
                            $query->OrWhereRaw($search ? "id = '%$search%'" : 1)
                            ->OrWhereRaw($search ? "description like '%$search%'" : 1);
                        }
                    })
                    ->orderBy('id', 'DESC')->paginate($paginate);


                return view('administration.spreadsheets.list', compact('data'));
                break;

        } 
    }

    public function create()
    {
        // $employeJob = EmployeJob::where('deleted_at', null)->get();

        // $shifts = Shifts::where('status', 'aprobado')->where('deleted_at',null)->get();
        return view('administration.spreadsheets.add');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        // return $request;

        DB::beginTransaction();
        try {
            [$year, $month] = explode('-', $request->input('month-year'));
            Spreadsheet::create([
                'month' => (int) $month,
                'year' => (int) $year,

                'description'=>$request->description,


                'register_userId'=>$user->id,
                'register_agentType' =>$user->role->name
            ]);
            // return 1;
            DB::commit();
            return redirect()->route('spreadsheets.index')->with(['message' => 'Planilla creada exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollback();
            // return 0;
            return redirect()->route('spreadsheets.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();

            $s = Spreadsheet::where('id', $id)->where('deleted_at', null)->first();
            if($s->status == 'aprobado')
            {
                return redirect()->route('spreadsheets.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
            }

            Spreadsheet::where('id', $id)->update([
                'deleted_at' => Carbon::now(),

                'deleted_userId' => $user->id,
                'deleted_agentType' => $user->role->name,
                'deleted_observation' => $request->observation
            ]);

            
            DB::commit();
            return redirect()->route('spreadsheets.index')->with(['message' => 'Anulado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('spreadsheets.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function rechazar(Request $request, $spreadsheet)
    {
        // return $contract;
        // return $request;
        DB::beginTransaction();
        try {
            Spreadsheet::where('id', $spreadsheet)->update([
                'status' => 'rechazado',
            ]);


            DB::commit();
            return redirect()->route('spreadsheets.index')->with(['message' => 'Rechazado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('spreadsheets.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    function timeToSeconds($time) {
        list($hours, $minutes, $seconds) = explode(":", $time);
        return ($hours * 3600) + ($minutes * 60) + $seconds; 
    }
    function sumTime($time1, $time2)
    {
        $time1_seconds = $this->timeToSeconds($time1);
        $time2_seconds = $this->timeToSeconds($time2);
        // dump($minuteLate);

        $total_seconds = $time1_seconds + $time2_seconds;

        // Convertimos el total de segundos de vuelta a formato H:i:s
        $total_hours = floor($total_seconds / 3600);
        $total_minutes = floor(($total_seconds % 3600) / 60);
        $total_seconds = $total_seconds % 60;

        // extraems el resultado en H:i:s
        return $minuteLate = sprintf('%02d:%02d:%02d', $total_hours, $total_minutes, $total_seconds);
        
    }

    public function generate($spreadsheet)
    {
        DB::beginTransaction();
        try {
            $spreadsheet = Spreadsheet::where('id', $spreadsheet)->where('deleted_at', null)->first();

            $mesAnio = $spreadsheet->year.'-'.$spreadsheet->month;
            list($anio, $mes) = explode('-', $mesAnio); // Separar el año y el mes
            
            $inicioRango = Carbon::create($anio, $mes, 1)->startOfMonth(); // Inicio del mes
            $finRango = Carbon::create($anio, $mes, 1)->endOfMonth();      // Fin del mes

            $contract = Contract::with(['contractDay' => function ($query) use ($inicioRango, $finRango) {
                $query->where('deleted_at', null)->where('spreadsheet', 0) // Solo los contractDay que no esten en una planilla
                      ->whereBetween('date', [$inicioRango, $finRango]); // date dentro del rango
            },'contractDay.contractDayAttendance'=>function($q){
                $q->where('deleted_at', null);
            }
            ])
            ->where('deleted_at', null)
            ->where('status', 'aprobado')
            ->whereHas('contractDay', function ($query) use ($inicioRango, $finRango) {
                $query->where('deleted_at', null)->where('spreadsheet', 0) // Solo los contractDay que no esten en una planilla
                      ->whereBetween('date', [$inicioRango, $finRango]); // date dentro del rango
            }) // Asegura que la relación contractDay tenga datos
            ->get();

            
            // return $contract;

            $late = LatePenalty::where('deleted_at', null)->get();
            foreach ($contract as $itemContract) {
                $paymentDayAux =0;
                $totaMinuteLate="00:00:00";
                $totalLostHour = 0;
                $totalPayment =0;
                $totalDayWorked =0;
                $totalDayWorkedFebrary =0;
                $dateStarts = NULL;
                $dateFinishs = null;
                // if($itemContract->id==6)
                // {
                //     // return $itemContract;
                //     dump($itemContract->people->ci);
                //     // dump($dayStartContract);
                // }


                $monthStartContract = date('m', strtotime($itemContract->dateStart)); //Para identificar el en que mes se inicio el contrato
                $dayStartContract = date('d', strtotime($itemContract->dateStart)); //Para identificar en que dia se inicio el contrato

                // if($itemContract->id==6)
                // {
                //     // return $itemContract;
                //     dump($monthStartContract);
                //     dump($dayStartContract);
                // }
                // return $monthStart;


                foreach ($itemContract->contractDay as $itemContractDay) {//recorro todos los dias del mes

                    // $paymentDay = 0;  //Para sumaar
                    $minuteLate ="00:00:00";
                    $cantHour = 0;//Para obtener el total de horario o horas de ese dia 
                    $lostHour = 0;//Para obtener el total de horas o horarios perdido o abandonado en Bs.
                    $attendance = Attendance::where('date', Carbon::parse($itemContractDay->date)->format('Y-m-d'))
                                    ->where('spreadsheet', 0)
                                    ->where('ci', $itemContract->people->ci)
                                    ->get();//obtenemos todas las asistencia del dia o fecha conforme incremente
                    if($itemContract->id==6 )
                    {
                        // return $attendance;
                        // dump($attendance);
                        // dump($itemContractDay->date);
                        // dump('::::::::::::::::::::::::::::::::::::');
                    }
                    foreach ($itemContractDay->contractDayAttendance as $itemContractDayAttendance) {//recorro las horas o horarios del dia
                        // dump($itemContractDayAttendance);
                        // return $itemContractDay;
                        // return $itemContractDayAttendance;
                        if($itemContractDayAttendance->license_id == null)
                        {
                            $cantHour++;
                            $okStart = true;
                            $okFinish = true;

                            //Para saber si pertenece a un turno o a un horario
                            if($itemContractDayAttendance->shiftHour_id){
                                $shiftHour = ShiftsHour::where('id', $itemContractDayAttendance->shiftHour_id)->where('deleted_at', null)->first();
                            }
                            else
                            {
                                $shiftHour = Hour::where('id', $itemContractDayAttendance->hour_id)->where('deleted_at', null)->first();
                            }

                            foreach ($attendance as $itemAttendance) //recorremos  las asistemcias de la BD pero que no pertenesca a una planilla
                            {
                                if(($itemAttendance->hour >= $shiftHour->rangeStartInput && $itemAttendance->hour <=$shiftHour->rangeStartOutput) && $itemAttendance->spreadsheet==0 && $okStart)
                                {
                                    $itemContractDayAttendance->update([
                                        'attendaceStart_id'=>$itemAttendance->id,
                                        'start'=>$itemAttendance->hour
                                    ]);
                                    $itemAttendance->update([
                                        'spreadsheet'=>1
                                    ]);

                                    //le sumo los 10 minutos a la hora de entrada establecida del sistema mas los minutos de tolerancia
                                    $hourAddLate = new DateTime($shiftHour->hourStart);
                                    // $hourAddLate = $hourAddLate->modify('+10 minutes');
                                    $hourAddLate = $hourAddLate->modify('+'.$shiftHour->minuteLate.' minutes');
                                    // dump($hourAddLate);
            
                                    $hourAddLate =$hourAddLate->format('H:i:s');
            
                                    $hourAttendances = $itemAttendance->hour;
                                    // dump($hourAttendances);
            
                                    if ($hourAttendances > $hourAddLate) { //Para sumar las horas que pasan de la toleranca
                                        // Convertir ambas horas a objetos DateTime para poder comparar
                                        $hourAddLate = new DateTime($hourAddLate);
                                        $hourAttendances = new DateTime($hourAttendances);
                                        
                                        // Calcular la diferencia entre la hora de entrada real y la hora con tolerancia
                                        $diff = $hourAddLate->diff($hourAttendances);
                                    
                                        // Obtener los minutos de retraso
                                        $minuteTotal = ($diff->h * 60) + $diff->i; // Convertir horas a minutos y sumarlas
                                        $secondTotal = $diff->s; // Obtener los segundos de la diferencia

                                        // Aquí sumamos el tiempo total de retraso
                                        $totalMinutes = $minuteTotal + $shiftHour->minuteLate;  // 10 minutos de tolerancia + minutos de retraso
                                        // $totalMinutes = $minuteTotal + 120;  // 10 minutos de tolerancia + minutos de retraso
        
                                        // Si hay más de 60 minutos, convertir los minutos en horas y minutos
                                        $totalHours = floor($totalMinutes / 60);  // Horas
                                        $totalMinutes = $totalMinutes % 60;      // Minutos restantes

                                        $totalTime = sprintf('%02d:%02d:%02d', $totalHours, $totalMinutes, $secondTotal);
                                        $minuteLate = $this->sumTime($totalTime, $minuteLate);
                                    }
                                    $okStart=false;
                                }

                                if(($itemAttendance->hour >= $shiftHour->rangeFinishInput && $itemAttendance->hour <=$shiftHour->rangeFinishOutput)&& $itemAttendance->spreadsheet==0 && $okFinish)
                                {
                                    // dump($shiftHour->rangeFinishInput);
                                    $itemContractDayAttendance->update([
                                        'attendaceFinish_id'=>$itemAttendance->id,
                                        'finish'=>$itemAttendance->hour
                                    ]);
                                    $itemAttendance->update([
                                        'spreadsheet'=>1
                                    ]);
                                    $okFinish=false;
                                }      
                            } 
                            

                            if($itemContractDayAttendance->start == null || $itemContractDayAttendance->finish == null)
                            {
                                $lostHour+= $itemContractDayAttendance->amount;//acumulo el total en Bs. de los dias abandonados

                                $itemContractDayAttendance->update([
                                    'lostHour'=>1
                                ]);
                            }
                        }

                    }

                    //###########################################################################
                    $itemContractDay->update([
                        'minuteLate'=>$minuteLate,
                        'cantHour' => $cantHour,
                        'cantHourAmount' => $lostHour,
                        'spreadsheet'=>1
                    ]);
  
                    $day = date('d', strtotime($itemContractDay->date));

                    // if($dateStarts==NULL)
                    // {
                    //     $dateStarts=$day;
                    // }
                    $dateStarts??$dateStarts=$itemContractDay->date;
                    $dateFinishs= $itemContractDay->date;

                    $totaMinuteLate = $this->sumTime($minuteLate,$totaMinuteLate);
                    $paymentDayAux = $itemContractDay->paymentDay;//sueldo al dia
                    $totalLostHour+=$lostHour;
                    if ($day != '31')//hara una excepcion del dia 31 para que no lo tome en cuenta 
                    {
                        $totalPayment+= $itemContractDay->paymentDay;
                        $totalDayWorked++;
                    }
                    if($mes==1 && $day==2)
                    {
                        $totalPayment+= $itemContractDay->paymentDay;
                    }


                    // return $itemContractDay;
                    
                }
                // if($mes==2 && )
                if($mes == 2)
                {
                    if($monthStartContract == 2 && $dayStartContract == 1)
                    {
                        if($day == 29)
                        {
                            $totalDayWorkedFebrary=1;
                            $totalPayment = $totalPayment+$paymentDayAux;
                        }
                        if($day == 28)
                        {
                            $totalDayWorkedFebrary=2;
                            $totalPayment = $totalPayment+($paymentDayAux*2);
                        }
                    }

                    if($monthStartContract != 2)
                    {
                        if($day == 29)
                        {
                            $totalDayWorkedFebrary=1;
                            $totalPayment = $totalPayment+$paymentDayAux;
                        }
                        if($day == 28)
                        {
                            $totalDayWorkedFebrary=2;
                            $totalPayment = $totalPayment+($paymentDayAux*2);
                        }
                    }
                    
                }


                // Dividido la cadena de la hora total de cada contract
                list($hora, $minutos, $segundos) = explode(":", $totaMinuteLate);
                // Convertirmo todo a minutos
                $minuteT = ($hora * 60) + $minutos ;

                $minuteD = 0;
                foreach ($late as  $index => $lat) {
                    if ($minuteT >= $lat->start && $minuteT <= $lat->finish ) 
                    {
                        $minuteD = $lat->amount;
                    }

                    // Verifica si esta es la última iteración
                    if ($index === $late->count() - 1) {
                        if ($minuteT > $lat->finish ) 
                        {
                            $minuteD = $lat->amount;
                        }
                    }
                }
                
                

                $advencement = ContractAdvancement::where('contract_id', $itemContract->id)
                ->where('periodMonth', $mes)
                ->where('periodYear', $anio) 
                ->where('deleted_at', null)
                ->get()
                ->SUM('advancement');

                $cAdvencement = ContractAdvancement::where('contract_id', $itemContract->id)
                ->where('periodMonth', $mes)
                ->where('periodYear', $anio) 
                ->where('deleted_at', null)
                ->get();
                foreach ($cAdvencement as $ca) {
                    $ca->update([
                        'spreadsheet' =>1
                    ]);
                }

                // return number_format($totalPayment,2);
                // return number_format($totalPayment,2, '.', '')-$minuteD-$totalLostHour-$advencement;

                $spreadsheetContact = SpreadsheetContract::create([
                    'dateStart' => $dateStarts,
                    'dateFinish' => $dateFinishs,
                    'contract_id' => $itemContract->id,
                    'spreadsheet_id' => $spreadsheet->id,
                    'advancement' => $advencement,
                    'dayWorked'=> $totalDayWorked,
                    'dayWorkedFebrary'=> $totalDayWorkedFebrary,

                    'minuteLate'=>$totaMinuteLate, //acumula todo los minutos de todos los dias

                    'cantHourAmount'=>number_format($totalLostHour,2, '.', ''),
                    'minuteLateAmount'=>number_format($minuteD,2, '.', ''),
                    'salary'=> number_format($itemContract->salary,2, '.', ''),
                    'payment'=> number_format($totalPayment,2, '.', ''),
                    'liquidPaid'=>number_format(number_format($totalPayment,2, '.', '')-$minuteD-$totalLostHour-$advencement,2, '.', '')

                    // 'cantHourAmount'=>number_format($totalLostHour, 2, '.', ''),
                    // 'minuteLateAmount'=>number_format($minuteD, 2, '.', ''),
                    // 'payment'=> number_format($totalPayment, 2, '.', ''),
                    // 'liquidPaid'=>number_format(number_format($totalPayment, 2, '.', '')-number_format($minuteD, 2, '.', '')-number_format($totalLostHour, 2, '.', '')-number_format($advencement, 2, '.', ''), 2, '.', '')   
                ]);
            }

            $spreadsheet->update([
                'status'=>'aprobado'
            ]);


            // return $spreadsheetContact;
            DB::commit();
            return redirect()->route('spreadsheets.index')->with(['message' => 'Planilla generada exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return 0;
            return redirect()->route('spreadsheets.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function printSpreadSheet($spreadsheet)
    {
        // return $spreadsheet;
        $spreadsheet = Spreadsheet::with(['spreadsheetContract.contract.people'])
            ->where('id', $spreadsheet)->where('deleted_at', null)->first();



        return view('administration.spreadsheets.print', compact('spreadsheet'));

    }
}
