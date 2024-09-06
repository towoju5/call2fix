<?php

namespace Modules\Artisan\Http\Controllers;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Artisan\Models\RequestQuotes;
use Validator;

class RequestQuoteController extends Controller
{
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $validate = Validator::make($request->all(), [
                "request_id" => "required",
                "workmanship" => "required",
                "sla_duration" => "required",
                "sla_start_date" => "required",
                "summary_note" => "required",
                "service_vat" => "required",
            ]);

            if ($validate->fails()) {
                return response()->json(['errors' => $validate->errors()], 422);
            }

            $artisan = auth()->user();
            $data = $validate->validated();
            if($artisan->hasRole('artisan')){
                $data['is_artisan'] = true;
                $data['artisan_id'] = $artisan->id;
            }

            $requestQuote = RequestQuotes::create([
                'artisan_id' => auth()->id(),
                'request_id' => $request->request_id,
                'workmanship' => $request->workmanship,
                'sla_duration' => $request->sla_duration,
                'sla_start_date' => $request->sla_start_date,
                'summary_note' => $request->summary_note,
                'administrative_fee' => get_settings_value('administrative_fee'),
                'service_vat' => $request->service_vat,
            ]);

            if ($request->has('items')) {
                foreach ($request->input('items') as $itemData) {
                    $requestQuote->items()->create($itemData);
                }
            }

            if ($request->has('attachments')) {
                foreach ($request->input('attachments') as $attachmentData) {
                    $requestQuote->attachments()->create($attachmentData);
                }
            }

            DB::commit();

            return response()->json(['message' => 'Request quote created successfully', 'data' => $requestQuote], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error creating request quote', 'error' => $e->getMessage()], 500);
        }
    }
}
