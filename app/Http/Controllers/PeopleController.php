<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\People;
use App\Models\PeopleSponsor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PeopleImport;
use Illuminate\Support\Facades\Http;
use App\Models\Contract;
use Illuminate\Support\Facades\Log;

class PeopleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        return view('people.browse');
    }

    public function list($search = null){
        $user = Auth::user();

        // $query_filter = 'busine_id = '.$user->busine_id;
        // if (Auth::user()->hasRole('admin')) {
        //     $query_filter = 1;
        // }
        // dd($user);
        $paginate = request('paginate') ?? 10;
        $data = People::where(function($query) use ($search){
                    $query->OrWhereRaw($search ? "id = '$search'" : 1)
                    ->OrWhereRaw($search ? "first_name like '%$search%'" : 1)
                    ->OrWhereRaw($search ? "last_name1 like '%$search%'" : 1)
                    ->OrWhereRaw($search ? "last_name2 like '%$search%'" : 1)
                    ->OrWhereRaw($search ? "CONCAT(first_name, ' ', last_name1, ' ', last_name2) like '%$search%'" : 1)
                    ->OrWhereRaw($search ? "ci like '%$search%'" : 1)
                    ->OrWhereRaw($search ? "cell_phone like '%$search%'" : 1);
                    // ->OrWhereRaw($search ? "phone like '%$search%'" : 1);
                    })
                    ->where('deleted_at', NULL)
                    ->orderBy('id', 'DESC')
                    ->paginate($paginate);
                    // $data = 1;
                    // dd($data->links());
        return view('people.list', compact('data'));
    }


    public function indexSponsor($id)
    {
        $people = People::find($id);
        $data = People::where('id', '!=', $id)->where('status',1)->where('deleted_at',null)->get();
        $sponsor = PeopleSponsor::with(['people'=>function($q)
                {
                    $q->where('deleted_at', null);
                }
            ])
            ->where('deleted_at', null)->where('people_id', $id)->get();

        return view('people.sponsor', compact('people', 'data', 'sponsor'));
    }

    public function storeSponsor(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // return $request;
            $ok = PeopleSponsor::where('people_id', $id)->where('deleted_at', null)->first();
            if($ok)
            {
                return redirect()->route('people-sponsor.index', ['id'=>$id])->with(['message' => 'Patrocinador existente.', 'alert-type' => 'error']);
            }
            PeopleSponsor::create([
                'people_id'=>$id,
                'sponsor_id'=>$request->sponsor_id,
                'observation'=>$request->observation
            ]);
            DB::commit();
            return redirect()->route('people-sponsor.index', ['id'=>$id])->with(['message' => 'Patrocinador registrado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('people-sponsor.index', ['id'=>$id])->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);

        }
    }

    public function destroySponsor($people, $sponsor)
    {
        DB::beginTransaction();
        try {
            PeopleSponsor::where('id', $sponsor)
                ->update([
                    'deleted_at'=>Carbon::now(),
                    'deleted_userId' => Auth::user()->id,
                    'deleted_agentType' => Auth::user()->role->name
                ]);
            DB::commit();
            return redirect()->route('people-sponsor.index', ['id'=>$people])->with(['message' => 'Eliminado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('people-sponsor.index', ['id'=>$people])->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function inhabilitarSponsor($people, $sponsor)
    {
        // return $people;
        DB::beginTransaction();
        try {
            PeopleSponsor::where('id', $sponsor)
                ->update([
                    'status'=>0,
                ]);
            DB::commit();
            return redirect()->route('people-sponsor.index', ['id'=>$people])->with(['message' => 'Inhabilitado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('people-sponsor.index', ['id'=>$people])->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function habilitarSponsor($people, $sponsor)
    {
        DB::beginTransaction();
        try {
            PeopleSponsor::where('id', $sponsor)
                ->update([
                    'status'=>1,
                ]);
            DB::commit();
            return redirect()->route('people-sponsor.index', ['id'=>$people])->with(['message' => 'Habilitado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('people-sponsor.index', ['id'=>$people])->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function verification(Request $request){
        DB::beginTransaction();
        try {
            
            if (setting('servidores.whatsapp') && setting('servidores.whatsapp-session')) {
                Http::post(setting('servidores.whatsapp').'/send?id='.setting('servidores.whatsapp-session'), [
                    'phone' => '591'.$request->phonee,
                    'text' => 'Hola *'.$request->name.'*, *CAPRESI* te da la Bienvenida. Para verificar tus datos personales has click en el siguiente enlace https://capresi.net/message/'.$request->id.'/verification',
                    'image_url' => '',
                ]);
            }
          
            DB::commit();
            return response()->json(['customer' => 1]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function import(Request $request)
    {
        $file = $request->file('file');
        Excel::import(new PeopleImport, $file);
        return 1;

    }

    public function store(Request $request){
        DB::beginTransaction();
        try {
            $ok = People::where('ci', $request->ci)->where('deleted_at', null)->first();
            if($ok){
                return response()->json(['error' => 'Ya existe una persona registrada con el mismo numero de CI']);
            }
            $request->merge(['register_userId'=>Auth::user()->id]);
            $person = People::create($request->all());
            DB::commit();
            return response()->json(['success' => 1, 'person' => $person]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['error' => $th->getMessage()]);
        }
    }

    //para la realizacion  de prestamos
    public function ajaxPeople(){
        $q = request('q');
        $data = [];
        // if ($q) {
            $data = People::whereRaw('(ci like "%'.$q.'%" or first_name like "%'.$q.'%" or last_name1 like "%'.$q.'%" or last_name2 like "%'.$q.'%" or CONCAT(first_name," " , last_name1, " ", last_name2) like "%'.$q.'%" )')->where('deleted_at', null)->get();
        // }
        return response()->json($data);
    }

    public function contractPeople(){
        // $q = request('q');
        // $data = [];
        // if ($q) {
        //     $data = People::with(['contract'])
        //         ->whereHas('contract', function($query) {
        //             // $query->where('status', 'aprobado'); // Solo contratos activos
        //             $query->whereIn('status', ['aprobado', 'finalizado']);
        //         })
        //         ->whereRaw('(ci like "%'.$q.'%" or first_name like "%'.$q.'%" or last_name1 like "%'.$q.'%" or last_name2 like "%'.$q.'%" or CONCAT(first_name," " , last_name1, " ", last_name2) like "%'.$q.'%" )')
        //         ->where('deleted_at', null)->get();
        // }
        // return response()->json($data);


        $q = request('q');
        $data = [];
        if ($q) {
            $data = People::whereRaw('(ci like "%'.$q.'%" or first_name like "%'.$q.'%" or last_name1 like "%'.$q.'%" or last_name2 like "%'.$q.'%" or CONCAT(first_name," " , last_name1, " ", last_name2) like "%'.$q.'%" )')->where('deleted_at', null)->get();
        }
        return response()->json($data);
    }

    public function peopleStore(Request $request){
        DB::beginTransaction();
        try {
            // Log::info('Datos recibidos en el request:', $request->all());
            $people =People::create($request->all());
            DB::commit();
            return response()->json(['people' => $people]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
}