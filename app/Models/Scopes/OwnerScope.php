<?php

namespace App\Models\Scopes;

use App\Models\Auth\User;
use App\Models\NonNaturalPerson\NonNaturalPerson;
use App\Models\Person\NaturalPerson;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class OwnerScope implements Scope
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
            $user = User::whereAuth0($id)->first();

            if($user){
                switch (get_class($model)) {
                    case NonNaturalPerson::class:
                        $builder->whereUserId($user->id);
                        break;

                    case NaturalPerson::class:
                        $builder->whereUserId($user->id);
                            break;

                    default:
                        $builder->whereUser($user->id);
                        break;
                }
            }
        }
    }
}
