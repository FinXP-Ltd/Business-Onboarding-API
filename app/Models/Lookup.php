<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lookup extends Model
{
    use HasFactory;

    protected $fillable = ['lookup_type_id'];
    protected $visible = ['lookup_type'];

    function lookuptable()
    {
        return $this->morphTo();
    }

    function lookupType()
    {
        return $this->belongsTo(LookupType::class, 'lookup_type_id', 'id');
    }
}
