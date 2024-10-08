<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Iban4uPaymentOrders extends Model
{
    use HasUuids;

    protected $table = 'iban4u_payment_orders';

    protected $fillable = [
        'name',
        'country',
        'type'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'id',
        'iban4u_payment_accounts_id',
    ];
}