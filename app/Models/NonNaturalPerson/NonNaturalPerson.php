<?php

namespace App\Models\NonNaturalPerson;

use App\Models\BusinessCompositionable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Document;
use App\Models\NonNaturalPerson\NonNaturalPersonAddresses;
use App\Traits\Scopes\HasOwnerScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class NonNaturalPerson extends Model
{
    use HasFactory, HasUuids, HasOwnerScope;

    protected $table = 'non_natural_persons';

    protected $fillable = [
        'name',
        'registration_number',
        'date_of_incorporation',
        'country_of_incorporation',
        'name_of_shareholder_percent_held',
        'user_id'
    ];

    public function businessComposition()
    {
        return $this->morphOne(BusinessCompositionable::class, 'business_compositionable');
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function addresses()
    {
        return $this->hasMany(
            NonNaturalPersonAddresses::class,
            'non_natural_person_id'
        );
    }
}
