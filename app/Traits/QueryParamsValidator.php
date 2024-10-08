<?php

namespace App\Traits;

trait QueryParamsValidator
{
    abstract public function rules();

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $allowedParams = array_keys($this->rules());

            foreach ($this->input() as $key => $value) {
                if (!in_array($key, $allowedParams)) {
                    $validator->errors()->add($key, "The $key is not a valid key.");
                }
            }
        });
    }
}
