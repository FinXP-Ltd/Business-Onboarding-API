<?php

namespace App\Models;

use App\Traits\Scopes\HasBusinessScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessAddress extends Model
{
    use HasFactory, HasBusinessScope;

    protected $fillable = ['line_1', 'line_2', 'city', 'postal_code', 'country', 'lookup_type_id'];

    protected $hidden = ['created_at', 'updated_at', 'id', 'business_id', 'lookup_type_id'];

    function business()
    {
        return $this->belongsTo(Business::class);
    }

    function addressType()
    {
        return $this->belongsTo(LookupType::class, 'lookup_type_id', 'id');
    }
}
