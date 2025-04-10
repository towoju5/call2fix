<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Models\ServiceRequestRatings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class ServiceRequestRatingsController extends Controller
{
    public function __construct()
    {
        // Check if 'read_by' column exists, if not, add it (This should be done in a migration)
        if (!Schema::hasColumn('service_request_ratings', 'status')) {
            Schema::table('service_request_ratings', function (Blueprint $table) {
                $table->string('service_provider_id')->nullable();
            });
        }
    }

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

        $service = ServiceRequest::whereId($request->service_request_id)->first();
        if(!$service) {
            return get_error_response("Service request not found", ['error' => "Service request not found"]);
        }

        $rating = ServiceRequestRatings::updateOrCreate([
            'service_request_id' => $request->service_request_id,
            'user_id' => auth()->id(),
            'service_provider_id' => $service->approved_providers_id,
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

    public function averageRatingByUser($userId)
    {
        // Get all ratings submitted by the user
        $ratings = ServiceRequestRatings::where('service_provider_id', $userId)->get();

        if ($ratings->isEmpty()) {
            return get_error_response("No ratings found for this user", ['error' => 'Service provider has no ratings'], 404);
        }

        $total = 0;
        $count = 0;

        foreach ($ratings as $rating) {
            // Extract all 10 criteria fields
            $criteria = [
                'work_quality',
                'timeliness',
                'communication',
                'professionalism',
                'cleanliness',
                'pricing_transparency',
                'tools_quality',
                'issue_handling',
                'safety_adherence',
                'overall_satisfaction',
            ];

            foreach ($criteria as $field) {
                if (isset($rating->$field['ratings']) && is_numeric($rating->$field['ratings'])) {
                    $total += $rating->$field['ratings'];
                    $count++;
                }
            }
        }

        if ($count === 0) {
            return get_error_response("No valid ratings found", ['error' => 'User has no valid numeric ratings'], 404);
        }

        $average = round($total / $count, 2); // out of 5

        return get_success_response([
            'user_id' => $userId,
            'average_rating' => $average,
            'out_of' => 5,
            'total_criteria' => $count,
        ], 'Average rating calculated successfully', 200);
    }

}
