<?php

namespace App\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

enum Auth0CacheKeys: string
{
    use InvokableCases, Values;

    case USER_PREFIX = 'AUTH0_USER_';
}
