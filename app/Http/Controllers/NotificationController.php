<?php

namespace App\Http\Controllers;

use App\Models\Cashier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function cashierOpen()
    {
        $user = Auth::user();

        return Cashier::where('user_id', $user->id)->where('status', 'apertura pendiente')->where('deleted_at', null)->first();
    }
}
