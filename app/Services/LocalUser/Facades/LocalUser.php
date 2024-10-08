<?php

namespace App\Services\LocalUser\Facades;

use App\Services\LocalUser\Client\Factory;
use Illuminate\Support\Facades\Facade;

class LocalUser extends Facade
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
