<?php

namespace App\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

enum PoliticalPersonEntities: string
{
    use InvokableCases, Values;

    case NO_ACTIVE = 'No Active and Relevant PEP Involvement';
    case HAS_ACTIVE = 'Has active and relevant PEP involvement';
    case HOLDING_PROMINENT_PUBLIC_FUNCTION = 'Holding a prominent public function';
    case IMMEDIATE_FAMILY_MEMBER = 'Immediate family member of PEP';

    case CLOSE_ASSOCIATE_OF_PEP = 'Close associate of PEP';
}
