<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Document extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    const BUSINESS_TYPE = 'B';
    const NATURAL_PERSON_TYPE = 'P';
    const NON_NATURAL_PERSON_TYPE = 'N';

    const BUSINESS_FOLDER = 'businesses';
    const NATURAL_PERSON_FOLDER = 'natural_persons';
    const NON_NATURAL_PERSON_FOLDER = 'non_natural_persons';

    const DOCUMENT_TYPES = ['DRIVERS_LICENSE', 'ID_CARD', 'PASSPORT', 'RESIDENCE_PERMIT'];
    const OWNER_TYPES = [self::BUSINESS_TYPE, self::NATURAL_PERSON_TYPE, self::NON_NATURAL_PERSON_TYPE];

    protected $fillable = ['document_type', 'owner_type', 'file_name', 'file_type'];

    public function documentable()
    {
        return $this->morphTo();
    }
    
    public function kycpRequirement()
    {
        return $this->hasOne(KycpRequirement::class);
    }

    public function getFileName(): string | null
    {
        $folderName = null;

        if ($this->owner_type === self::BUSINESS_TYPE) {
            $folderName = self::BUSINESS_FOLDER;
        }

        if ($this->owner_type === self::NATURAL_PERSON_TYPE) {
            $folderName = self::NATURAL_PERSON_FOLDER;
        }

        if ($this->owner_type === self::NON_NATURAL_PERSON_TYPE) {
            $folderName = self::NON_NATURAL_PERSON_FOLDER;
        }

        if (! $folderName) {
            return null;
        }

        return "{$folderName}/{$this->file_name}";
    }
}
