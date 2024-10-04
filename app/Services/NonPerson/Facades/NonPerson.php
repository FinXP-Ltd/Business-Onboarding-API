<?php

namespace App\Services\NonPerson\Facades;

use App\Services\NonPerson\Client\Factory;
use Illuminate\Support\Facades\Facade;

class NonPerson extends Facade
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
