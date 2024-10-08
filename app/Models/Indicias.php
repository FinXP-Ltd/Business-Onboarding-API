<?php

namespace App\Models;

use App\Traits\Scopes\HasBusinessScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Indicias extends Model
{
    use HasFactory, HasUuids, HasBusinessScope;

    protected $table = 'indicias';

    protected $fillable = [
        'business_id',
        'indicia_name',
        'is_selected'
    ];

    protected $casts = [
        'is_selected' => 'boolean'
    ];

    protected $hidden = ['created_at', 'updated_at', 'id'];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
