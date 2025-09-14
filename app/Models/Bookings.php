<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    protected $casts = [
        'total_price' => 'float', // or 'decimal:2'
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

    public function getNotesAttribute()
    {
        // Decode JSON as associative array
        $notes = json_decode($this->attributes['notes'], true);

        if($notes && !empty($notes['driver_license']) && empty($notes['book_with_driver'])){
            $storage = Storage::disk('s3');

            $notes['driver_license_url'] = $storage->temporaryUrl(
                $notes['driver_license'], // make sure you use the value from notes
                now()->addMinutes(5)
            );
        }

        return json_encode($notes);
    }
}
