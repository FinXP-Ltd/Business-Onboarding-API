<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidRequestKeys implements Rule
{
    protected $allowedKeys;

    protected $invalidKeys = [];

    public function __construct($validFilters = [])
    {
        $this->allowedKeys = $validFilters; // Define your allowed keys here
    }

    public function addInvalidKey(string $key): void
    {
        if (!in_array($key, $this->invalidKeys)) {
            $this->invalidKeys[] = $key;
        }
    }

    public function passes($attribute, $value)
    {
        // Ensure $value is an array
        if (!is_array($value)) {
            return false;
        }

        // Check if all keys in the $value array are allowed
        foreach ($value as $key => $val) {
            $key = preg_replace('/\[\d+\]/', '', $key);

            if (!in_array($key, $this->allowedKeys)) {
                $this->addInvalidKey($key);
                return false;
            }
        }

        return true;
    }

    public function message()
    {
        $invalidKeyList = implode(', ', $this->invalidKeys);
        return "The :attribute contains invalid keys ($invalidKeyList).";
    }
}
