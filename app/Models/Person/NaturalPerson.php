<?php

namespace App\Models\Person;

use App\Models\BusinessCompositionable;
use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Person\AdditionalInfo;
use App\Models\Person\NaturalPersonAddresses;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\Person\NaturalPersonIdentificationDocument;
use App\Traits\HasEncryptedFields;
use App\Traits\Scopes\HasOwnerScope;

class NaturalPerson extends Model
{
    use HasFactory, HasEncryptedFields, HasUuids, HasOwnerScope;

    protected $table = 'natural_persons';

    protected $fillable = [
        'title',
        'name',
        'surname',
        'sex',
        'date_of_birth',
        'place_of_birth',
        'email_address',
        'country_code',
        'mobile',
        'user_id'
    ];

    const PERSON_TITLE = ['MRS','MS','MISS','MR'];
    const GENDER = ['MALE','FEMALE', 'OTHER', 'PREFER NOT TO TELL'];

    protected $encryptable = [
        'name',
        'date_of_birth',
        'email_address',
        'mobile',
    ];

    public function addresses()
    {
        return $this->hasOne(NaturalPersonAddresses::class, 'natural_person_id');
    }

    public function identificationDocument()
    {
        return $this->hasOne(NaturalPersonIdentificationDocument::class, 'natural_person_id');
    }

    public function additionalInfos()
    {
        return $this->hasOne(AdditionalInfo::class, 'natural_person_id');
    }

    public function businessComposition()
    {
        return $this->morphOne(BusinessCompositionable::class, 'business_compositionable');
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
