<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentApportionment extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_request_id',
        'subtotal',
        'service_provider_earnings',
        'call2fix_management_fee',
        'call2fix_earnings',
        'warranty_retention',
        'artisan_earnings'
    ];

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequestModel::class);
    }
}