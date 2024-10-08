<?php

namespace App\Models;

use App\Traits\Scopes\HasBusinessScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CompanyRepresentative extends Model
{
    use HasFactory, HasUuids, HasBusinessScope;

    protected $table = 'company_representative';
    protected $fillable = [
        'company_information_id',
        'index',
        'order',
        'first_name',
        'middle_name',
        'surname',
        'place_of_birth',
        'date_of_birth',
        'nationality',
        'citizenship',
        'email_address',
        'phone_code',
        'phone_number'
    ];

    const ROLES = [
        'Authorised Signatory',
        'Director',
        'Partner',
        'UBO',
        'Shareholder',
        'Senior Manager Officer'
    ];

    protected $hidden = ['created_at', 'updated_at', 'id'];

    public function residentialAddress()
    {
        return $this->hasOne(ResidentialAddress::class);
    }

    public function identityInformation()
    {
        return $this->hasOne(IdentityInformation::class);
    }

    public function companyRepresentativeDocument()
    {
        return $this->hasOne(CompanyRepresentativeDocuments::class);
    }

    public function rolesPercentOwnership()
    {
        return $this->hasMany(RolesPercentOwnership::class);
    }

    function business()
    {
        return $this->belongsTo(Business::class);
    }
}
