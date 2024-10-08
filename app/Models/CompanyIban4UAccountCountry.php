<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CompanyIban4UAccountCountry extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'company_iban4u_accounts_deposit_withdraw_countries';

    protected $fillable = [
        'company_iban4u_account_id',
        'type',
        'country',
        'is_selected',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_selected' => 'boolean'
    ];
}
