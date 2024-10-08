<?php

namespace App\Models;

use App\Traits\Scopes\HasBusinessScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditCardProcessing extends Model
{
    use HasFactory, HasBusinessScope;

    protected $fillable = [
        'currently_processing_cc_payments',
        'trading_urls',
        'offer_recurring_billing',
        'frequency_offer_billing',
        'if_other_offer_billing',
        'recurring_details',
        'offer_refunds',
        'frequency_offer_refunds',
        'if_other_offer_refunds',
        'refund_details',
        'country',
        'distribution_sale_volume',
        'processing_account_primary_currency',
        'average_ticket_amount',
        'ac_average_ticket_amount',
        'highest_ticket_amount',
        'ac_highest_ticket_amount',
        'ac_alternative_payment_methods',
        'ac_method_currently_offered',
        'other_alternative_payment_methods',
        'other_alternative_payment_method_used',
        'current_mcc',
        'ac_current_mcc',
        'current_descriptor',
        'ac_current_descriptor',
        'ac_cb_volumes_twelve_months',
        'cb_volumes_twelve_months',
        'ac_cc_volumes_twelve_months',
        'cc_volumes_twelve_months',
        'ac_refund_volumes_twelve_months',
        'refund_volumes_twelve_months',
        'current_acquire_psp',
        'ac_current_acquire_psp',
        'iban4u_processing'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'id',
        'business_id',
    ];

    function business()
    {
        return $this->belongsTo(Business::class);
    }

    function alternativePaymentMethods()
    {
        return $this->morphMany(Lookup::class, 'lookuptable')
            ->whereRelation('lookupType', 'group', 'AlternativePaymentMethods');
    }

    function methodCurrentlyOffered()
    {
        return $this->morphMany(Lookup::class, 'lookuptable')
            ->whereRelation('lookupType', 'group', 'MethodCurrentlyOffered');
    }

    function countries()
    {
        return $this->hasMany(Countries::class);
    }

    function tradingUrl()
    {
        return $this->hasMany(TradingUrl::class);
    }
}
