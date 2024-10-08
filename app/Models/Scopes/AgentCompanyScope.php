<?php

namespace App\Models\Scopes;

use App\Enums\UserRole;
use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class AgentCompanyScope implements Scope
{
    protected $userId = null;

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $authId = auth()->id();
        $user = User::whereAuth0($authId)->first();

        if ($authId && $user->hasRole(UserRole::AGENT())) {
            $this->userId = $user->id;

            $builder->whereHas('user', function (Builder $query) {
                $query->whereHas('agentCompanies', function (Builder $builder) {
                    $builder->whereHas('users', function ($query) {
                        $query->whereId($this->userId);
                    });
                })->orWhereHas('sharedInvitation', function (Builder $query) {
                    $query
                        ->whereHas('parent', function ($query) {
                            $query
                                ->whereParentId($this->userId)
                                ->orWhereHas('agentCompanies', function (Builder $builder) {
                                    $builder->whereHas('users', function ($query) {
                                        $query->whereId($this->userId);
                                    });
                            });
                        })->orWhereHas('headParent', function ($query) {
                            $query->whereHas('agentCompanies', function (Builder $builder) {
                                $builder->whereHas('users', function ($query) {
                                    $query->whereId($this->userId);
                                });
                            });
                        });
                });
            });
        }

        return null;
    }
}
