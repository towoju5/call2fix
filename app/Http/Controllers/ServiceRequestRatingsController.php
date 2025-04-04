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
            "service_request_id" => "required|string",
            "work_quality" => "required|array",
            "work_quality.ratings" => "nullable|numeric",
            "work_quality.comment" => "nullable|string",
            "timeliness" => "required|array",
            "timeliness.ratings" => "nullable|numeric",
            "timeliness.comment" => "nullable|string",
            "communication" => "required|array",
            "communication.ratings" => "nullable|numeric",
            "communication.comment" => "nullable|string",
            "professionalism" => "required|array",
            "professionalism.ratings" => "nullable|numeric",
            "professionalism.comment" => "nullable|string",
            "cleanliness" => "required|array",
            "cleanliness.ratings" => "nullable|numeric",
            "cleanliness.comment" => "nullable|string",
            "pricing_transparency" => "required|array",
            "pricing_transparency.ratings" => "nullable|numeric",
            "pricing_transparency.comment" => "nullable|string",
            "tools_quality" => "required|array",
            "tools_quality.ratings" => "nullable|numeric",
            "tools_quality.comment" => "nullable|string",
            "issue_handling" => "required|array",
            "issue_handling.ratings" => "nullable|numeric",
            "issue_handling.comment" => "nullable|string",
            "safety_adherence" => "required|array",
            "safety_adherence.ratings" => "nullable|numeric",
            "safety_adherence.comment" => "nullable|string",
            "overall_satisfaction" => "required|array",
            "overall_satisfaction.ratings" => "nullable|numeric",
            "overall_satisfaction.comment" => "nullable|string",
            "comment" => "nullable|string",
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
