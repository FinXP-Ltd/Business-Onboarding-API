<?php

namespace App\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Options;

enum Status: int
{
    use Options;
    use InvokableCases;

    case INPUTTING = 1;
    case ONBOARDING = 2;
    case PREAPPROVAL = 4;
    case PENDING_FINAL_APPROVAL = 5;
    case PENDING_COMPLIANCE_CHECK = 6;
    case APPROVED_LIVE = 7;
    case DECLINE = 8;
    case COMPLIANCE_APPROVAL = 9;
    case COMPLIANCE_DECLINE = 10;
    case ADDITIONAL_INFO = 11;
    case TERMINATED = 13;
    case DORMANT_WITHDRAWN = 14;
    case REJECTED = 31;

    public function BPStatus(): string
    {
        switch ($this->value) {
            case 1:
                $status = 'SUBMITTED';
                break;
            case 2:
            case 4:
            case 5:
            case 6:
            case 11:
                $status = 'PENDING';
                break;
            case 7:
                $status = 'APPROVED';
                break;
            case 8:
            case 13:
            case 31:
                $status = 'REJECTED';
                break;
            case 14:
                $status = 'WITHDRAWN';
                break;
            default:
                $status = 'OPENED';
                break;
        }

        return $status;
    }
}
