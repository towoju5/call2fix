<?php

namespace Modules\Artisan\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequestModel;
use App\Models\ArtisanCanSubmitQuote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TaskController extends Controller
{
    // get all service request where request_status is Work In Progress
    public function index()
    {
        try {
            // $tasks = ServiceRequestModel::latest()
            //             ->where('approved_artisan_id', auth()->id())
            //             ->with('service_provider', 'serviceCategory', 'submittedQuotes')->get();

            $requestIds = ArtisanCanSubmitQuote::whereArtisanId(auth()->id())->latest()->pluck("request_id");
            $tasks = ServiceRequestModel::whereIn('id', $requestIds)->with([
                'user',
                'service_provider',
                'artisan',
                'property',
                'serviceCategory',
                'service',
                'reworkMessages',
                'checkIns',
            ])->where('request_status', 'Work In Progress')->orWhere('request_status', 'Awaiting Approval')
                ->with('service_provider', 'serviceCategory', 'submittedQuotes')
                ->latest()->get();
            return get_success_response($tasks, "All tasks retrieved successfully");
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ["error" => $th->getMessage()]);
        }
    }
}
