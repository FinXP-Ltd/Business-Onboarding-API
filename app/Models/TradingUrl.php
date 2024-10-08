<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradingUrl extends Model
{
    use HasFactory;

    protected $table = 'cc_processing_trading_urls';

    protected $fillable = ['trading_urls'];

    protected $hidden = ['created_at', 'updated_at', 'id', 'credit_card_processing_id'];
}
