<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyCreditCardProcessing extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'company_credit_card_processing';

    protected $fillable = [
        'company_information_id',
        'currently_processing_cc_payments',
        'offer_recurring_billing',
        'frequency_offer_billing',
        'if_other_offer_billing',
        'offer_refunds',
        'frequency_offer_refunds',
        'if_other_offer_refunds',
        'processing_account_primary_currency',
        'average_ticket_amount',
        'highest_ticket_amount',
        'alternative_payment_methods',
        'payment_method_currently_offered',
        'current_mcc',
        'current_descriptor',
        'cb_volumes_twelve_months',
        'sales_volumes_twelve_months',
        'refund_twelve_months',
        'current_acquire_psp',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'id'
    ];

    function companyCountries()
    {
        return $this->hasMany(CompanyCCCountry::class, 'company_credit_card_processing_id', 'id');
    }

    function companyTradingUrl()
    {
        return $this->hasMany(CompanyCCTradingUrl::class, 'company_credit_card_processing_id', 'id');
    }
}
