<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SepaDd extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sepa_dd';

    protected $fillable = [
        'sepa_dd_direct_debits',
        'name',
        'value',
        'description',
    ];

    protected $hidden = ['created_at', 'updated_at', 'id', 'sepa_dd_direct_debits'];

    function SepaDdDirectDebit()
    {
        return $this->belongsTo(SepaDdDirectDebit::class);
    }
}
