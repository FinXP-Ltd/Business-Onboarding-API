<?php

namespace App\Enums;

use ArchTech\Enums\Options;

enum KYCEntities: int
{
    use Options;

    case NATURAL_PERSON = 1;
    case COMPANY = 2;
    case SHAREHOLDER_CORPORATE = 8;
    case SHAREHOLDER = 9;
    case UBO = 10;
    case DIRECTOR_NATURAL_PERSON = 12;
    case DIRECTOR_CORPORATE = 11;
    case COMPANY_SECRETARY = 13;
    case ADMINISTRATOR_AUTHORISED_SIGNATORY = 14;

    public function word(): string
    {
        if ($this->value == 14) {
            return 'Administrator/Authorised Signatory';
        }

        return str($this->name)->replace('_', ' ')->title()->__toString();
    }

    public static function corporateEntities($position): int
    {
        switch ($position) {
            case 'UBO':
            case 'Senior Manager Officer':
                return 10;
            break;
            case 'Authorised Signatory':
                return 14;
            break;
            case 'Shareholder':
                return 9;
            break;
            case 'Partner':
                return 8;
            break;
            case 'Director':
                return 12;
            break;
            default:
                return 1;
            break;
        }
    }
}
