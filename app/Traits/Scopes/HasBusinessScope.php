<?php

namespace App\Traits\Scopes;

use App\Models\Scopes\BusinessScope;

trait HasBusinessScope
{
    public static function bootHasOwnerScope()
    {
        static::addGlobalScope(new BusinessScope());
    }
}
