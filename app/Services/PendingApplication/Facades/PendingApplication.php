<?php

namespace App\Services\PendingApplication\Facades;

use App\Services\PendingApplication\Client\Factory;
use Illuminate\Support\Facades\Facade;

class PendingApplication extends Facade
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
