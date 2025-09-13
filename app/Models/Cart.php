<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'item_id',
        'variation_id',
        'quantity',
    ];

    public function item()
    {
        return $this->belongsTo(Items::class, 'item_id');
    }

    public function variation()
    {
        return $this->belongsTo(ItemVariation::class, 'variation_id');
    }
}
