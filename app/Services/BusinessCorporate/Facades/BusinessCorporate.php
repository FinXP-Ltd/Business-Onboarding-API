<?php

namespace App\Services\BusinessCorporate\Facades;

use App\Services\BusinessCorporate\Client\Factory;
use Illuminate\Support\Facades\Facade;

class BusinessCorporate extends Facade
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
