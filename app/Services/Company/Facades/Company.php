<?php

namespace App\Services\Company\Facades;

use App\Services\Company\Client\Factory;
use Illuminate\Support\Facades\Facade;

class Company extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Factory::class;
    }
}
