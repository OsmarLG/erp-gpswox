<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehiculosController extends Controller
{
    public function index()
    {
        return view('vehicles.index');
    }

    public function show($id)
    {
        $vehiculo = Vehicle::find($id);
        
        if (!$vehiculo) {
            return abort(404);
        }
        
        return view('vehicles.show', compact('vehiculo'));
    }
}
