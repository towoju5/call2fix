<?php

namespace Modules\Artisan\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequestModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TaskController extends Controller
{
    // get all service request where request_status is Work In Progress
    public function index()
    {
        try {
            $tasks = ServiceRequestModel::query() //where('request_status', 'Work In Progress')->where('request_status', 'Awaiting Approval')
                        // ->where('approved_artisan_id', auth()->id())
                        ->with('service_provider', 'serviceCategory', 'submittedQuotes')->get();
            return get_success_response($tasks, "All tasks retrieved successfully");
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ["error" => $th->getMessage()]);
        }
    }
}
