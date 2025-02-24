<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PartesController extends Controller
{
    public function index()
    {
        return view('partes.index');
    }
}
