<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Http\Request;
use App\Models\BusinessComposition;
use App\Models\Business;

class ValidBusinessComposition implements DataAwareRule, InvokableRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    protected $data = [];

    public function setData($data)
    {
        $this->data = $data;
    }

    public function __invoke($attribute, $value, $fail)
    {
        $businessId = $this->data['business_id'];
        $status = BusinessComposition::where('business_id', $businessId)->first()?->business()?->value('status');

        if ($status === Business::STATUS_SUBMITTED) {
            $fail('Unable to create nor update the business. The business is already submitted.');
        }
    }
}
