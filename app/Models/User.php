<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 
        'email', 
        'password',
        'username',
        'first_name',
        'last_name',
        'phone',
        'address',
        'profile',
        'verification_code',
        'verification_code_expires_at',
        'role',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends =[
        'full_name',
        'profile_url'
    ];

    public function getFullNameAttribute()
    {
     if($this->first_name && $this->last_name){
         return $this->first_name . ' '. $this->last_name;
     }
     return $this->email;
    } 

    public function getProfileUrlAttribute()
    {
        $storage = Storage::disk('s3');
        if($this->profile){
            return $storage->temporaryUrl(
                $this->profile,
                now()->addMinutes(5)
            );
        }
        return null;
    }
}
