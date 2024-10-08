<?php

namespace App\Models;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SharedApplication extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'parent_id',
        'business_id',
    ];

    public function parent(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'parent_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function business(): HasOne
    {
        return $this->hasOne(Business::class, 'id', 'business_id');
    }
}
