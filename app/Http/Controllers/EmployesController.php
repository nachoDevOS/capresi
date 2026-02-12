<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

// Models
use App\Models\Employe;
use App\Models\EmployePayment;

class EmployesController extends Controller
{

    public function index()
    {
        return view('employes.browse');
    }


    public function list($search = null){
        $user = Auth::user();
        // return 1;

        $paginate = request('paginate') ?? 10;
        $data = Employe::with(['employeJob'])
                    ->where(function($query) use ($search){
                        $query->OrWhereRaw($search ? "id = '$search'" : 1)
                        ->OrWhereRaw($search ? "full_name like '%$search%'" : 1)
                        ->OrWhereRaw($search ? "address like '%$search%'" : 1)
                        // ->OrWhereRaw($search ? "CONCAT(first_name, ' ', last_name1, ' ', last_name2) like '%$search%'" : 1)
                        ->OrWhereRaw($search ? "ci like '%$search%'" : 1)
                        ->OrWhereRaw($search ? "phone like '%$search%'" : 1);
                        // ->OrWhereRaw($search ? "phone like '%$search%'" : 1);
                    })
                    ->where('deleted_at', NULL)->orderBy('id', 'DESC')->paginate($paginate);

        // dump($data);
        // dd($data);
        return view('employes.list', compact('data'));
    }


    public function payments_index($id){
        $employe = Employe::find($id);
        return view('employes.payments', compact('employe'));
    }

    public function payments_store($id, Request $request){
        $redirect = $request->redirect ?? $_SERVER['HTTP_REFERER'];
        try {
            EmployePayment::create([
                'employe_id' => $id,
                'user_id' => Auth::user()->id,
                'description' => $request->description,
                'amount' => $request->amount,
                'date' => $request->date
            ]);
            return redirect()->to($redirect)->with(['message' => 'Adelanto registrado', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            return redirect()->to($redirect)->with(['message' => 'Ocurrió un error', 'alert-type' => 'error']);
        }
    }

    public function payoff_store($id, Request $request){
        $redirect = $request->redirect ?? $_SERVER['HTTP_REFERER'];
        DB::beginTransaction();
        try {
            for ($i=0; $i < count($request->payment_id); $i++) { 
                EmployePayment::where('id', $request->payment_id[$i])->update([
                    'status' => 'saldado'
                ]);
            }
            DB::commit();
            return redirect()->to($redirect)->with(['message' => 'Adelanto saldado', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->to($redirect)->with(['message' => 'Ocurrió un error', 'alert-type' => 'error']);
        }
    }
}