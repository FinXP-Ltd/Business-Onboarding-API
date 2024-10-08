<?php

namespace App\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Names;

enum Position
{
    use Names;
    use InvokableCases;

    case UBO;
    case DIRECTOR;
    case SHAREHOLDER;
    case SIGNATORY;
}