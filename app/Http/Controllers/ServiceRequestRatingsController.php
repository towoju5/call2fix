<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequestRatings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceRequestRatingsController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_request_id' => 'required|exists:service_requests,id',
            'work_quality' => 'required|integer|min:1|max:5',
            'timeliness' => 'required|integer|min:1|max:5',
            'communication' => 'required|integer|min:1|max:5',
            'professionalism' => 'required|integer|min:1|max:5',
            'cleanliness' => 'required|integer|min:1|max:5',
            'pricing_transparency' => 'required|integer|min:1|max:5',
            'tools_quality' => 'required|integer|min:1|max:5',
            'issue_handling' => 'required|integer|min:1|max:5',
            'safety_adherence' => 'required|integer|min:1|max:5',
            'overall_satisfaction' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return get_error_response("Validation Error", $validator->errors(), 422);
        }

        $rating = ServiceRequestRatings::updateOrCreate([
            'service_request_id' => $request->service_request_id,
            'user_id' => auth()->id(),
            'work_quality' => $request->work_quality,
            'timeliness' => $request->timeliness,
            'communication' => $request->communication,
            'professionalism' => $request->professionalism,
            'cleanliness' => $request->cleanliness,
            'pricing_transparency' => $request->pricing_transparency,
            'tools_quality' => $request->tools_quality,
            'issue_handling' => $request->issue_handling,
            'safety_adherence' => $request->safety_adherence,
            'overall_satisfaction' => $request->overall_satisfaction,
            'comment' => $request->comment,
        ]);

        return get_success_response($rating, 'Rating submitted successfully', 201);
    }

    public function show($id)
    {
        $rating = ServiceRequestRatings::where('service_request_id', $id)->first();
        if(!$rating) {
            return get_error_response("Rating not found", ['error' => 'Rating not found!'], 404);
        }
        return get_success_response($rating, 'Rating retrieved successfully', 200);
    }
}
