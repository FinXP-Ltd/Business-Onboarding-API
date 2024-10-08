<?php

namespace App\Models;

use App\Traits\Scopes\HasBusinessScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PoliticalPersonEntity extends Model
{
    use HasFactory, HasUuids, HasBusinessScope;

    protected $table = 'political_person_entity';

    protected $fillable = [
        'business_id',
        'entity_name',
        'is_selected'
    ];

    protected $casts = [
        'is_selected' => 'boolean'
    ];

    protected $hidden = ['created_at', 'updated_at', 'id'];

    function business()
    {
        return $this->belongsTo(Business::class);
    }
}
