<?php

namespace App\Models;

use App\Traits\Scopes\HasBusinessScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class UsaTaxLiability extends Model
{
    use HasFactory, HasUuids, HasBusinessScope;

    protected $table = 'usa_tax_liability';

    protected $fillable = [
        'company_information_id',
        'tax_name',
    ];

    protected $hidden = ['created_at', 'updated_at', 'id'];

    function business()
    {
        return $this->belongsTo(Business::class);
    }
}
