<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// Models
use App\Models\PawnRegisterPayment;

class HomeController extends Controller
{
    public function payment_notification($id){
        $payment = PawnRegisterPayment::with(['pawn.person', 'user'])->where('id', $id)->first();
        return view('pawn.print.notification', compact('payment'));
    }
}
