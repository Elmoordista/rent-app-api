<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'account_name',
        'account_number',
        'qr_code_image',
    ];
}
