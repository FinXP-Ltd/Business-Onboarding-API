<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Countries extends Model
{
    use HasFactory;
    protected $table = 'cc_processing_countries';
    protected $fillable = ['countries_where_product_offered', 'distribution_per_country', 'order'];

    protected $hidden = ['created_at', 'updated_at', 'id', 'credit_card_processing_id'];
}
