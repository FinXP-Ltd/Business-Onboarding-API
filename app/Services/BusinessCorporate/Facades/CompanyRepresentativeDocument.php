<?php

namespace App\Services\BusinessCorporate\Facades;

use App\Services\BusinessCorporate\Client\CompanyRepresentativeDocumentFactory;
use Illuminate\Support\Facades\Facade;

class CompanyRepresentativeDocument extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return CompanyRepresentativeDocumentFactory::class;
    }
}
