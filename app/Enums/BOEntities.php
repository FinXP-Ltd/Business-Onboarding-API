<?php

namespace App\Enums;

use ArchTech\Enums\Options;

enum BOEntities: int
{
    use Options;

    case DIR = 12;
    case SH = 9;
    case UBO = 10;
    case SIG = 14;

    public function type($type): string
    {
        if ($this->name == 'SH' && $type == 'N') {
            return 8;
        }

        return $this->value;
    }
}
