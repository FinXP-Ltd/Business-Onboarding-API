<?php

namespace App\Models;

use App\Traits\Scopes\HasBusinessScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class BusinessComposition extends Model
{
    use HasFactory, HasUuids, HasBusinessScope;

    const MODEL_TYPE = ['P', 'N'];

    const DIRECTOR = 'DIR';
    const SHAREHOLDER = 'SH';
    const UBO = 'UBO';
    const POSITION = ['UBO', 'DIR', 'SIG', 'SH', 'SH_CORPORATE', 'DIR_CORPORATE'];

    const POSITION_LOOKUP_GROUP = 'BUSINESS_POSITION';

    protected $fillable = ['voting_share', 'start_date', 'end_date', 'business_id', 'model_type', 'person_responsible'];

    protected $hidden = ['created_at', 'updated_at', 'id'];

    function business()
    {
        return $this->belongsTo(Business::class);
    }

    function position()
    {
        return $this->morphMany(Lookup::class, 'lookuptable')
            ->whereRelation('lookupType', 'group', self::POSITION_LOOKUP_GROUP);
    }

    function person()
    {
        return $this->hasOne(BusinessCompositionable::class);
    }
}
