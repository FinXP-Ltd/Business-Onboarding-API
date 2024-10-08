<?php

namespace App\Traits\Scopes;

use App\Models\Scopes\OwnerScope;

trait HasOwnerScope
{
    public static function bootHasOwnerScope()
    {
        static::addGlobalScope(new OwnerScope());
    }
}
