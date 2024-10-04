<?php

namespace App\Http\Requests;

use App\Models\Auth\User;
use App\Traits\QueryParamsValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use App\Services\LocalUser\Facades\LocalUser;

class InviteRequest extends FormRequest
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

        $this->merge([
            'parent_id' => $userId,
            'head_parent_id' => $headParent?->id ?? $userId
        ]);
    }

    public function rules()
    {
        return [
            'first_name' => 'required|regex:/^[a-zA-Z\s]+$/u|string',
            'last_name' => 'required|regex:/^[a-zA-Z\s]+$/u|string',
            'email' => 'required|email',
        ];
    }
}
