<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KwikDelivery;

class KwikDeliveryController extends Controller
{
    public function index()
    {
        $serviceAreas = KwikDelivery::latest()->paginate(10);
        return view('admin.kwik_delivery.index', compact('serviceAreas'));
    }

    public function show(KwikDelivery $KwikDelivery)
    {
        return view('admin.kwik_delivery.show', compact('KwikDelivery'));
    }

    public function destroy(KwikDelivery $serviceArea)
    {
        $serviceArea->delete();

        return redirect()->route('admin.kwik_delivery.index')
            ->with('success', 'Delivery task deleted successfully');
    }
}
