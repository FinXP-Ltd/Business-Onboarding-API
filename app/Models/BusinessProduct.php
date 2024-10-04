<?php

namespace App\Models;

use App\Traits\Scopes\HasBusinessScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
class BusinessProduct extends Model
{
    use HasFactory, HasUuids, HasBusinessScope;

    protected $table = 'business_products';

    protected $fillable = [
        'business_id',
        'product_name',
        'is_selected'
    ];

    protected $hidden = ['created_at', 'updated_at', 'id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_selected' => 'boolean'
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
