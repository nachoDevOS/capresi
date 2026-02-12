<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class BlockAccessAfterHours
{
    public function handle($request, Closure $next)
    {
        $currentTime = Carbon::now();
        $startHour = 8; 
        $endHour = 21; 

        // if (Auth::check() && env('APP_SYSTEM_LIMIT_HOUR'))
        // {
        //     if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('gerente') || auth()->user()->hasRole('administrador')) {
        //         return $next($request);
        //     }
        //     $name = Auth::user()->name;

        //     if ($currentTime->hour < $startHour || $currentTime->hour >= $endHour) {
        //         Auth::logout(); // Cierra la sesión del usuario
        //         return redirect('/admin/login')->withErrors([
        //             'msg' => 'Hola ' . $name . '<br>El sistema solo está disponible entre las 6:00 AM y las 10:00 PM.',
        //         ]);
        //     }
        // }

        return $next($request);
    }
}
