<?php

namespace App\Traits\Scopes;

use App\Models\Scopes\AgentCompanyScope;

trait HasAgentCompanyScope
{
    public static function bootHasAgentCompanyScope()
    {
        static::addGlobalScope(new AgentCompanyScope());
    }
}
