<?php

return [
    'base_url' => env('KYCP_BASE_URL'),
    'credentials' => [
        'username' => env('KYCP_CREDENTIAL_USERNAME'),
        'password' => env('KYCP_CREDENTIAL_PASSWORD'),
    ],
    'program_id' => [
        'customers' => env('KYCP_PROGRAMME_CUSTOMER', 1),
        'better_payment' => env('KYCP_PROGRAMME_BETTER_PAYMENT', 11),
        'zbx' => env('KYCP_PROGRAMME_ZAZOO', 14),
        'iban4u_applications' => 6,
        'payment_processing' => 7,
    ],
    'field_model' => [
        'bp' => 'App\Models\COT\Field',
        'zbx' => 'App\Models\Zazoo\Field',
        'app' => 'App\Models\BusinessCorporate\Field'
    ],
    'resources_mapping' => [
        'bp' => 'constants/bp-kycp.php',
        'zbx' => 'constants/zazoo-kycp.php',
    ],
    'keycloak_role' => [
        'customers' => env('KYCP_PROGRAMME_CUSTOMER_ROLE', 'customers'),
        'better_payment' => env('KYCP_PROGRAMME_BETTER_PAYMENT_ROLE', 'better_payment')
    ]
];
