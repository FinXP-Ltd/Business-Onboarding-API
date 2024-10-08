<?php

namespace App\Models\Auth;

use App\Models\ShareInvites;
use App\Traits\Support\Sortable;
use App\Traits\Support\SortHeaders;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Laravel\Sanctum\HasApiTokens;
use Database\Factories\Auth\UserFactory;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids, HasRoles, UuidTrait, Sortable, SortHeaders, Filterable;

    const ROLE_OPERATIONS = 'operation';

    const ROLE_AGENT = 'agent';

    const ROLE_CLIENT = 'client';

    const ROLE_INVITED_CLIENT = 'invited_client';

    protected $guard_name = 'auth0-api';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'auth0',
        'program_id',
        'password',
        'mobile',
        'telephone',
        'tfa_secret',
        'tfa_enabled',
        'is_active',
        'last_ip',
        'last_logged_in',
        'password_updated',
        'email_verified_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['created_at', 'updated_at', 'id'];

    /**
     * Sortable headers
     *
     */
    protected static $sortedColumns = [
        'id' => 'id',
        'created_at' => 'created_at'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return UserFactory::new();
    }

    public function sharedInvitation(): BelongsTo
    {
        return $this->belongsTo(ShareInvites::class, 'id', 'client_id');
    }

    public function agentCompanies(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\AgentCompany::class,
            'user_has_agent_companies',
            'user_id',
            'agent_company_id',
        );
    }

    public function hasCompany(): bool
    {
        return $this->agentCompanies()->exists();
    }
}
