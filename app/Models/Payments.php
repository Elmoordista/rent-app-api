<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
        $storage = Storage::disk('s3');
        if($this->proof_of_payment){
            return $storage->temporaryUrl(
                $this->proof_of_payment,
                now()->addMinutes(5)
            );
        }
        return url($this->proof_of_payment);
    }
}
