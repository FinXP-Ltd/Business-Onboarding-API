<?php

namespace App\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Options;

enum ClientType: string
{
    use InvokableCases;

    case BP = 'bp';
    case ZBX = 'zbx';

    case APP = 'app';
}
