<?php

namespace App\Http\Resources;

use App\Enums\UserRole;
use App\Models\AgentCompany;
use App\Models\Auth\User;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $roleName = $this->roles()->first()?->name;

        $roles = [
            $this->getRoles($roleName)
        ];

        $hasCompany = in_array($roleName, [UserRole::AGENT(), UserRole::INVITED_CLIENT()]);

        $sharedInvitation = $this->sharedInvitation()->first();

        $parent = null;

        $agentCompany = null;

        if ($roleName === UserRole::AGENT()) {
            $agentCompany = $this->agentCompanies()->first();
        }

        if ($sharedInvitation && $hasCompany) {
            $parent = User::whereId($sharedInvitation->parent_id)->first();

            if ($sharedInvitation->headParent()->exists()) {
                $parentAgent = $sharedInvitation->headParent()->first();
                $agentCompany = $parentAgent->agentCompanies()->first();
            }
        }

        return [
            'id' => $this->id,
            'auth0' => $this->auth0,
            'first_name' => trim($this->first_name),
            'last_name' => trim($this->last_name),
            'email' => $this->email,
            'username' => $this->email,
            'roles' => $roles,
            'is_active' => $this->is_active,
            'blocked' => !$this->is_active,
            'company' => $this->when(
                $hasCompany,
                $agentCompany ? AgentCompanyResource::make($agentCompany) : null
            ),
            'agent' => $this->when(
                !is_null($parent) && $parent->hasRole(UserRole::AGENT()) && $roleName === UserRole::INVITED_CLIENT(),
                [
                    'first_name' => $parent?->first_name,
                    'last_name' => $parent?->last_name
                ]
            )
        ];
    }

    private function getRoles($roleName)
    {
        switch ($roleName) {
            case 'operation':
                $roles = [
                    'name' => 'operation',
                    'description' => 'OPS'
                ];
                break;
            case 'agent':
                $roles = [
                    'name' => 'agent',
                    'description' => 'Agent'
                ];
                break;
            case 'client':
                $roles = [
                    'name' => 'client',
                    'description' => 'Direct'
                ];
                break;
            case 'invited client':
                $roles = [
                    'name' => 'invited client',
                    'description' => 'ReferM'
                ];
                break;
            default:
                $roles = [];
        }

        return $roles;
    }
}
