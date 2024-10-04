<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Models\Auth\User;
use App\Services\LocalUser\Facades\LocalUser;
use App\Traits\QueryParamsValidator;
use Illuminate\Foundation\Http\FormRequest;

class ShareApplicationRequest extends FormRequest
{
    use QueryParamsValidator;

    public function __construct(private User $user) {}

    protected function prepareForValidation()
    {
        $this->user = LocalUser::createOrFetchLocalUser();
    }

    protected function passedValidation()
    {
        $userId = $this->user->id;

            $sharedInvitation = $this->user->sharedInvitation()->first();

            $headParent = $sharedInvitation ? $sharedInvitation->headParent()->first() : null;

            $headParentId = null;

            if (!$this->user->hasRole(UserRole::OPERATION())) {
                $headParentId = $headParent?->id ?? null;
            }

            $this->merge([
                'parent_id' => $userId,
                'head_parent_id' => $headParentId,
            ]);
    }

    public function rules()
    {
        return [
            'first_name' => 'required|regex:/^[a-zA-Z\s]+$/u|string',
            'last_name' => 'required|regex:/^[a-zA-Z\s]+$/u|string',
            'email' => 'required|email',
            'business_id' => 'required|uuid',
        ];
    }
}
