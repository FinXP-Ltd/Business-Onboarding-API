<?php

namespace App\Models\Scopes;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BusinessScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $clientType = getClientType();

        if ($clientType !== 'app') {
            $id = auth()->id();
            $user = User::whereAuth0($id)->firstOrFail();

            $builder->whereHas('business', function ($query) use ($user) {
                $query->whereUser($user->id);
            });
        }
    }
}
