<?php

namespace App\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

enum UserRole: string
{
    use InvokableCases, Values;

    case OPERATION = 'operation';
    case AGENT = 'agent';
    case CLIENT = 'client';
    case INVITED_CLIENT = 'invited client';
    case BETTER_PAYMENT = 'better payment';
}
