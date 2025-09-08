<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
        $storage = Storage::disk('s3');
        if($this->image_path){
            return $storage->temporaryUrl(
                $this->image_path,
                now()->addMinutes(5)
            );
        }
        return url($this->image_path);
    }
}
