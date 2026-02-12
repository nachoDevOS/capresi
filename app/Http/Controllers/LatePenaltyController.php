<?php

namespace App\Http\Controllers;

use App\Models\LatePenalty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LatePenaltyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create()
    {
        // return 1;
        $data = null;
        return view('adm-attendances.latePenalty.edit-add', compact('data'));        
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        DB::beginTransaction();
        try {
            $latePenalty = LatePenalty::where('status', 1)->where('deleted_at', null)->get();

                $ok=true;
                foreach ($latePenalty as $sh) {
                    if($request->start >= $sh->start && $request->start <= $sh->finish)
                    {
                        $ok=false;
                    }

                    if($request->finish >= $sh->start && $request->finish <= $sh->finish)
                    {
                        $ok=false;
                    }
                }
                //si es verdadero y no se interceptan las horas se registra
                if($ok)
                {
                    LatePenalty::create([
                        'start'=>$request->start,
                        'finish'=>$request->finish,
                        'amount'=>$request->amount,

                        'register_userId'=>$user->id,
                        'register_agentType' =>$user->role->name
            
                    ]);
                }
                else
                {
                    DB::rollBack();
                    return redirect()->route('voyager.late-penalties.create')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);                  
                }
            

            DB::commit();
            return redirect()->route('voyager.late-penalties.index')->with(['message' => 'Sanción registrada exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('voyager.late-penalties.create')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function edit($id)
    {
        $data = LatePenalty::where('deleted_at', null)->where('id', $id)->first();
        // return $data;
        return view('adm-attendances.latePenalty.edit-add', compact('data'));  
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        DB::beginTransaction();
        try {
            $latePenalty = LatePenalty::where('status', 1)->where('id', '!=', $id)->where('deleted_at', null)->get();
                $ok=true;
                
                foreach ($latePenalty as $sh) {
                    if($request->start >= $sh->start && $request->start <= $sh->finish)
                    {
                        $ok=false;
                    }

                    if($request->finish >= $sh->start && $request->finish <= $sh->finish)
                    {
                        $ok=false;
                    }
                }
                //si es verdadero y no se interceptan las horas se registra
                if($ok)
                {
                    $late =LatePenalty::where('id', $id)->where('deleted_at', null)->first();
                    $late->update([
                        'start'=>$request->start,
                        'finish'=>$request->finish,
                        'amount'=>$request->amount,
                    ]);
                }
                else
                {
                    DB::rollBack();
                    return redirect()->route('voyager.late-penalties.edit', ['id'=>$id])->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);                  
                }

            DB::commit();
            return redirect()->route('voyager.late-penalties.index')->with(['message' => 'Sanción actualizada exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('voyager.late-penalties.edit', ['id'=>$id])->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }
}
