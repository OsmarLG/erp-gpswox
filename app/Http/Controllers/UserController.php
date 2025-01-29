<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return view('user.index');
    }

    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return abort(404);
        }

        return view('user.show', compact('user'));
    }
}
