<?php

namespace App\Services\BusinessCorporate\Facades;

use App\Services\BusinessCorporate\Client\DocumentFactory;
use Illuminate\Support\Facades\Facade;

class BusinessCorporateDocument extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return DocumentFactory::class;
    }
}
