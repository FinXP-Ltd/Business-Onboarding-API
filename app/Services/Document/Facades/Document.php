<?php

namespace App\Services\Document\Facades;

use App\Services\Document\Client\Factory;
use Illuminate\Support\Facades\Facade;

class Document extends Facade
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
