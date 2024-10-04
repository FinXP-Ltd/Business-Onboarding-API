<?php

namespace App\Services\Business\Facades;

use App\Services\Business\Client\Factory;
use Illuminate\Support\Facades\Facade;

class Business extends Facade
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
