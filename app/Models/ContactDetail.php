<?php

namespace App\Models;

use App\Traits\Scopes\HasBusinessScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
class ContactDetail extends Model
{
    use HasFactory, HasBusinessScope;

    protected $fillable = [
        'first_name',
        'last_name',
        'position_held',
        'country_code',
        'mobile_no',
        'email',
    ];

    protected $hidden = ['created_at', 'updated_at', 'id', 'business_id'];

    protected function mobileNo(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => str_replace('+','',$value),
            set: fn ($value) => str_replace('+','',$value)
        );
    }

    protected function countryCode(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => str_replace('+','',$value),
            set: fn ($value) => str_replace('+','',$value)
        );
    }

    function business()
    {
        return $this->belongsTo(Business::class);
    }
}
