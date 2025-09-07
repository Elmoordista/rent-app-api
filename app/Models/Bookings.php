<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bookings extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'start_date',
        'end_date',
        'total_price',
        'status',
        'notes',
        'payment_status',
        'delivery_info',
        'payment_type',
        'delivery_option',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function booking_details()
    {
        return $this->hasMany(BookingDetail::class, 'booking_id', 'id');
    }

    public function payments()
    {
        return $this->hasOne(Payments::class, 'booking_id', 'id');
    }
}
