<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
     public function verification($email_address, $code)
    {
        // $customer = User::where('email', $email_address)->first();
        
        return Mail::raw("Your verification code is: {$code}", function ($message) use ($email_address) {
            $message->to($email_address)
                ->subject('Email Verification Code')
                ->from(env('MAIL_FROM_ADDRESS'));
        });
    }

    public static function generateCode($length = 7){
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

}
