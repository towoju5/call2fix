<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    public function login(Request $request)
    {
        try {
            $credentials = $request->only('email', 'password');
            $remember = true;

            if (Auth::guard('admin')->attempt($credentials, $remember)) {
                $request->session()->regenerate();
                return redirect()->route('admin.dashboard')->with('success', 'Login successful');
            }

            return back()->withErrors(['email' => 'Invalid credentials'])->withInput($request->only('email'));

        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred during login. Please try again.');
        }
    }

    public function index()
    {
        $admins = Admin::with('roles')->get();
        return view('admin.admins.index', compact('admins'));
    }

    public function create()
    {
        $roles = Role::where('guard_name', 'admin')->get();
        return view('admin.admins.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'required|array',
        ]);

        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $admin->syncRoles($request->roles);

        return redirect()->route('admin.admins.index')->with('success', 'Admin created successfully');
    }

    public function edit(Admin $admin)
    {
        $roles = Role::where('guard_name', 'admin')->get();
        return view('admin.admins.edit', compact('admin', 'roles'));
    }

    public function update(Request $request, Admin $admin)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins,email,' . $admin->id,
            'roles' => 'required|array',
        ]);

        $admin->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        $admin->syncRoles($request->roles);

        return redirect()->route('admin.admins.index')->with('success', 'Admin updated successfully');
    }

    public function destroy(Admin $admin)
    {
        $admin->delete();
        return redirect()->route('admin.admins.index')->with('success', 'Admin deleted successfully');
    }

    public function assignSuperAdmin(Admin $admin)
    {
        $superAdminRole = Role::findByName('super-admin', 'admin');
        $admin->assignRole($superAdminRole);
        return redirect()->route('admin.admins.index')->with('success', 'Super Admin role assigned successfully');
    }
}
