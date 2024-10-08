<?php

return [
  'entity_documents' => [
    'UBO',
    'SH',
    'SH_CORPORATE',
    'DIR',
    'DIR_CORPORATE',
    'SIG',
    'B',
  ],
  'document_types' => [
    'agreements_with_the_entities_that_shall_be_settling_funds_into_the_account',
    'application_form',
    'approval',
    'audited_accounts',
    'authorised_signatories_form',
    'bank_statement_demonstration_deposits',
    'board_resolution',
    'brief_company_profile',
    'certificate_of_incorporation',
    'coloured_copy_of_photo_identity_document',
    'copy_of_court_order_judicial_separation_agreement',
    'copy_of_will_or_signed_letter_from_solicitor_or_grant_of_probate_or_letter_from_executor',
    'detailed_corporate_chart_showing_from_ubos_down_to_controlling_persons',
    'documentary_evidence_for_the_donor_as_detailed_above',
    'employment_contract_or_statement_of_income',
    'evidence_from_the_lottery_company_cheque_winnings_receipt',
    'export_of_app_for_archive_for_new_split_programs',
    'fxp_internal',
    'loan_agreement_or_statement',
    'memorandum_and_articles_of_association',
    'other',
    'processing_history',
    'product_information',
    'proof_of_ownership_of_the_domain',
    'screening_searches',
    'signed_letter_from_notary_or_solicitor_or_advocate_or_estate_agent_contract_of_sale',
    'source_of_wealth_declaration_form',
    'sow_supporting_docs',
    'statement_from_investment_provider_or_bank_statement_showing_settlement_of_investment',
    'transaction_monitoring_documentation',
    'utility_bill_or_proof_of_address',
    'yearly_review_docs',
  ],
  'assigned_keys' => [
    'agreements_with_the_entities_that_shall_be_settling_funds_into_the_account' => 8,
    'application_form' => 1,
    'approval' => 37,
    'audited_accounts' => 19,
    'authorised_signatories_form' => 33,
    'bank_statement_demonstration_deposits' => 25,
    'board_resolution' => 34,
    'brief_company_profile' => 6,
    'certificate_of_incorporation' => 4,
    'coloured_copy_of_photo_identity_document' => 11,
    'copy_of_court_order_judicial_separation_agreement' => 13,
    'copy_of_will_or_signed_letter_from_solicitor_or_grant_of_probate_or_letter_from_executor' => 14,
    'detailed_corporate_chart_showing_from_ubos_down_to_controlling_persons' => 5,
    'documentary_evidence_for_the_donor_as_detailed_above' => 15,
    'employment_contract_or_statement_of_income' => 16,
    'evidence_from_the_lottery_company_cheque_winnings_receipt' => 17,
    'export_of_app_for_archive_for_new_split_programs' => 36,
    'fxp_internal' => 26,
    'loan_agreement_or_statement' => 20,
    'memorandum_and_articles_of_association' => 2,
    'other' => 28,
    'processing_history' => 31,
    'product_information' => 32,
    'proof_of_ownership_of_the_domain' => 7,
    'screening_searches' => 35,
    'signed_letter_from_notary_or_solicitor_or_advocate_or_estate_agent_contract_of_sale' => 21,
    'source_of_wealth_declaration_form' => 22,
    'sow_supporting_docs' => 38,
    'statement_from_investment_provider_or_bank_statement_showing_settlement_of_investment' => 23,
    'transaction_monitoring_documentation' => 40,
    'utility_bill_or_proof_of_address' => 24,
    'yearly_review_docs' => 39,
  ],
  'business_document_required' => [
    'certificate_of_incorporation'
  ],
  'business_document_optional' => [
    'detailed_corporate_chart_showing_from_ubos_down_to_controlling_persons',
    'agreements_with_the_entities_that_shall_be_settling_funds_into_the_account',
    'audited_accounts',
    'approval',
    'bank_statement_demonstration_deposits',
    'board_resolution',
    'brief_company_profile',
    'other',
    'export_of_app_for_archive_for_new_split_programs',
    'fxp_internal',
    'screening_searches',
    'transaction_monitoring_documentation',
    'yearly_review_docs',
    'processing_history',
    'product_information',
    'proof_of_ownership_of_the_domain',
  ],
  'ubo_document_required' => [
  ],
  'ubo_document_optional' => [
    'fxp_internal',
    'other',
    'screening_searches',
    'source_of_wealth_declaration_form',
    'sow_supporting_docs',
    'utility_bill_or_proof_of_address',
    'coloured_copy_of_photo_identity_document'
  ],
  'dir_document_required' => [
    'utility_bill_or_proof_of_address',
  ],
  'dir_document_optional' => [
    'other',
    'screening_searches',
    'coloured_copy_of_photo_identity_document'
  ],
  'dir_corporate_document_required' => [
    'utility_bill_or_proof_of_address'
  ],
  'dir_corporate_document_optional' => [
    'certificate_of_incorporation',
    'fxp_internal',
    'memorandum_and_articles_of_association',
    'other'
  ],
  'sh_document_required' => [
    'utility_bill_or_proof_of_address'
  ],
  'sh_document_optional' => [
    'coloured_copy_of_photo_identity_document',
    'audited_accounts',
    'bank_statement_demonstration_deposits',
    'copy_of_court_order_judicial_separation_agreement',
    'copy_of_will_or_signed_letter_from_solicitor_or_grant_of_probate_or_letter_from_executor',
    'documentary_evidence_for_the_donor_as_detailed_above',
    'employment_contract_or_statement_of_income',
    'evidence_from_the_lottery_company_cheque_winnings_receipt',
    'loan_agreement_or_statement',
    'other',
    'screening_searches',
    'signed_letter_from_notary_or_solicitor_or_advocate_or_estate_agent_contract_of_sale',
    'sow_supporting_docs',
    'statement_from_investment_provider_or_bank_statement_showing_settlement_of_investment',
  ],
  'sh_corporate_document_required' => [
    'utility_bill_or_proof_of_address'
  ],
  'sh_corporate_document_optional' => [
    'memorandum_and_articles_of_association',
    'fxp_internal',
    'other',
    'screening_searches',
  ],
  'sig_document_required' => [
    'utility_bill_or_proof_of_address'
  ],
  'sig_document_optional' => [
    'other',
    'coloured_copy_of_photo_identity_document'
  ],
  'sole_trader_document_required' => [],
  'sole_trader_document_optional' => [
     'memorandum_and_articles_of_association'
   ]
];
