<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;

class ValidPaymentConfirmation implements InvokableRule
{
    const CONFIRM = ['YES', 'NO'];

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail)
    {
        if (! in_array($value, self::CONFIRM)) {
            $length = strlen($value);
            $min = 2;
            $max = 7;

            if ($length < $min) {
                $fail('validation.min.string')->translate(['min' => $min]);
            }

            if ($length > $max) {
                $fail('validation.max.string')->translate(['max' => $max]);
            }
        }
    }
}
