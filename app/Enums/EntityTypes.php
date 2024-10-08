<?php

namespace App\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Names;

enum EntityTypes
{
    use Names;
    use InvokableCases;

    case UBO;
    case DIR;
    case SIG;
    case SH;
    case TRADING;
    case HOLDING;
    case PARTNERSHIP;
    case FOUNDATION;
    case CHARITIES;
    case TRUST;
    case PUBLIC;
    case LIMITED;
}
