<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payments extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'amount',
        'proof_of_payment',
        'status',
        'notes',
        'paid_at',
    ];

    protected $appends = [
        'proof_of_payment_url',
    ];

    public function getProofOfPaymentUrlAttribute()
    {
        return asset($this->proof_of_payment);
    }
}
