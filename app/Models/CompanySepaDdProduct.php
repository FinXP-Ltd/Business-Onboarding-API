<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CompanySepaDdProduct extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'company_sepa_dd_products';

    protected $fillable = [
        'company_sepa_dd_id',
        'name',
        'value',
        'description'
    ];

    protected $hidden = ['created_at', 'updated_at', 'id', 'company_sepa_dd_id'];
}
