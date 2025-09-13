<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'item_id',
        'variation_id',
        'quantity',
        'price',
    ];
    public function booking()
    {
        return $this->belongsTo(Bookings::class, 'booking_id');
    }

    public function item()
    {
        return $this->hasOne(Items::class, 'id', 'item_id')->withTrashed();
    }

    public function variation()
    {
        return $this->hasOne(ItemVariation::class, 'id', 'variation_id')->withTrashed();
    }
}
