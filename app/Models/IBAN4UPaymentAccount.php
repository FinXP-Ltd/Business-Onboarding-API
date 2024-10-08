<?php

namespace App\Models;

use App\Traits\Scopes\HasBusinessScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class IBAN4UPaymentAccount extends Model
{
    use HasFactory, HasUuids, HasBusinessScope;

    protected $table = 'iban4u_payment_accounts';

    protected $fillable = [
        'share_capital',
        'annual_turnover',

        'deposit_trading',
        'deposit_countries',
        'deposit_approximate_per_month',
        'deposit_cumulative_per_month',
        'withdrawal_trading',
        'withdrawal_countries',
        'withdrawal_approximate_per_month',
        'withdrawal_cumulative_per_month',

        'incoming_payments',
        'outgoing_payments',

        'held_accounts',
        'held_accounts_description',
        'refused_banking_relationship',
        'refused_banking_relationship_description',
        'terminated_banking_relationship',
        'terminated_banking_relationship_description',
        'purpose_of_account_opening',
        'partners_incoming_transactions',
        'estimated_monthly_transactions',
        'average_amount_transaction_euro',
        'accepting_third_party_funds',
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

    function countryOrigin()
    {
        return $this->morphMany(Lookup::class, 'lookuptable')
            ->whereRelation('lookupType', 'group', 'SPEcountryorigin');
    }

    function countryRemittance()
    {
        return $this->morphMany(Lookup::class, 'lookuptable')
            ->whereRelation('lookupType', 'group', 'SPEcountryremittance');
    }

    function depositCountries()
    {
        return $this->morphMany(Lookup::class, 'lookuptable')
            ->whereRelation('lookupType', 'group', 'deposit_countries');
    }

    function withdrawalCountries()
    {
        return $this->morphMany(Lookup::class, 'lookuptable')
            ->whereRelation('lookupType', 'group', 'withdrawal_countries');
    }

    function payments()
    {
        return $this->hasMany(Iban4uPaymentOrders::class, 'iban4u_payment_accounts_id');
    }

    function withdrawalTrading()
    {
        return $this->morphMany(Lookup::class, 'lookuptable')
            ->whereRelation('lookupType', 'group', 'withdrawal_trading');
    }

    function depositTrading()
    {
        return $this->morphMany(Lookup::class, 'lookuptable')
            ->whereRelation('lookupType', 'group', 'deposit_trading');
    }
}
