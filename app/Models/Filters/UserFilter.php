<?php

namespace App\Models\Filters;

use EloquentFilter\ModelFilter;

class UserFilter extends ModelFilter
{
    public function firstName(string $firstName)
    {
        return $this->whereRaw('LOWER(first_name) LIKE ?', ['%' . strtoupper($firstName) . '%']);
    }

    public function lastName(string $lastName)
    {
        return $this->whereRaw('LOWER(last_name) LIKE ?', ['%' . strtoupper($lastName) . '%']);
    }

    public function email(string $email)
    {
        return $this->whereRaw('LOWER(email) LIKE ?', ['%' . strtoupper($email) . '%']);
    }

    public function role(string $role)
    {
        return $this->whereHas('roles', function ($query) use ($role) {
            $query->whereName($role);
        });
    }

    public function isActive($isActive)
    {
        return $this->whereIsActive($isActive);
    }
}
