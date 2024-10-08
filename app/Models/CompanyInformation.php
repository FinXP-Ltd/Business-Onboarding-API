<?php

namespace App\Models;

use App\Traits\Scopes\HasBusinessScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CompanyInformation extends Model
{
    use HasFactory, HasUuids, HasBusinessScope;

    protected $table = 'company_information';

    protected $fillable = [
        'business_id',
        'company_name',
        'registration_number',
        'type_of_company',
        'company_trading_as',
        'date_of_incorporation',
        'country_of_incorporation',
        'number_of_employees',
        'number_of_years',
        'vat_number',
        'tin',
        'tin_jurisdiction',
        'industry_type',
        'business_activity_description',
        'industry_description',
        'share_capital',
        'previous_year_turnover',
        'email',
        'website',
        'additional_website',
        'is_group_corporate',
        'parent_holding_company',
        'parent_holding_company_other',
        'company_fiduciary_capacity',
        'allow_constituting_documents',
        'is_company_licensed',
        'country_of_incorporation',
        'licensed_in',
        'full_name',
        'email_address',
        'source_of_funds'
    ];

    protected $hidden = ['created_at', 'updated_at', 'id'];

    function business()
    {
        return $this->belongsTo(Business::class);
    }

    function companyAddress()
    {
        return $this->hasOne(CompanyAddress::class);
    }

    function companySource()
    {
        return $this->hasMany(CompanySource::class);
    }

    function companySourceCountry()
    {
        return $this->hasMany(CompanySourceCountry::class);
    }

    function companySepaDd()
    {
        return $this->hasOne(CompanySepaDd::class);
    }

    function companyIban4u()
    {
        return $this->hasOne(CompanyIban4UAccount::class);
    }

    function companyCreditCardProcessing()
    {
        return $this->hasOne(CompanyCreditCardProcessing::class);
    }

    function companyRepresentative()
    {
        return $this->hasMany(CompanyRepresentative::class);
    }

    public function seniorManagementOfficer()
    {
        return $this->hasOne(SeniorManagementOfficer::class);
    }

    function companyDataProtectionMarketing()
    {
        return $this->hasOne(DataProtectionMarketing::class);
    }

    function companyDeclaration()
    {
        return $this->hasOne(Declaration::class);
    }

    public function generalDocuments()
    {
        return $this->hasMany(\App\Models\Documents\GeneralDocuments::class, 'company_information_id');
    }

    public function additionalDocuments()
    {
        return $this->hasMany(\App\Models\Documents\AdditionalDocuments::class, 'company_information_id');
    }

    public function iban4uPaymentAccountDocuments()
    {
        return $this->hasMany(\App\Models\Documents\IBAN4UPaymentAccountDocuments::class, 'company_information_id');
    }

    public function creditCardProcessingDocuments()
    {
        return $this->hasMany(\App\Models\Documents\CreditCardProcessingDocuments::class, 'company_information_id');
    }

    public function sepaDirectDebitDocuments()
    {
        return $this->hasMany(\App\Models\Documents\SepaDirectDebitDocuments::class, 'company_information_id');
    }

    public function usaTaxLiability()
    {
        return $this->hasOne(UsaTaxLiability::class);
    }

    public function pendingApplication()
    {
        return $this->hasOne(PendingApplication::class);
    }
}
