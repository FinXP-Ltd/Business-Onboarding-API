<?php

namespace App\Models;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ShareInvites extends Model
{
    use HasUuids;

    protected $table = 'client_invites';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'client_id',
        'parent_id',
        'head_parent_id',
    ];

    public function parent(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'parent_id');
    }

    public function headParent(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'head_parent_id');
    }
}
