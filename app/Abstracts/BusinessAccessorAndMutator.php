<?php

namespace App\Abstracts;

use App\Models\Business;
use App\Services\AzureStorage\Facades\AzureStorage;
use Illuminate\Http\Response;

abstract class BusinessAccessorAndMutator
{
    protected Business $business;

    public function setBusiness(Business $business): self
    {
        $this->business = $business;
        return $this;
    }

    protected function getBusiness(): Business
    {
        return $this->business;
    }
}
