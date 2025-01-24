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
        
        try {
            $user->assignRole($request->role);
            $user->load('roles');
            
            return get_success_response($user->getRoleNames(), 'Role added successfully');
        } catch (\Exception $e) {
            return get_error_response("Unable to enable/activate requested service: " . $e->getMessage());
        }
    }

    public function getUserRoles()
    {
        
        try {
            $user = auth()->user();
            return get_success_response($user->getRoleNames(), 'Roles fetched successfully');
        } catch (\Exception $e) {
            return get_error_response("Unable to retrieve requested service: " . $e->getMessage());
        }
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
