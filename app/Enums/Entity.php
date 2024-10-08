<?php

namespace App\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Names;
use ArchTech\Enums\Values;

enum Entity: string
{
    use Values;
    use Names;
    use InvokableCases;

    case BUSINESS = 'B';
    case NATURAL_PERSON = 'P';
    case NON_NATURAL_PERSON = 'N';

    public static function resource($value): string
    {
        if ($value == 'P') {
            return 'App\Http\Resources\Persons\NaturalResource';
        }

        return 'App\Http\Resources\Persons\NonNaturalResource';
    }
}
