<?php

namespace App\Imports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\People;

class AttendanceImport implements ToModel
{
    // Variable para llevar el contador de filas
    protected $counter = 0;

    public function model(array $row)
    {
        $agent = new Controller();

        // Si es la primera fila, la omites (encabezado)
        if ($this->counter === 0) {
            $this->counter++; // Incrementa el contador para procesar las siguientes filas
            return null; // No procesar la primera fila
        }

        // $people = People::where('ci', $row[1])->where('deleted_at', null)->first();

        $ci = $row[1]; // ID del usuario
        $date = $row[3];  // Fecha de la asistencia
        $hour = $row[4];   // Hora de la asistencia

        // Verifica si ya existe el registro en la base de datos
        $existe = Attendance::where('ci', $ci)
                            ->where('date', $date)
                            ->where('hour', $hour)
                            ->exists();

        // dump($existe);
        // return $existe;
        $fecha = '2024-11-07';
        if (!$existe && $date >= $fecha) {
            return new Attendance([
                'ci'=>$ci,
                'date'=>$date,
                'hour'=>$hour,

                'register_userId' => Auth::user()->id,
                'register_agentType' => Auth::user()->role->id
                
            ]);
        }
        return null;
    }
}
