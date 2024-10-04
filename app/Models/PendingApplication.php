<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PendingApplication extends Model
{
    use HasUuids;

    protected $table = 'pending_applications';
    protected $fillable = [
        'products',
        'company_products',
        'company_address',
        'company_details',
        'company_sources',
        'sepa_direct_debit',
        'iban4u_payment_account',
        'acquiring_services',
        'company_representatives',
        'data_protection_marketing',
        'declaration',
        'required_documents',
        'company_information_id',
        'company_name',
        'company_trading_as',
        'entities',
        'indicias',
        'status'
    ];
    protected $hidden = ['created_at', 'updated_at', 'company_information_id', 'id'];
}
