<?php

namespace App\Rules;

use Illuminate\Support\Str;
use Illuminate\Contracts\Validation\Rule;

class ExtensionValidation implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {

        $allowedExtension = explode(',', config('apply.file_extensions'));
        $extension = $value->getClientOriginalExtension();

        return in_array($extension, $allowedExtension);
    }

    public function message()
    {
        return 'The :attribute extension is not allowed.';
    }
}
