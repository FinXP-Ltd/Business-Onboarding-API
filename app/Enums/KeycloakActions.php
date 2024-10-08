<?php

namespace App\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

enum KeycloakActions: string
{
    use InvokableCases, Values;

    case ACTIONS_OTP = 'CONFIGURE_TOTP';
    case ACTIONS_VERIFY_EMAIL = 'VERIFY_EMAIL';
    case ACTIONS_UPDATE_PASSWORD = 'UPDATE_PASSWORD';
    case ACTIONS_UPDATE_PROFILE = 'UPDATE_PROFILE';
}
