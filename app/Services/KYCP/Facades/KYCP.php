<?php

namespace App\Services\KYCP\Facades;

use App\Services\KYCP\Client\Factory;
use Illuminate\Support\Facades\Facade;

class KYCP extends Facade
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
