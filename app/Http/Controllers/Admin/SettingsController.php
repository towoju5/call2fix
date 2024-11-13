<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use App\Models\Settings;

class SettingsController extends Controller
{
    /**
     * Display settings page
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $settings = Settings::all()->pluck('value', 'key')->toArray();
        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update settings
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $key)
    {
        $validated = $request->validate([
            'setting_value' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            Settings::where('key', $key)->update([
                'value' => $validated['setting_value']
            ]);

            DB::commit();
            return redirect()->route('admin.settings.index')
                ->with('success', 'Setting updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }


    /**
     * Reset settings to default
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reset()
    {
        Settings::truncate();

        // Add default settings
        $defaults = [
            'site_name' => 'My Application',
            'site_description' => 'A Laravel Application',
            'site_email' => 'admin@example.com',
            'site_logo' => 'default-logo.png',
            'maintenance_mode' => false,
        ];

        foreach ($defaults as $key => $value) {
            Settings::create([
                'key' => $key,
                'value' => $value
            ]);
        }

        return redirect()->back()->with('success', 'Settings reset to default');
    }

}
