<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class BusinessCompositionable extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'business_compositionable';

    protected $fillable = [
        'business_composition_id',
        'uid',
        'application_id',
        'entity_id',
        'entity_type_id'
    ];

    public function businessComposition()
    {
        return $this->belongsTo(BusinessComposition::class);
    }

    public function businessCompositionable()
    {
        return $this->morphTo();
    }
}
