<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CompanySepaDd extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'company_sepa_dd';
    protected $fillable = [
        'company_information_id',
        'processing_sepa_dd',
        'expected_global_mon_vol'
    ];

    protected $hidden = ['created_at', 'updated_at', 'company_information_id'];

    function sepaProducts()
    {
        return $this->hasMany(CompanySepaDdProduct::class);
    }

}
