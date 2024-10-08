<?php

namespace App\Rules;

use Illuminate\Support\Str;
use Illuminate\Contracts\Validation\Rule;

class SecureFileName implements Rule
{
    protected $prohibitedWords = [
        'write',
        'sleep',
        'insert',
        'update',
        'delete',
        'select',
        'drop',
        'truncate'
    ];

    public function passes($attribute, $value)
    {
        $characters = config('apply.prohibited_characters');

        foreach ($this->prohibitedWords as $word) {
            if (stripos($value, $word) !== false) {
                return false;
            }
        }

        return !Str::contains($value, str_split($characters));
    }

    public function message()
    {
        return 'The :attribute contains prohibited words or special characters.';
    }
}
