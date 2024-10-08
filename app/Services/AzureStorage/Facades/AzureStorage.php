<?php

namespace App\Services\AzureStorage\Facades;

use App\Services\AzureStorage\Client\Factory;
use Illuminate\Support\Facades\Facade;

class AzureStorage extends Facade
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
