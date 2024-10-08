<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Mail\ResetPasswordEmail;
use App\Mail\PasswordChangedConfirmation;
use Illuminate\Support\Facades\Mail;

class PasswordReset extends Model
{
    use HasFactory;
    protected $table = 'password_resets';

    protected $fillable = [
        'email', 'token'
    ];

    public $timestamps = true;

    public static function generateToken()
    {
        return Str::random(42);
    }

    public static function calculateExpirationTime()
    {
        return Carbon::now()->addMinutes(60);
    }

    public static function sendResetPasswordEmail($userEmail, $token, $email)
    {
        Mail::to($userEmail)->queue(new ResetPasswordEmail($token, $email));
    }

    public static function saveToken($email, $token, $expiration)
    {
        return static::create([
            'email' => $email,
            'token' => $token
        ]);
    }

    public static function passwordConfirmationEmail($email)
    {
        Mail::to($email)->queue(new PasswordChangedConfirmation($email));
    }
}
