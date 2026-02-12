<?php

namespace App\Http\Controllers;

use App\Imports\AttendanceImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;


class AttendanceController extends Controller
{
    public function index()
    {
        return view('adm-attendances.attendances.browse');
    }

    public function list($search = null){
        $user = Auth::user();
        $paginate = request('paginate') ?? 10;
     
        $data = DB::table('attendances')
            ->leftJoin('people', 'attendances.ci', '=', 'people.ci')
            ->where('people.status', 1)
            ->select(
                'attendances.*',
                DB::raw("COALESCE(CONCAT(people.first_name, ' ', people.last_name1,' ', people.last_name2), 'Sin Nombre') as name")
            )
            ->where(function ($query) use ($search) {
                $query->where(DB::raw("CONCAT(people.first_name, ' ', people.last_name1, ' ', people.last_name2)"), 'like', "%$search%") // Busca por nombree
                    ->orWhereDate('attendances.date', '=', $search) // Buscar por fecha 
                    ->orWhereTime('attendances.hour', '=', $search); // Buscarr por hora
            })
            ->orderBy('attendances.date', 'desc')
            ->orderBy('attendances.hour', 'desc')

            ->paginate($paginate);
        // dump('Hola');

        return view('adm-attendances.attendances.list', compact('data'));        
    }


    // public function import()
    // {
    //     $this->validate(request(), [
    //         'file' => 'required|mimetypes::'.
    //             'application/vnd.ms-office,'.
    //             'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,'.
    //             'application/vnd.ms-excel',
    //     ]);

    //     try {
    //         set_time_limit(0);
    //         DB::beginTransaction();
    //         Excel::import(new UsersImport(), request("file"));
    //         DB::commit();
    //         return back()
    //             ->with('notification', ['type' => 'success', 'title' => 'Usuarios importados']);
    //     } catch (\Exception $exception) {
    //         DB::rollBack();
    //         return back()
    //             ->with('notification', ['type' => 'danger', 'title' => 'Error importando usuarios']);
    //     }
    // }

    public function import(Request $request)
    {

        DB::beginTransaction();
        try {
            $file = $request->file('file');
            Excel::import(new AttendanceImport, $file);
            DB::commit();
            return redirect()->route('attendances.index')->with(['message' => 'Asistenacia importada exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            // return 0;
            return redirect()->route('attendances.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }

    }
}
