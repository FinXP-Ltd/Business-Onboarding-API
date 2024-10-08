<?php

namespace App\Enums;
use ArchTech\Enums\Values;

enum Product: string
{
    use Values;

    case SEPA_DIRECT_DEBIT = 'SEPA Direct Debit';
    case CREDIT_CARD_PROCESSING = 'Credit Card Processing';
    case IBAN4U_PAYMENT_ACCOUNT = 'IBAN4U Payment Account';
}
