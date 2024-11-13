<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionsController extends Controller
{
    /**
     * Display a listing of permissions
     */
    public function index()
    {
        $permissions = Permission::orderBy('name')
                        // ->orderBy('id', 'desc')
                        ->paginate(get_settings_value('per_page', 15));
                        
        return view('admin.permissions.index', compact('permissions'));
    }

    /**
     * Show form for creating permissions
     */
    public function create()
    {
        return view('admin.permissions.create');
    }

    /**
     * Store a newly created permission
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:permissions,name'
        ]);

        \Spatie\Permission\Models\Permission::create($validated);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission created successfully');
    }

    /**
     * Show form for editing permission
     */
    public function edit(\Spatie\Permission\Models\Permission $permission)
    {
        return view('admin.permissions.edit', compact('permission'));
    }

    /**
     * Update the specified permission
     */
    public function update(Request $request, \Spatie\Permission\Models\Permission $permission)
    {
        $validated = $request->validate([
            'name' => 'required|unique:permissions,name,' . $permission->id
        ]);

        $permission->update($validated);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission updated successfully');
    }

    /**
     * Delete permission
     */
    public function destroy(\Spatie\Permission\Models\Permission $permission)
    {
        $permission->delete();

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission deleted successfully');
    }

    /**
     * Show permission details
     */
    public function show(\Spatie\Permission\Models\Permission $permission)
    {
        return view('admin.permissions.show', compact('permission'));
    }

    /**
     * Assign permission to role
     */
    public function assignRole(Request $request, \Spatie\Permission\Models\Permission $permission)
    {
        if ($permission->hasRole($request->role)) {
            return back()->with('error', 'Role already assigned.');
        }

        $permission->assignRole($request->role);
        return back()->with('success', 'Role assigned.');
    }

    /**
     * Remove permission from role
     */
    public function removeRole(\Spatie\Permission\Models\Permission $permission, \Spatie\Permission\Models\Role $role)
    {
        if ($permission->hasRole($role)) {
            $permission->removeRole($role);
            return back()->with('success', 'Role removed.');
        }

        return back()->with('error', 'Role not assigned.');
    }

}
