<?php

namespace App\Models;

use Database\Factories\AgentCompanyFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AgentCompany extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'agent_companies';

    public $timestamps = false;

    protected $fillable = [
        'name'
    ];

    protected $hidden = ['id'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\Auth\User::class,
            'user_has_agent_companies',
            'agent_company_id',
            'user_id'
        );
    }

    protected static function newFactory()
    {
        return AgentCompanyFactory::new();
    }
}
