<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ContractAdvancement;
use App\Models\EmployeJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Models\Cashier;
use App\Models\CashierMovement;
use App\Models\ContractDay;
use App\Models\ContractDayAttendance;
use App\Models\ContractShift;
use App\Models\Hour;
use App\Models\License;
use App\Models\Shifts;
use App\Models\ShiftsHour;
use App\Models\Spreadsheet;
use DateTime;
use DateInterval;
use DatePeriod;

// use datetime

class ContractController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        // $data = Contract::where('deleted_at', null)->where('status', 'aprobado')->get();
        // foreach ($data as $item) {
        //     if ($item->dateFinish<date('Y-m-d')) {
        //         $item->update([
        //             'status'=>'finalizado'
        //         ]);
        //     }
        // }
        
        return view('administration.contracts.browse');
    }

    public function list($type, $search = null){
        $user = Auth::user();
        $paginate = request('paginate') ?? 10;

        switch($type)
        {
            case 'pendiente':
                $data = Contract::with(['people'])
                    ->where(function($query) use ($search){
                        // ->OrWhereRaw($search ? "CONCAT(first_name, ' ', last_name1, ' ', last_name2) like '%$search%'" : 1)              

                        if($search){
                            $query->OrwhereHas('people', function($query) use($search){
                                $query->whereRaw("(ci like '%$search%' or first_name like '%$search%' or last_name1 like '%$search%' or last_name2 like '%$search%' or CONCAT(first_name, ' ', last_name1, ' ', last_name2) like '%$search%')");
                            })
                            ->OrWhereRaw($search ? "id = '%$search%'" : 1)
                            ->OrWhereRaw($search ? "work like '%$search%'" : 1);
                        }
                    })
                    ->where('status', 'pendiente')->where('deleted_at', NULL)->orderBy('id', 'DESC')->paginate($paginate);

                return view('administration.contracts.list', compact('data'));
                break;
            case 'finalizado':
                $data = Contract::with(['people'])
                    ->where(function($query) use ($search){
                        // ->OrWhereRaw($search ? "CONCAT(first_name, ' ', last_name1, ' ', last_name2) like '%$search%'" : 1)              

                        if($search){
                            $query->OrwhereHas('people', function($query) use($search){
                                $query->whereRaw("(ci like '%$search%' or first_name like '%$search%' or last_name1 like '%$search%' or last_name2 like '%$search%' or CONCAT(first_name, ' ', last_name1, ' ', last_name2) like '%$search%')");
                            })
                            ->OrWhereRaw($search ? "id = '%$search%'" : 1)
                            ->OrWhereRaw($search ? "work like '%$search%'" : 1);
                        }
                    })
                    ->where('status', 'finalizado')->where('deleted_at', NULL)->orderBy('id', 'DESC')->paginate($paginate);

                return view('administration.contracts.list', compact('data'));
                break;
                    
            case 'vigente':
                $data = Contract::with(['people'])
                    ->where(function($query) use ($search){           
                        if($search){
                            $query->OrwhereHas('people', function($query) use($search){
                                $query->whereRaw("(ci like '%$search%' or first_name like '%$search%' or last_name1 like '%$search%' or last_name2 like '%$search%' or CONCAT(first_name, ' ', last_name1, ' ', last_name2) like '%$search%')");
                            })
                            ->OrWhereRaw($search ? "id = '%$search%'" : 1)
                            ->OrWhereRaw($search ? "work like '%$search%'" : 1);
                        }
                    })
                    ->where('status', 'aprobado')->where('deleted_at', NULL)->orderBy('id', 'DESC')->paginate($paginate);

                return view('administration.contracts.list', compact('data'));
                break;
            case 'eliminado':
                $data = Contract::with(['people'])
                    ->where(function($query) use ($search){          
                        if($search){
                            $query->OrwhereHas('people', function($query) use($search){
                                $query->whereRaw("(ci like '%$search%' or first_name like '%$search%' or last_name1 like '%$search%' or last_name2 like '%$search%' or CONCAT(first_name, ' ', last_name1, ' ', last_name2) like '%$search%')");
                            })
                            ->OrWhereRaw($search ? "id = '%$search%'" : 1)
                            ->OrWhereRaw($search ? "work like '%$search%'" : 1);
                        }
                    })
                    ->where('deleted_at', '!=', null)->orderBy('id', 'DESC')->paginate($paginate);
                    // dump($data);
                return view('administration.contracts.list', compact('data'));

                break;
            case 'rechazado':
                $data = Contract::with(['people'])
                    ->where(function($query) use ($search){
                        // ->OrWhereRaw($search ? "CONCAT(first_name, ' ', last_name1, ' ', last_name2) like '%$search%'" : 1)              

                        if($search){
                            $query->OrwhereHas('people', function($query) use($search){
                                $query->whereRaw("(ci like '%$search%' or first_name like '%$search%' or last_name1 like '%$search%' or last_name2 like '%$search%' or CONCAT(first_name, ' ', last_name1, ' ', last_name2) like '%$search%')");
                            })
                            ->OrWhereRaw($search ? "id = '%$search%'" : 1)
                            ->OrWhereRaw($search ? "work like '%$search%'" : 1);
                        }
                    })
                    ->where('status', 'rechazado')->where('deleted_at', NULL)->orderBy('id', 'DESC')->paginate($paginate);

                return view('administration.contracts.list', compact('data'));
                break;
            case 'todo':
                $data = Contract::with(['people'])
                    ->where(function($query) use ($search){
                        if($search){
                            $query->OrwhereHas('people', function($query) use($search){
                                $query->whereRaw("(ci like '%$search%' or first_name like '%$search%' or last_name1 like '%$search%' or last_name2 like '%$search%' or CONCAT(first_name, ' ', last_name1, ' ', last_name2) like '%$search%')");
                            })
                            ->OrWhereRaw($search ? "id = '%$search%'" : 1)
                            ->OrWhereRaw($search ? "work like '%$search%'" : 1);
                        }
                    })
                    // ->withTrashed()
                    ->orderBy('id', 'DESC')->paginate($paginate);
                // dump($data);
                return view('administration.contracts.list', compact('data'));
                break;

        } 
    }

    public function create()
    {
        $employeJob = EmployeJob::where('deleted_at', null)->get();

        $shifts = Shifts::where('status', 'aprobado')->where('deleted_at',null)->get();
        return view('administration.contracts.add', compact('employeJob', 'shifts'));
    }


    public function store(Request $request)
    {
        $user = Auth::user();
        
        $dayContract = 30;// Duración del contrato en días
        $dayWeekList = [
            'Monday' => 'Lunes',
            'Tuesday' => 'Martes',
            'Wednesday' => 'Miercoles',
            'Thursday' => 'Jueves',
            'Friday' => 'Viernes',
            'Saturday' => 'Sabado',
            'Sunday' => 'Domingo'
        ];

        DB::beginTransaction();
        try {
            $contract = Contract::where('people_id', $request->people_id)
                ->where('status', '!=', 'rechazado')
                ->Where('status', '!=', 'finalizado')
                ->where('deleted_at', null) 
                ->first();            
            if($contract)
            {   
                // return 0;
                return redirect()->route('contracts.index')->with(['message' => 'La persona ya cuenta con un contrato..', 'alert-type' => 'error']);
            }
            $employeJob = EmployeJob::where('id', $request->employeJob)->first();
            if($request->dateStart >= $request->dateFinish)
            {   
                return redirect()->route('contracts.index')->with(['message' => 'Fecha Incorrecta', 'alert-type' => 'error']);
            }

            $contract = Contract::create([
                'people_id' => $request->people_id,
                'salary' => $employeJob->amount,
                'advancement' => 0,
                'work' => $employeJob->name,
                'type' => null,
                'dateStart' => $request->dateStart,
                'dateFinish' => $request->dateFinish,
                'observation' => $request->observation,
                'totalSalary' => $employeJob->amount,       

                'register_userId'=>$user->id,
                'register_agentType' =>$user->role->name
            ]);


            $dateStart = new DateTime($contract->dateStart);
            $dateFinish = new DateTime($contract->dateFinish);        

            $diferencia = $dateStart->diff($dateFinish);
            $cantDay = $diferencia->days + 1;//Para saber la cantidad de dia desde que se inicia hasta que termina

            $paymentDay = $contract->salary / $dayContract;// obtengo lo que gana por dia un trabajado salario / 30 dias de trabajo

            // return $paymentDay;
            $monthList = [
                'January' => 'Enero',
                'February' => 'Febrero',
                'March' => 'Marzo',
                'April' => 'Abril',
                'May' => 'Mayo',
                'June' => 'Junio',
                'July' => 'Julio',
                'August' => 'Agosto',
                'September' => 'Septiembre',
                'October' => 'Obtubre',
                'November' => 'Noviembre',
                'December' => 'Diciembre'
            ];


            $ok= true;
            $auxMonth = $dateStart->format('m');
            $amountDay=0;

            while ($dateStart <= $dateFinish)
            {
                if($auxMonth != $dateStart->format('m') || $ok)
                {
                    $auxMonth = $dateStart->format('m');
                    $ok = false;
                    $totalDiasTrabajados = 0;
                    $currentDate = clone $dateStart;
                    $inicioMes = max($currentDate, new DateTime($currentDate->format('Y-m-01')));
                    $finMes = min(new DateTime($currentDate->format('Y-m-30')), $dateFinish);
                    // Verifica si el rango es válido
                    if ($inicioMes <= $finMes) 
                    {
                        $diasTrabajados = $inicioMes->diff($finMes)->days + 1; // +1 para incluir el último día
                        $totalDiasTrabajados += $diasTrabajados;
                    }

                }
                $dayWeek = $dateStart->format('l');//Obtenemos los dias de la semana en ingles desde la fecha inicial incrementandoce
                $contractDay = ContractDay::create([
                    'contract_id'=>$contract->id,
                    'shift_id'=>$request->shift_id,
                    'periodMonth'=>$dateStart->format('m'),
                    'periodYear'=>$dateStart->format('Y'),
                    'date'=>$dateStart,
                    'paymentDay'=>$paymentDay,
                    'dayWeekNumber'=>array_search($dayWeek, array_keys($dayWeekList))+1,
                    'dayWeekName'=>$dayWeekList[$dayWeek],

                    'register_userId'=>$user->id,
                    'register_agentType' =>$user->role->name
                ]);

                // return $contractDay;
                // return $paymentDay;
                $shiftHour = ShiftsHour::where('shifts_id', $request->shift_id)->where('deleted_at', null)->where('dayWeekName', $dayWeekList[$dayWeek])->get();//obtenemos las horas o horario que le pertenece al turno respecto al dia de la semana  
                $sudAmount = $shiftHour->count()>0?$contractDay->paymentDay/$shiftHour->count():0;
                // return $sudAmount;
                // dump($shiftHour->count());
                foreach ($shiftHour as $itemShiftHour) {
                    ContractDayAttendance::create([
                        'contractDay_id'=>$contractDay->id,
                        'attendaceStart_id'=>null,
                        'attendaceFinish_id'=>null,
                        'shiftHour_id'=>$itemShiftHour->id,
                        'amount'=>$sudAmount,
                        'start'=>null,
                        'finish'=>null,
                        
                        'typeHour' => $itemShiftHour->day,
                        'register_userId'=>$user->id,
                        'register_agentType' =>$user->role->name
                    ]);
                }

                $contractDay->update([
                    'job' => $shiftHour->count()>0?1:0,
                ]);

                $dateStart->modify('+1 day');//Para poder incrementar los dias
            }
            // return $contractDay;

            ContractShift::create([
                'contract_id'=>$contract->id,
                'shift_id'=>$request->shift_id,
                'start' => $request->dateStart,
                'finish' => $request->dateFinish,


                'register_userId'=>$user->id,
                'register_agentType' =>$user->role->name
            ]);
            // return 1;
            DB::commit();
            return redirect()->route('contracts.index')->with(['message' => 'Contrato creado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollback();
            // return 0;
            return redirect()->route('contracts.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    //Para aprobar el contarto
    public function successLoan($contract)
    {
        DB::beginTransaction();
        try {
            // return $loan;
            Contract::where('id', $contract)->update([
                'status' => 'aprobado',

                'success_userId' => Auth::user()->id,
                'success_agentType' => Auth::user()->role->name
            ]);
        


            DB::commit();
            return redirect()->route('contracts.index')->with(['message' => 'Contrato aprobado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('contracts.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function rechazar(Request $request, $contract)
    {
        // return $contract;
        // return $request;
        DB::beginTransaction();
        try {
            Contract::where('id', $contract)->update([
                'status' => 'rechazado',

                'rejected_userId' => Auth::user()->id,
                'rejected_agentType' => Auth::user()->role->name,
                'rejectedObservation' => $request->observation
            ]);


            DB::commit();
            return redirect()->route('contracts.index')->with(['message' => 'Rechazado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('contracts.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }



    public function show($contract)
    {
        $contract = Contract::with(['contractDay'=>function($qu)
        {
            $qu->where('deleted_at', null);
        },
        'contractDay.contractDayAttendance'=>function($q){
            $q->where('deleted_at', null);
        },
        'people', 'contractAdvancement',
        'spreadsheetContract'=>function($query){
            $query->where('deleted_at', null)
            ->where('paid',1);
        }, 
        'spreadsheetContract.spreadsheet','contractDay.contractDayAttendance.shiftHour','contractDay.contractDayAttendance.hours'])
            ->where('id', $contract)->first();

        // return $contract;
        $cantMonth = DB::table('contract_days')
                    ->where('contract_id', $contract->id)
                    ->select(DB::raw('DATE_FORMAT(date, "%Y-%m") as meses'), DB::raw('DATE_FORMAT(date, "%m") as mes'), DB::raw('DATE_FORMAT(date, "%Y") as ano'))
                    ->orderBy('meses', 'ASC')
                    ->groupBy('meses')
                    ->get();

        $ContractDay = ContractDay::where('contract_id', $contract->id)->where('deleted_at', null)->orderBy('id', 'ASC')->get();

        // return $cantMonth;

        $cashier = $this->cashierOpen();
        // $hour = Hour::where('deleted_at', null)->get();
        $hour = Hour::where('deleted_at', NULL)
            ->where('status', 'normal')
            ->orderBy('id', 'DESC')
            ->get();

        $licenses = License::where('contract_id', $contract->id)->where('deleted_at', null)->get();

        return view('administration.contracts.read', compact('contract', 'cashier', 'cantMonth', 'ContractDay', 'hour', 'licenses'));
    }


    public function storeAdvancement(Request $request)
    {
        DB::beginTransaction();
        try {

            if($request->amount <= 0){
                return redirect()->to($_SERVER['HTTP_REFERER'])->with(['message' => 'Monto ingresado no válido', 'alert-type' => 'warning']);
            }

            $cashier = $this->cashierOpen();

            if(!$cashier){
                return redirect()->to($_SERVER['HTTP_REFERER'])->with(['message' => 'La caja no se encuentra abierta.', 'alert-type' => 'warning']);
            }

            $contract = Contract::with([
                    'contractDay' => function ($query) {
                        $query->select('id', 'contract_id', 'spreadsheet', 'deleted_at', 'periodMonth', 'periodYear') 
                              ->where('spreadsheet', 0) 
                              ->where('deleted_at', null) 
                              ->groupBy('periodMonth', 'periodYear')->first();
                    }
                ])->where('id', $request->contract_id)
                  ->whereNull('deleted_at')
                  ->first();
            $contract = Contract::where('id', $request->contract_id)
                ->where('status', 'aprobado')
                ->where('deleted_at', null)
                ->first();

            $contractDay = ContractDay::where('contract_id', $contract->id)
                ->where('spreadsheet', 0)
                ->where('deleted_at', null) 
                ->select('periodMonth', 'periodYear', DB::raw('COUNT(*) as total_entries'))
                ->groupBy('periodMonth', 'periodYear') 
                ->orderBy('periodMonth', 'asc') 
                ->orderBy('periodYear', 'asc') 
                ->first();
            // return $contractDay;

            $contractDayBs = ContractDay::where('contract_id', $contract->id)
                ->where('spreadsheet', 0)
                ->where('deleted_at', null) 
                ->where('periodMonth', $contractDay->periodMonth)
                ->where('periodYear', $contractDay->periodYear) 
                ->select(DB::raw('SUM(paymentDay) as paymentDay'))
                ->first();
            // return 

            $amount = $contractDayBs->paymentDay*0.4;//el 40% que se le puede dar por periodo

            $total_caja = 0;
            foreach ($cashier->movements as $movement) {
                if($movement->type == 'ingreso'){
                    $total_caja += $movement->balance;
                }elseif($movement->type == 'egreso'){
                    $total_caja -= $movement->amount;
                }
            }

            if($total_caja < $request->amount){
                return redirect()->to($_SERVER['HTTP_REFERER'])->with(['message' => 'El monto supero el dinero en caja', 'alert-type' => 'warning']);   
            }
            $contractAdvancement = ContractAdvancement::where('contract_id', $contract->id)
                ->where('periodMonth', $contractDay->periodMonth)
                ->where('periodYear', $contractDay->periodYear) 
                ->where('deleted_at', null)
                ->get()
                ->SUM('advancement');

            $total = $contractAdvancement+$request->amount;
            if($total > $amount){
                return redirect()->to($_SERVER['HTTP_REFERER'])->with(['message' => 'El monto el 40% de lo que se puede adelantar', 'alert-type' => 'warning']);   
            }
            // return $total;

            $movement = CashierMovement::create([
                'user_id' => Auth::user()->id,
                'cashier_id' => $cashier->id,
                'amount' => $request->amount,
                'description' => $request->description,
                'type' => 'egreso',
                'status'=>'Aceptado'
            ]);

            ContractAdvancement::create([
                'contract_id' => $contract->id,
                'cashier_id' => $this->cashierOpen()->id,
                'cashierMovement_id' => $movement->id,

                'periodMonth'=>$contractDay->periodMonth,
                'periodYear'=>$contractDay->periodYear,

                'advancement' => $request->amount,
                'dateAdvancement' => Carbon::now(),
                'observation' => $request->description,

                'register_userId' => Auth::user()->id,
                'register_agentType' => Auth::user()->role->name

            ]);


            // return 'Si';
            DB::commit();
            return redirect()->route('contracts.show', ['contract' => $request->contract_id])->with(['message' => 'Adelanto registrado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            // return 111222;
            return redirect()->route('contracts.show', ['contract' => $request->contract_id])->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function storeLicenseAll(Request $request)
    {
        if($request->dateStart > $request->dateFinish)
        {
            return redirect()->route('contracts.show', ['contract' => $request->contract_id])->with(['message' => 'Ocurrió un error en la selección de las fechas.', 'alert-type' => 'error']);
        }
    
        $dateStart = new DateTime($request->dateStart);
        $dateFinish = new DateTime($request->dateFinish);  

        $contract = Contract::where('id', $request->contract_id)->where('deleted_at', null)->first();
        // return $request;
        $user = Auth::user();
        DB::beginTransaction();
        try {

            $license = License::create([
                'contract_id'=>$contract->id,
                'dateStart'=>$request->dateStart,
                'dateFinish'=>$request->dateFinish,
                'description'=>$request->description,
                'type'=>'Licencia dia completo',
                'register_userId'=>$user->id,
                'register_agentType' =>$user->role->name
            ]);

            while ($dateStart <= $dateFinish)
            {
                // dump(1);
                $contractDay = ContractDay::with(['contractDayAttendance'])
                    ->where('contract_id', $contract->id)->where('date', $dateStart)->where('deleted_at', null)->first();
                if($contractDay->spreadsheet == 0)
                {
                    foreach ($contractDay->contractDayAttendance as $item) {
                        $item->update([
                            'typeLicense'=>'Licencia dia completo',
                            'license_id'=>$license->id
                        ]);
                    }
                }
                else
                {
                    DB::rollBack();
                    return redirect()->route('contracts.show', ['contract' => $contract->id])->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);

                }
                $contractDay->update([
                    'typeLicense'=>'Licencia dia completo',
                ]);

            
                $dateStart->modify('+1 day');//Para poder incrementar los dias
            }
            DB::commit();
            return redirect()->route('contracts.show', ['contract' => $contract->id])->with(['message' => 'Licencia registrado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('contracts.show', ['contract' => $contract->id])->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }

    }

    public function destroyLicenseAll(Request $request, $license)
    {
        // return $request;
        $user = Auth::user();

        DB::beginTransaction();
        try {
            $license = License::where('id', $license)->where('deleted_at', null)->first();

            // $contractDayAttendance = ContractDayAttendance::where('license_id', $license->id)->where('deleted_at', null)->get();

            $contractDays = ContractDay::with(['contractDayAttendance'])
                ->where('contract_id', $request->contract_id)->where('date', '>=', $license->dateStart)
                ->where('date', '<=', $license->dateFinish)->where('deleted_at', null)->get();
            // return $contractDays;

            foreach ($contractDays as $contractDay) {
                if(!$contractDay->spreadsheet)
                {
                    foreach ($contractDay->contractDayAttendance->where('deleted_at', null) as $contractDayAttendance) {
                        $contractDayAttendance->update([
                            'license_id'=>null,
                            'typeLicense'=>null,
                        ]);
                    }
                }
                else
                {
                    DB::rollBack();
                    return redirect()->route('contracts.show', ['contract' => $request->contract_id])->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
                }
                $contractDay->update([
                    'typeLicense'=>null
                ]);
            }

            $license->update([
                'deleted_at' => Carbon::now(),

                'deleted_userId' => Auth::user()->id,
                'deleted_agentType' => Auth::user()->role->name,
                'deleted_observation' => $request->observation
            ]);
            
            // return 'si';
            DB::commit();
            return redirect()->route('contracts.show', ['contract' => $request->contract_id])->with(['message' => 'Licencia eliminada exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            // return 
            DB::rollBack();
            return redirect()->route('contracts.show', ['contract' => $request->contract_id])->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }


    }

    // $table->foreignId('paid_userId')->nullable()->constrained('users');
    // $table->string('')->nullable();
    public function destroyAdvancement(Request $request, $id)
    {
        DB::beginTransaction();
        try {

            $contractAdvancement = ContractAdvancement::where('id', $id)->where('deleted_at', null)->where('spreadsheet', 0)->first();

            // return $contractAdvancement;
            $cashier = $this->cashierOpen();

            if(!$cashier){
                return redirect()->to($_SERVER['HTTP_REFERER'])->with(['message' => 'La caja no se encuentra abierta.', 'alert-type' => 'warning']);
            }
            if($cashier->id != $contractAdvancement->cashier_id){
                return redirect()->to($_SERVER['HTTP_REFERER'])->with(['message' => 'La caja no se encuentra abierta.', 'alert-type' => 'warning']);
            }

            $contract = Contract::where('id', $contractAdvancement->contract_id)->first();
 
            //Disminuyendo el adelanto del contrato
            // $contract->decrement('advancement', $contractAdvancement->advancement);

            $cashierMovement = CashierMovement::where('id', $contractAdvancement->cashierMovement_id)->first();

            $cashierMovement->update(['deleted_at'=>Carbon::now()]);

            $contractAdvancement->update([
                'deleted_at'=>Carbon::now(),

                'deleted_userId' => Auth::user()->id,
                'deleted_agentType' => Auth::user()->role->name,
                'deletedObservation'=>$request->observation
            ]);
            // return $request;
            DB::commit();
            return redirect()->route('contracts.show', ['contract' => $contract->id])->with(['message' => 'Rechazado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('contracts.show', ['contract' => $contract->id])->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function printAdvancementMoney($contract_id, $id)
    {
        $contractAdvancement = ContractAdvancement::with(['contract', 'contract.people'])
            ->where('id', $id)
            ->where('contract_id', $contract_id)
            ->where('deleted_at', null)->first();

        return view('administration.contracts.printMoneyAdvancement', compact('contractAdvancement'));
    }
    


    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $c = Contract::where('id', $id)->where('deleted_at', null)->first();
            if($c->paid)
            {
                return redirect()->route('contracts.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
            }

            if($c->advancement>0)
            {
                return redirect()->route('contracts.index')->with(['message' => 'El contrato ya cuenta con un pago en adelanto.', 'alert-type' => 'error']);
            }

            Contract::where('id', $id)->update([
                'deleted_at' => Carbon::now(),

                'deleted_userId' => Auth::user()->id,
                'deleted_agentType' => Auth::user()->role->name,
                'deletedObservation' => $request->observation
            ]);

            
            DB::commit();
            return redirect()->route('contracts.index')->with(['message' => 'Anulado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('contracts.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }



    //Para el cambio de horario dentro del contrato
    public function saveContractDayHour(Request $request, $contractDay_id)
    {
        $contractDay = ContractDay::where('id', $contractDay_id)
            ->where('deleted_at', null)
            ->first();
        // return $request;

            DB::beginTransaction();

            $week=[
                'Lunes','Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'
            ];
            $user = Auth::user();
    
            // return $week;
            try {
               
                $sudAmount = count($request->hour)>0?$contractDay->paymentDay/count($request->hour):0;


                $cda = ContractDayAttendance::where('deleted_at', null)
                ->where('contractDay_id', $contractDay->id)->update([
                    'deleted_userId'=>$user->id,
                    'deleted_agentType'=>$user->role->name,
                    'deleted_at'=>Carbon::now()
                ]);
                // return 1;



                // return $contractDay->id;


                // return $contractDay->id;

                foreach ($request->hour as $id) {
                    $contractDayAttendance = ContractDayAttendance::with(['hours'])
                        ->where('deleted_at', null)->where('contractDay_id', $contractDay->id)->get();

                    // return count($contractDayAttendance);
                    $hour = Hour::where('id', $id)->first();

                    // dump($contractDayAttendance->count());
                    if($contractDayAttendance->count()>0)
                    {
                    // dump($hour);

                        // return $contractDayAttendance;
                        // foreach ($request->day as $day) {
                            $ok=true;
                            // $shiftsHour = ShiftsHour::where('shifts_id', $shifts)->where('dayWeekNumber', $day)->where('deleted_at', null)->get();
                            foreach ($contractDayAttendance as $sh) {
                                // if($sh->dayWeekNumber == $day)//comparacion
                                // {
                                    $hourStart = date("H:i", strtotime($hour->rangeStartInput));
                                    $hourFinish = date("H:i", strtotime($hour->rangeFinishOutput));
    
                                    $shStart = date("H:i", strtotime($sh->hours->rangeStartInput)); // 16:45
                                    $shFinish = date("H:i", strtotime($sh->hours->rangeFinishOutput)); // 16:45
    
                                    if($hourStart >= $shStart && $hourStart <= $shFinish)
                                    {
                                        $ok=false;
                                    }
    
                                    if($hourFinish >= $shStart && $hourFinish <= $shFinish)
                                    {
                                        $ok=false;
                                    }
                                    if($hourStart <= $shStart && $hourFinish >= $shFinish)
                                    {
                                        $ok=false;
                                    }
                                // }
                            }
                            //si es verdadero y no se interceptan las horas se registra
                            if($ok)
                            {
                                ContractDayAttendance::create([
                                    'contractDay_id'=>$contractDay->id,
                                    'attendaceStart_id'=>null,
                                    'attendaceFinish_id'=>null,
                                    'shiftHour_id'=>null,
                                    'hour_id'=>$id,
                                    
                                    'typeHour' => $hour->day,
                                    'amount'=>$sudAmount,
                                    'start'=>null,
                                    'finish'=>null,
                                    
                                    'typeHour' => $hour->day,
                                    'register_userId'=>$user->id,
                                    'register_agentType' =>$user->role->name
                                ]);
                            }
                            else
                            {
                                DB::rollBack();
                                return redirect()->route('contracts.show', ['contract' => $contractDay->contract_id])->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
                            }
                    }
                    else
                    {
                        ContractDayAttendance::create([
                            'contractDay_id'=>$contractDay->id,
                            'attendaceStart_id'=>null,
                            'attendaceFinish_id'=>null,
                            'shiftHour_id'=>null,
                            'hour_id'=>$id,
                            
                            'typeHour' => $hour->day,
                            'amount'=>$sudAmount,
                            'start'=>null,
                            'finish'=>null,
                            
                            'typeHour' => $hour->day,
                            'register_userId'=>$user->id,
                            'register_agentType' =>$user->role->name
                        ]);        

                    }
                }
                $job = ContractDayAttendance::where('contractDay_id', $contractDay->id)->where('deleted_at', null)->get();//obtenemos las horas o horario que le pertenece al turno respecto al dia de la semana  

                $contractDay->update([
                    // 'shift_id'=>null,
                    'job' => $job->count()>0?1:0
                ]);
    
                DB::commit();
                return redirect()->route('contracts.show', ['contract' => $contractDay->contract_id])->with(['message' => 'Horario registrado exitosamente.', 'alert-type' => 'success']);
            } catch (\Throwable $th) {
                DB::rollBack();
                return redirect()->route('contracts.show', ['contract' => $contractDay->contract_id])->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
            }
    }
}
