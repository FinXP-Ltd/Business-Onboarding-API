<?php

namespace App\Models;

use App\Traits\Scopes\HasBusinessScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessDetail extends Model
{
    use HasFactory, HasBusinessScope;

    protected $fillable = [
        'business_purpose',
        'number_employees',
        'number_of_years',
        'share_capital',
        'number_shareholder',
        'number_directors',
        'previous_year_turnover',
        'license_rep_juris',
        'business_year_count',
        'terms_and_conditions',
        'privacy_accepted',
        'description',
        'is_part_of_group',
        'parent_holding_company',
        'parent_holding_company_other',
        'has_fiduciary_capacity',
        'has_constituting_documents',
        'is_company_licensed',
        'contact_person_name',
        'contact_person_email',
    ];

    protected $guard = ['finxp_products'];

    protected $hidden = ['created_at', 'updated_at', 'id', 'business_id'];

    function business()
    {
        return $this->belongsTo(Business::class);
    }

    function finxpProducts()
    {
        return $this->morphMany(Lookup::class, 'lookuptable')
            ->whereRelation('lookupType', 'group', 'GENpweproduct');
    }

    function industryKey()
    {
        return $this->morphMany(Lookup::class, 'lookuptable')
            ->whereRelation('lookupType', 'group', 'GENindustry');
    }

    function countryOfLicense()
    {
        return $this->morphMany(Lookup::class, 'lookuptable')
            ->whereRelation('lookupType', 'group', 'GENcountryoflicense');
    }

    function countryJurisDealings()
    {
        return $this->morphMany(Lookup::class, 'lookuptable')
            ->whereRelation('lookupType', 'group', 'GENjurisdealing');
    }

    function sourceOfFunds()
    {
        return $this->morphMany(Lookup::class, 'lookuptable')
            ->whereRelation('lookupType', 'group', 'GENsowFund');
    }

    function sourceOfFundsOther()
    {
        return $this->morphMany(Lookup::class, 'lookuptable')
            ->whereRelation('lookupType', 'group', 'GENsowFundOther');
    }

    function countrySourceOfFunds()
    {
        return $this->morphMany(Lookup::class, 'lookuptable')
            ->whereRelation('lookupType', 'group', 'GENsowFundCountry');
    }

    function sourceOfWealth()
    {
        return $this->morphMany(Lookup::class, 'lookuptable')
            ->whereRelation('lookupType', 'group', 'GENsowWealth');
    }

    function sourceOfWealthOther()
    {
        return $this->morphMany(Lookup::class, 'lookuptable')
            ->whereRelation('lookupType', 'group', 'GENsowWealthOther');
    }
    function countrySourceOfWealth()
    {
        return $this->morphMany(Lookup::class, 'lookuptable')
            ->whereRelation('lookupType', 'group', 'GENsowWealthCountry');
    }


    function getLookupGroupFields($group)
    {
        return $this->morphMany(Lookup::class, 'lookuptable')
            ->whereRelation('lookupType', 'group', $group)->get();
    }

    function politicalPersonEntity()
    {
        return $this->morphMany(Lookup::class, 'lookuptable')
            ->whereRelation('lookupType', 'group', 'GENpoliticalPersonEntity');
    }

    function usaTaxLiability()
    {
        return $this->morphMany(Lookup::class, 'lookuptable')
            ->whereRelation('lookupType', 'group', 'GENusaTaxLiability');
    }

    function indicias()
    {
        return $this->morphMany(Lookup::class, 'lookuptable')
            ->whereRelation('lookupType', 'group', 'GENindicias');
    }
}
