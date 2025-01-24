<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Negotiation extends Model
{
    use HasFactory;

    protected $fillable = [
        'submitted_quote_id',
        'request_id', 
        'provider_id',
        'price',
        'status'
    ];

    protected $casts = [
        'price' => 'float'
    ];

    // Define which relationships should be eager loaded by default
    // protected $with = [
    //     'submittedQuote',
    //     'serviceRequest',
    //     'provider'
    // ];

    // Add appends for any computed attributes you want included in responses
    protected $appends = [
        'formatted_price'
    ];

    public function submittedQuote()
    {
        return $this->belongsTo(SubmittedQuotes::class, 'submitted_quote_id');
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2);
    }
}
