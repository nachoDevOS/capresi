<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Device;
use App\Models\Location;

class OwnTracksController extends Controller
{
    public function store(Request $request, $user, $device)
    {
        // Autenticación básica con token Voyager
        if ($request->header('Authorization') !== 'Bearer '.config('voyager.api.token')) {
            abort(401);
        }

        $data = $request->validate([
            '_type' => 'required|string',
            'lat' => 'required|numeric',
            'lon' => 'required|numeric',
            'tst' => 'required|integer',
        ]);

        $device = Device::firstOrCreate([
            'user_id' => $user,
            'device_id' => $device
        ]);

        $location = $device->locations()->create([
            'latitude' => $data['lat'],
            'longitude' => $data['lon'],
            'timestamp' => \Carbon\Carbon::createFromTimestamp($data['tst']),
            'battery' => $request->input('batt'),
            'accuracy' => $request->input('acc'),
        ]);

        return response()->json(['status' => 'OK']);
    }

    public function show($user, $device)
    {
        return response()->json(['status' => 'READY']);
    }
}
