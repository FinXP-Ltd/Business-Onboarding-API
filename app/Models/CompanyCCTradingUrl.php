<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyCCTradingUrl extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'company_cc_trading_url';

    protected $fillable = ['trading_urls'];

    protected $hidden = ['created_at', 'updated_at', 'id', 'company_credit_card_processing_id'];
}
