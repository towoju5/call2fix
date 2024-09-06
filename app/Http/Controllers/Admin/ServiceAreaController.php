<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceArea;

class ServiceAreaController extends Controller
{
    public function index()
    {
        $serviceAreas = ServiceArea::all();
        return view('admin.service_areas.index', compact('serviceAreas'));
    }

    public function create()
    {
        return view('admin.service_areas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'service_area_title' => 'required|string|max:255',
        ]);

        ServiceArea::create($request->all());

        return redirect()->route('admin.service_areas.index')
            ->with('success', 'Service Area created successfully.');
    }

    public function show(ServiceArea $serviceArea)
    {
        return view('admin.service_areas.show', compact('serviceArea'));
    }

    public function edit(ServiceArea $serviceArea)
    {
        return view('admin.service_areas.edit', compact('serviceArea'));
    }

    public function update(Request $request, ServiceArea $serviceArea)
    {
        $request->validate([
            'service_area_title' => 'required|string|max:255',
        ]);

        $serviceArea->update($request->all());

        return redirect()->route('admin.service_areas.index')
            ->with('success', 'Service Area updated successfully');
    }

    public function destroy(ServiceArea $serviceArea)
    {
        $serviceArea->delete();

        return redirect()->route('admin.service_areas.index')
            ->with('success', 'Service Area deleted successfully');
    }
}
