<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestQuotation extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        return $user->hasPermission('create-request');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $validated = [
            'request_id' => 'required',
            'workmanship' => "required|numeric",
            'items' => 'sometimes|array',
            'items.*.material_type_id' => 'sometimes',
            'items.*.description' => 'sometimes',
            'items.*.unit_price' => 'sometimes',
            'items.*.quantity' => 'sometimes',
            'sla_duration' => 'required',
            'sla_start_date' => 'required',
            'attachments' => 'file|mimes:pdf,jpg,png,jpeg',
            'summary_note' => 'sometimes'
        ];

        $validated['user_id'] = auth()->id();

        return $validated;
    }
}
