<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemImages extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'image_path',
        'image_type',
        'is_primary',
        'image_size',
    ];

    protected $appends = [
        'image_url',
    ];

    public function getImageUrlAttribute()
    {
        return asset($this->image_path);
    }
}
