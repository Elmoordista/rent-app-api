<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bookings extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'user_id',
        'start_date',
        'end_date',
        'total_price',
        'status',
        'notes',
        'payment_status',
    ];
}
