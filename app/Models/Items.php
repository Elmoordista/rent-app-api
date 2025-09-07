<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Items extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'owner_id',
        'category_id',
        'price_per_day',
        'available',
        'status',
        'location',
    ];

    protected $appends = ['is_favorite'];

    public function images()
    {
        return $this->hasMany(ItemImages::class, 'item_id', 'id');
    }

    public function reviews()
    {
        return $this->hasMany(ItemRating::class, 'item_id', 'id');
    }
    
    public function getIsFavoriteAttribute()
    {
        $user = Auth::user();
        return $user ? $this->favorites()->where('user_id', $user->id)->exists() : false;
    }
    
    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'item_id', 'id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
}
