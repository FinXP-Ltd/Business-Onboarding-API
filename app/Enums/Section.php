<?php

namespace App\Enums;

use ArchTech\Enums\Values;

enum Section: string
{
    use Values;

    case COMPANY_PRODUCTS = 'company-products';
    case COMPANY_DETAILS = 'company-details';
    case COMPANY_ADDRESS = 'company-address';
    case COMPANY_SOURCES = 'company-sources';
    case COMPANY_SEPA_DD = 'company-sepa-dd';
    case IBAN4U_PAYMENT_ACCOUNT = 'iban4u-payment-account';
    case ACQUIRING_SERVICES = 'acquiring-services';
    case COMPANY_REPRESENTATIVES = 'company_representatives';
    case DATA_PROTECTION_AND_MARKETING = 'data-protection-and-marketing';
    case DECLARATION = 'declaration';
    case REQUIRED_DOCUMENTS = 'required_documents'; // Do not change the first author used underscore across the app
    case SUBMIT_APPLICATION = 'submit-application';
}
