<?php

namespace App\Services\Person\Facades;

use App\Services\Person\Client\Factory;
use Illuminate\Support\Facades\Facade;

class Person extends Facade
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
