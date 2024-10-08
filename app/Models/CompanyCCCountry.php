<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyCCCountry extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'company_cc_country';

    protected $fillable = ['index', 'order', 'countries_where_product_offered', 'distribution_per_country'];

    protected $hidden = ['created_at', 'updated_at', 'id', 'company_credit_card_processing_id'];
}
