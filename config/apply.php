<?php

return [

    'declaration_url' => env('CORPORATE_DECLARATION_URL', 'https://pweresources.blob.core.windows.net/document/declaration.pdf'),
    'download_documents' => [
        'iban4u_payment_account_documents' => [
            'third_party_questionnaire' => env('CORPORATE_IBAN4U_QUESTIONNAIRE_URL', 'https://pweresources.blob.core.windows.net/document/Customer_Third_Party_Payments_Questionnaire_Update.pdf'),
            'board_resolution' => env('CORPORATE_IBAN4U_RESOLUTION_URL', 'https://pweresources.blob.core.windows.net/document/IBAN4U_Board_Resolution_updated.pdf'),
        ],
        'credit_card_processing_documents' => [
            'company_pci_certificate' => env('CORPORATE_CC_PCI_URL', 'https://pweresources.blob.core.windows.net/document/PCI-DSS-v4-0-SAQ-A_Template.pdf'),
        ],
        'company_representative_document' => [
            'sow_document_declaration' => env('CORPORATE_SOW_DECLARATION_URL', 'https://pweresources.blob.core.windows.net/document/SOW_declaration_updated.pdf')
        ]
    ],
    'prohibited_characters' => env('CORPORATE_PROHIBITED_CHARACTERS' , "#%&{}\<>*?/$!'\":@+|="),

    'file_extensions' => env('CORPORATE_FILE_EXTENTIONS' , "PDF,JPG,JPEG,PNG,pdf,jpg,jpeg,png,xlsx,docx")
];
