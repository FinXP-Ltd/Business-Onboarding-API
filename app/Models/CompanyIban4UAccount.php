<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CompanyIban4UAccount extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'company_iban4u_accounts';

    protected $fillable = [
        'company_information_id',
        'annual_turnover',
        'purpose_of_account_opening',

        'deposit_type',
        'deposit_approximate_per_month',
        'deposit_cumulative_per_month',

        'withdrawal_type',
        'withdrawal_approximate_per_month',
        'withdrawal_cumulative_per_month',

        'held_accounts',
        'held_accounts_description',
        'refused_banking_relationship',
        'refused_banking_relationship_description',
        'terminated_banking_relationship',
        'terminated_banking_relationship_description'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'id',
    ];

    function countries()
    {
        return $this->hasMany(CompanyIban4UAccountCountry::class, 'company_iban4u_account_id', 'id');
    }

    function activities()
    {
        return $this->hasMany(CompanyIban4UAccountActivity::class, 'company_iban4u_account_id', 'id');
    }
}
