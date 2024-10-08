<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidFilter implements Rule
{
    protected $allowedKeys;

    public function __construct($validFilters = [])
    {
        $this->allowedKeys = $validFilters; // Define your allowed keys here
    }

    public function passes($attribute, $value)
    {
        // Ensure $value is an array
        if (!is_array($value)) {
            return false;
        }

        // Check if all keys in the $value array are allowed
        foreach ($value as $key => $val) {
            if (!in_array($key, $this->allowedKeys)) {
                return false;
            }
        }

        return true;
    }

    public function message()
    {
        return 'The :attribute contains invalid keys.';
    }
}
