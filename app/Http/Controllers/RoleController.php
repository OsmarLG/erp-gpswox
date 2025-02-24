<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        return view('security.roles.roles');
    }

    public function show($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return abort(404);
        }

        return view('security.roles.show', compact('role'));
    }
}
