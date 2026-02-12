<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Bonu;
use App\Models\BonuDetail;
use App\Models\BonuDetailContract;
use App\Models\Contract;
use App\Models\ContractDay;
use App\Models\ContractDayAttendance;
use App\Models\People;

class BonuController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('administration.bonus.browse');
    }

    public function list($type, $search = null){
        $user = Auth::user();
        $paginate = request('paginate') ?? 10;

        switch($type)
        {
            case 'pendiente':
                $data = Bonu::
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

                return view('administration.bonus.list', compact('data'));
                break;
      
                    
            case 'aprobado':
                $data = Bonu::
                    where(function($query) use ($search){
                        if($search){
                            $query->OrWhereRaw($search ? "id = '%$search%'" : 1)
                            ->OrWhereRaw($search ? "description like '%$search%'" : 1);
                        }
                    })
                    // ->where('status', 'aprobado')
                    ->whereIn('status', ['aprobado', 'finalizado'])

                    ->where('deleted_at', NULL)->orderBy('id', 'DESC')->paginate($paginate);
                

                return view('administration.bonus.list', compact('data'));
                break;
            case 'eliminado':
                $data = Bonu::
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

                return view('administration.bonus.list', compact('data'));
                break;
            case 'todo':
                $data = Bonu::
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


                return view('administration.bonus.list', compact('data'));
                break;

        } 
    }

    public function create()
    {
        return view('administration.bonus.add');
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        DB::beginTransaction();
        try {
            Bonu::create([
                'year' => $request->year,

                'description'=>$request->description,

                'register_userId'=>$user->id,
                'register_agentType' =>$user->role->name
            ]);
            // return 1;
            DB::commit();
            return redirect()->route('bonus.index')->with(['message' => 'Planilla creada exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollback();
            // return 0;
            return redirect()->route('bonus.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();

            $s = Bonu::where('id', $id)->where('deleted_at', null)->first();
            if($s->status == 'aprobado')
            {
                return redirect()->route('bonus.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
            }

            Bonu::where('id', $id)->update([
                'deleted_at' => Carbon::now(),

                'deleted_userId' => $user->id,
                'deleted_agentType' => $user->role->name,
                'deleted_observation' => $request->observation
            ]);

            
            DB::commit();
            return redirect()->route('bonus.index')->with(['message' => 'Anulado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('bonus.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function rechazar(Request $request, $bonu)
    {
        DB::beginTransaction();
        try {
            Bonu::where('id', $bonu)->update([
                'status' => 'rechazado',
            ]);

            DB::commit();
            return redirect()->route('bonus.index')->with(['message' => 'Rechazado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('bonus.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    // public function destroy(Request $request, $id)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $user = Auth::user();

    //         $s = Bonu::where('id', $id)->where('deleted_at', null)->first();
    //         if($s->status == 'aprobado')
    //         {
    //             return redirect()->route('bonus.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
    //         }

    //         Bonu::where('id', $id)->update([
    //             'deleted_at' => Carbon::now(),

    //             'deleted_userId' => $user->id,
    //             'deleted_agentType' => $user->role->name,
    //             'deleted_observation' => $request->observation
    //         ]);

            
    //         DB::commit();
    //         return redirect()->route('bonus.index')->with(['message' => 'Anulado exitosamente.', 'alert-type' => 'success']);
    //     } catch (\Throwable $th) {
    //         DB::rollBack();
    //         return redirect()->route('bonus.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
    //     }
    // }

    function contract_duration_calculate($start, $finish)
    {
        // Obtener el último día de febrero (por si es un año bisiesto)
        $last_day_february = date("t", strtotime(date('Y').'-02-01'));
        $start = Carbon::parse($start);
        $finish = Carbon::parse($finish);
        $count_months = 0;
        // dump($finish->format('Ym'));

        if($start->format('Ym') == $finish->format('Ym')){
            // dump(1);
            $count_months = 0;
            if($finish->format('d') > 30){
                $finish = Carbon::parse($finish->addDays(-1)->format('Y-m-d'));
            }
            $count_days = $start->diffInDays($finish) +1;
            if($finish->format('m') == 2 && ($finish->format('d') == $last_day_february)){
                $count_days += (30 - $finish->endOfMonth()->format('d'));
            }
            $count_days = $count_days > 30 ? 30 : $count_days;
        }else{
            $count_months = 0;
            if($start->format('d') > 30){
                $start = Carbon::parse($start->addDays()->format('Y-m-d'));
            }
            $start_day = $start->format('d');
            $count_days = 30 - $start_day +1;
            $start = Carbon::parse($start->addMonth()->format('Y-m').'-01');
            // return $start;
            // dump($start);
            while ($start <= $finish) {
                // dump($start);
                $count_months++;
                $start->addMonth();
            }
            $count_months--;

            // Calcula la cantidad de días del ultimo mes
            $count_days_last_month = $start->subMonth()->diffInDays($finish) +1;
            // dump($start->subMonth()->diffInDays($finish) +1);
            // dump($count_days_last_month);
            // Si es mayor o igual a 30 se toma como un mes completo
            if($count_days_last_month >= 30 || ($finish->format('m') == 2 && $count_days_last_month == $last_day_february)){
                $count_days_last_month = 0;
                $count_months++;
            }
            $count_days += $count_days_last_month;
        }

        if($count_days >= 30){
            $count_months++;
            $count_days -= 30;
        }

        return json_decode(json_encode(['months' => $count_months, 'days' => $count_days]));
    }

    public function generate($bonu)
    {
        DB::beginTransaction();
        try {
            $bonu = Bonu::where('id', $bonu)->where('deleted_at', null)->first();
            $year = $bonu->year;

            $contract = Contract::whereYear('dateStart',$year)
                ->whereYear('dateFinish',$year)
                ->where('deleted_at', null)
                ->whereIn('status', ['aprobado', 'pendiente'])
                ->get();

            if(count($contract)>0)
            {
                return redirect()->route('bonus.index')->with(['message' => 'Ocurrió un error, aun hay contrato vigente', 'alert-type' => 'error']);
            }
            // return $contract;

            $peoples = People::with(['contract'=>function($query)use($year){
                    $query->where('deleted_at', null)    
                    ->whereIn('status', ['aprobado', 'finalizado'])
                    ->whereYear('dateStart',$year)
                    ->whereYear('dateFinish',$year)
                    ->orderBy('id', 'ASC');
                },
                'contract.spreadsheetContract'=>function($query){
                    $query->where('deleted_at', null);
                }
                ])
                ->whereHas('contract',function($query)use($year){
                    $query->where('deleted_at', null)    
                    ->whereIn('status', ['aprobado', 'finalizado'])
                    ->whereYear('dateStart',$year)
                    ->whereYear('dateFinish',$year)
                    ->orderBy('id', 'ASC');
                })
                ->orderBy('last_name1', 'ASC')
                ->orderBy('last_name2', 'ASC')
                ->get();
        // return $peoples;

            $bonuses = array();
            $cont = 0;
            foreach ($peoples as $person) {
                $contracts_list = array();
                $contracts = array();
                $last_contract_start = null;
                $days_contract = 0;
                $count_contract = 1;
                // return $person;

                foreach ($person->contract as $contract) {
                    // return $contract;
                    // $start = date('Y', strtotime($contract->dateStart)) == date('Y') ? $contract->dateStart : date('Y').'-01-01';
                    $start = $contract->dateStart;
                    // return $start;
                    // $finish = $contract->dateFinish ?? date('Y').'-12-30';
                    $finish = $contract->dateFinish;

                    if($contract->dateFinish == date('Y-m-d', strtotime($last_contract_start.' -1 days')) || $last_contract_start == null){
                        // return $last_contract_start;
                        // dump($last_contract_start);
                        $duration = $this->contract_duration_calculate($start, $finish);
                        // return $duration;
                        $days_contract += $duration->months * 30 + $duration->days;
                        // return $days_contract;
                        array_push($contracts, $contract);
                        // return $contracts;
                        // return $person->contract;
                        // Si es el último contrato y si la cantidad de días es mayor a 90
                        if($person->contract->count() == $count_contract && $days_contract >= 90){
                            $days_contract = $days_contract > 360 ? 360 : $days_contract;

                            // Si el último contrato (posición 0 ordenado DESC) es de la DA seleccionada
                            // if ($contracts[0]->direccion_administrativa_id == $direccion_id) {
                                array_push($contracts_list, ['days'=> $days_contract, 'contracts' => $contracts]);
                            // }
                        }
                        // return $contracts_list;
                    }
                    else
                    {
                        // Agregar lo acumulado si tiene más de 90 días
                        if ($days_contract >= 90) {
                            // if ($contracts[0]->direccion_administrativa_id == $direccion_id) {
                                array_push($contracts_list, ['days'=> $days_contract, 'contracts' => $contracts]);
                            // }
                        }
                        
                        // Calcular los nuevos datos
                        $duration = $this->contract_duration_calculate($start, $finish);
                        $days_contract = $duration->months * 30 + $duration->days;
                        $days_contract = $days_contract > 360 ? 360 : $days_contract;
                        $contracts = array();
                        // Si el contrato es mayor a 90 días se almacena
                        if ($days_contract >= 90) {
                            array_push($contracts, $contract);
                        }

                        // Si es el último recorrido y hay contratos acumulados
                        if($person->contract->count() == $count_contract && count($contracts)){
                            $days_contract = $days_contract > 360 ? 360 : $days_contract;
                            // if ($contract->direccion_administrativa_id == $direccion_id) {
                                array_push($contracts_list, ['days'=> $days_contract, 'contracts' => [$contract]]);
                            // }
                        }
                    }
                    
                    $last_contract_start = $contract->dateStart;
                    // return $last_contract_start;
                    $count_contract++;
                }
                // return 11;

                // Almacenar registros de aguinaldo
                if (count($contracts_list)) {
                    $peoples[$cont]->contracts_list = $contracts_list;
                    array_push($bonuses, $peoples[$cont]);
                }
                $cont++;
            }
            // return $bonuses;

            
            $totalPayment=0;
            foreach ($bonuses as $bonus) {
                // return $bonus;
                $bonuDetail = BonuDetail::create([
                    'people_id'=>$bonus->id,
                    'bonu_id'=>$bonu->id,
                    // 'payment'
                    // 'dayWorked'
                ]);
                $payment =0;
                $dayWorked =0;
                foreach ($bonus->contracts_list as $contracts_list) {
                    $dayWorked+=$contracts_list['days'];
                    // $payment +=$contracts_list['']:
                    foreach ($contracts_list['contracts'] as $contract) {
                        $bonuDetailContract = BonuDetailContract::create([
                            'bonuDetail_id'=>$bonuDetail->id,
                            'contract_id'=>$contract->id
                        ]);
                        foreach ($contract->spreadsheetContract as $spreadsheet) {
                            $payment+=($spreadsheet->payment/12);
                        }
                    }               
                }
                $totalPayment+=$payment;
                $bonuDetail->update([
                    'dayWorked'=>$dayWorked,
                    'payment'=>$payment
                ]);
            }
            // return 1;
            $bonu->update([
                'totalPayment'=>$totalPayment,
                'status'=>'aprobado'
            ]);
            // return $bonu;
            DB::commit();
            return redirect()->route('bonus.index')->with(['message' => 'Planilla generada exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('bonus.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }
    public function printBonuses($bonu)
    {
        // return $bonu;
        $bonuses = Bonu::with(['bonuDetail.bonuDetailContract','bonuDetail.people'])
            ->where('id', $bonu)->where('deleted_at', null)->first();

        // return $bonuses;

        return view('administration.bonus.print', compact('bonuses'));

    }

}
