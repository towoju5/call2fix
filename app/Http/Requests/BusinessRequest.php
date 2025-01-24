<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BusinessRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'businessName' =>'required|string|max:255',
            'cacNumber' =>'required|string|max:255',
            'officeAddress' =>'required|string|max:255',
            'businessCategory' =>'required|string|max:255',
            'businessDescription' =>'required|string|max:255',
            'businessIdType' =>'required|string|max:255',
            'businessIdNumber' =>'required|string|max:255',
            'businessIdImage' =>'required|file:img,png,jpg,pdf|max:255',
            'businessBankInfo.bankCode' => 'required',
            'businessBankInfo.accountNumber' => 'required|string|max:11', 
        ];
    }
}
