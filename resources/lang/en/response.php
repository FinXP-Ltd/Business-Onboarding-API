<?php

return [
    'error' => [
        'unauthenticated' => 'Unauthenticated. Please sign in.',
        'general_not_found' => 'No results found!',
        'not_found' => ':entity does not exist.',
        'mapping_not_found' => 'mapping_id :mapping_id for owner_type ":owner_type" does not exist.',
        'mapping_already_exists' => 'model_type ":model_type" with mapping_id :mapping_id and position(s) :existing_positions already exists.',
        'mapping_not_required' => 'mapping id is not required for update composition if model type is the same.',
        'business_id_restriction' => 'It is not allowed to update the business id of other businesses.',
        'voting_share_restriction' => 'Please update the previous composition or create the lower one. The shareholding order must be highest to lowest.',
        'voting_share_update_restriction' => 'Shareholding percentage should be greater than or equal the next share. The shareholding order must be highest to lowest.',
        'draft_error' => 'Only withdrawn business can be set to draft.',
        'withdrawn_error' => 'Business is already been withdrawn.'
    ],
    'submit' => [
        'incomplete' =>  'Unable to submit business. Please complete the shareholding composition first.',
        'composition' => 'Unable to submit business. Please complete the list of compositions first. Add :no_of_directors Director/s and :no_of_shareholders Shareholder/s',
        'submitted' => 'Unable to submit business. The business is already submitted.',
        'documents' => 'Unable to submit business. There are compositions that still need required documents.',
        'presubmit' => 'Successfully Update Business Status'
    ],
    'draft' => [
        'draft_success' => 'Business has been set to draft.'
    ],
    'withdrawn' => [
        'withdrawn_success' => 'Business has been withdrawn.'
    ],
    'exceed' => 'Total shares will exceed 100%',
    'success' => 'Successfully :action :entity!',
    'kycp_error' => 'There was a problem while communicating on KYCP',
    'documents' => [
        'not_needed' => 'The document type that you are trying to upload is not needed by the person/business.',
        'array_not_found' => 'Requirement list does not exist.',
        'position_not_found' => 'The type of business/person that you are trying to enter is not applicable.',
        'entity_not_found' => 'Entity does not exist',
        'no_uploaded' => 'No uploaded files',
    ],
    'businesses' => [
        'success' => 'Successfully retrieved all businesses.',
        'not_exist' => 'Business does not exist'
    ],
    'role' => [
        'error' => 'You don\'t have the correct permissions to access this resource.',
    ],
    'search' =>[
        'success' => ':search_found result found'
    ]
];
