<?php

namespace App\Models;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\DataProtectionMarketing;
use App\Traits\Scopes\HasAgentCompanyScope;
use App\Traits\Scopes\HasOwnerScope;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Business extends Model
{
    use HasFactory, HasUuids, HasOwnerScope, HasAgentCompanyScope;

    protected $table = 'businesses';

    const REGISTRATION_TYPE = [
        'TRADING',
        'HOLDING',
        'PARTNERSHIP',
        'FOUNDATION',
        'CHARITIES',
        'TRUST',
        'PUBLIC',
        'LIMITED'
    ];

    const AC_REGISTRATION_TYPE = [
        'Trading',
        'Holding',
        'Partnership',
        'Foundation',
        'Is Foundation',
        'Charities/MPOs',
        'Is Charities/MPOs',
        'Trust',
        'Is Trust',
        'Public',
        'Is Public Listed Company',
        'Limited'
    ];

    const SOLE_TRADER = [
        'LIMITED'
    ];

    const LICENSE_REP_JURIS = [
        'YES',
        'NO',
        'LICENSE_NOT_REQUIRED'
    ];

    const PAYMENT_METHOD = [
        'PAYPAL',
        'SOFORT',
        'SKRILL',
        'GIRO',
        'RTP',
        'OTHER',
    ];

    const SOURCE_OF_FUNDS = [
        'SALE_OF_GOODS_AND_SERVICES',
        'TRANSFER_FROM_DIFFERENT_ACCOUNTS',
        'INCOME_FROM_CUSTOMERS',
        'CORPORATE_MERGERS_AND_ACQUISITIONS',
        'FINANCING',
        'SECURITIES_OR_OTHER_FINANCIAL_INSTRUMENTS',
        'TREASURY',
        'DONATIONS',
    ];
    const FREQUENCY = [
        'WEEKLY',
        'MONTHLY',
        'QUARTERLY',
        'YEARLY',
        'OTHER'
    ];

    const STATUS_OPENED = 'OPENED';
    const STATUS_INPUTTING = 'INPUTTING';
    const STATUS_SUBMITTED = 'SUBMITTED';
    const STATUS_WITHDRAWN = 'WITHDRAWN';
    const STATUS_DRAFT = 'DRAFT';
    const PRESUBMIT = 'PRESUBMIT';

    const STATUSES = [self::STATUS_OPENED, self::STATUS_SUBMITTED, self::STATUS_INPUTTING];

    const FINXP_PRODUCTS = [self::IBAN4U, self::CC_PROCESSING, self::SEPADD];
    const IBAN4U = 'IBAN4U Payment Account';
    const CC_PROCESSING = 'Credit Card Processing';
    const SEPADD = 'SEPA Direct Debit';

    protected $fillable = [
        'trading_name',
        'foundation_date',
        'vat_number',
        'telephone',
        'email',
        'website',
        'additional_website',
        'status',
        'uid',
        'application_id',
        'entity_id',
        'entity_type_id',
        'kycp_status_id',
        'user',
        'IBAN',
        'external_identifier'
    ];

    protected $hidden = ['created_at', 'updated_at', 'id'];

    protected function telephone(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => str_replace('+','',$value),
            set: fn ($value) => str_replace('+','',$value)
        );
    }

    public function taxInformation()
    {
        return $this->hasOne(TaxInformation::class);
    }

    public function companyInformation()
    {
        return $this->hasOne(CompanyInformation::class);
    }

    public function registeredAddress()
    {
        return $this->hasOne(BusinessAddress::class)
            ->whereRelation('addressType', 'type', 'REGISTERED_BUSINESS_ADDRESS');
    }

    public function operationalAddress()
    {
        return $this->hasOne(BusinessAddress::class)
            ->whereRelation('addressType', 'type', 'OPERATIONAL_BUSINESS_ADDRESS');
    }

    public function contactDetails()
    {
        return $this->hasOne(ContactDetail::class);
    }

    public function businessCompositions()
    {
        return $this->hasMany(BusinessComposition::class);
    }

    public function businessDetails()
    {
        return $this->hasOne(BusinessDetail::class);
    }

    public function iban4uPaymentAccount()
    {
        return $this->hasOne(IBAN4UPaymentAccount::class);
    }

    public function sepaDdDirectDebit()
    {
        return $this->hasOne(SepaDdDirectDebit::class);
    }

    public function creditCardProcessing()
    {
        return $this->hasOne(CreditCardProcessing::class);
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function dataProtectionMarketing()
    {
        return $this->hasOne(DataProtectionMarketing::class, 'company_information_id', 'id');
    }

    public function declaration()
    {
        return $this->hasOne(Declaration::class);
    }

    public function generalDocuments()
    {
        return $this->hasOne(GeneralDocuments::class);
    }

    public function iban4uPaymentAccountDocuments()
    {
        return $this->hasOne(IBAN4UPaymentAccountDocuments::class);
    }

    public function creditCardProcessingDocuments()
    {
        return $this->hasOne(CreditCardProcessingDocuments::class);
    }

    public function sepaDirectDebitDocuments()
    {
        return $this->hasOne(SepaDirectDebitDocuments::class);
    }

    public function companyRepresentative()
    {
        return $this->hasMany(CompanyRepresentative::class);
    }

    public function seniorManagementOfficer()
    {
        return $this->hasOne(SeniorManagementOfficer::class);
    }

    public function products()
    {
        return $this->hasMany(BusinessProduct::class);
    }

    public function politicalPersonEntity()
    {
        return $this->hasMany(PoliticalPersonEntity::class);
    }

    public function indicias()
    {
        return $this->hasMany(Indicias::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user', 'id');
    }

    public function sharedInvites()
    {
        return $this->belongsToMany(
            User::class,
            'client_invites',
            'business_id',
            'client_id'
        );
    }

    public function sharedApplication()
    {
        return $this->belongsToMany(
            User::class,
            'shared_applications',
            'business_id',
            'user_id',
        );
    }
}
