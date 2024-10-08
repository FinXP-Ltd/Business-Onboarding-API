<?php

namespace App\Models;

use App\Traits\Scopes\HasBusinessScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SepaDdDirectDebit extends Model
{
    use HasFactory, HasUuids, HasBusinessScope;

    protected $table = 'sepa_dd_direct_debits';
    protected $fillable = [
        'currently_processing_sepa_dd',
        'sepa_dds',
        'sepa_dd_volume_per_month',
        'ac_sepa_dd_volume_per_month'
    ];

    protected $hidden = ['created_at', 'updated_at', 'id', 'business_id'];

    function sepaDds() {
        return $this->hasMany(SepaDd::class);
    }

    function business()
    {
        return $this->belongsTo(Business::class);

    }
}
