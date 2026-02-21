<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoanRecoveryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {   
        return view('loanRecoveries.browse');
    }

    public function list($search = null){
        $user = Auth::user();
        $paginate = request('paginate') ?? 10;

        $data = Loan::with(['loanDay', 'loanRoute', 'people', 'payments_period'])
                ->where(function($query) use ($search){
                if($search){
                    $query->OrwhereHas('people', function($query) use($search){
                    $query->whereRaw("(ci like '%$search%' or first_name like '%$search%' or last_name1 like '%$search%' or last_name2 like '%$search%' or CONCAT(first_name, ' ', last_name1, ' ', last_name2) like '%$search%')");
                })
                ->OrWhereRaw($search ? "typeLoan like '%$search%'" : 1)
                ->OrWhereRaw($search ? "code like '%$search%'" : 1);
            }
            })
            ->where('deleted_at', NULL)
            ->where('status', 'entregado')
            ->where('debt', '!=', 0)
            ->where('recovery', 'si')
            ->orderBy('dateDelivered', 'DESC')->paginate($paginate);

        return view('loanRecoveries.list', compact('data'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $loans = Loan::where('deleted_at', NULL)
                    ->where('status', 'entregado')
                    ->where('debt', '!=', 0)
                    ->whereDate('dateDelivered', '>=', $request->start)
                    ->whereDate('dateDelivered', '<=', $request->finish)
                    ->where('recovery', 'no')
                    ->orderBy('dateDelivered', 'DESC')
                    ->get();

            foreach ($loans as $item) {
                $item->update([
                    'recovery'=>'si'
                ]);
            }
            DB::commit();
            return redirect()->route('loanRecoveries.index')->with(['message' => 'Prestamos asignado a la nueva cartera exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('loanRecoveries.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }


    public function listPrint()
    {
        
        $data = Loan::with(['loanDay', 'loanRoute', 'people', 'payments_period'])
            ->where('deleted_at', NULL)
            ->where('status', 'entregado')
            ->where('debt', '!=', 0)
            ->where('recovery', 'si')
            ->orderBy('date', 'DESC')
            ->get();
        // return 1;

        return view('loanRecoveries.print', compact('data'));

    }



}
