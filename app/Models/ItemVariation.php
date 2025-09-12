<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ItemVariation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'item_id',
        'size',
        'color',
        'quantity',
        'image',
    ];

    protected $appends = ['image_url'];

    public function item()
    {
        return $this->belongsTo(Items::class);
    }

    public function getImageUrlAttribute()
    {
        $storage = Storage::disk('s3');
        if($this->image){
            return $storage->temporaryUrl(
                $this->image,
                now()->addMinutes(5)
            );
        }
        return url($this->image_path);
    }

}
