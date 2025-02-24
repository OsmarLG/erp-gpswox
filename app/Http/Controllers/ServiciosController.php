<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ServiciosController extends Controller
{
    public function index()
    {
        return view('servicios.index');
    }

    public function service($id)
    {
        return view('servicios.service', ['servicio' => $id]);
    }

    public function create()
    {
        return view('servicios.create');
    }
}
