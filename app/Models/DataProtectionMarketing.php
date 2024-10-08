<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Business;
use App\Traits\Scopes\HasBusinessScope;

class DataProtectionMarketing extends Model
{
    use HasFactory, HasBusinessScope;

    protected $fillable = [
        'company_information_id',
        'data_protection_notice',
        'receive_messages_from_finxp',
        'receive_market_research_survey'
    ];

    protected $hidden = ['created_at', 'updated_at', 'id', 'company_information_id'];

    function business()
    {
        return $this->belongsTo(Business::class);
    }
}
