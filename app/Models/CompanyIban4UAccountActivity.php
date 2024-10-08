<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CompanyIban4UAccountActivity extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'company_iban4u_accounts_activities';

    protected $fillable = [
        'index',
        'company_iban4u_account_id',
        'type',
        'name',
        'country',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'id',
        'company_iban4u_account_id'
    ];
}
