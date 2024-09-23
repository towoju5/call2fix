<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function addRoleToUser(Request $request)
    {
        $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        $user = auth()->user();
        $user->assignRole($request->role);

        return redirect()->back()->with('success', 'Role added successfully');
    }

    public function removeRoleFromUser(Request $request)
    {
        $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        $user = auth()->user();
        $user->removeRole($request->role);

        return redirect()->back()->with('success', 'Role removed successfully');
    }
}
