<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Business;
use App\Models\CompanyInformation;
use App\Models\PendingApplication;
use App\Http\Resources\CompanyDefaultResource;
use Illuminate\Support\Facades\DB;
use Exception;
class MigratePendingApplication extends Command
{
    protected $signature = 'migrate:pending-application';
    protected $description = 'Migrate the current business application to table:pending_application';

    public function handle()
    {
        $this->line('Migration of application to the table');
        if ($this->confirm('Do you wish to continue?')) {
            $this->line('');
            $this->line('processing....');
            if ($this->confirm('Did you migrate the tables needed?')) {
                $this->line('');
                $this->getAllApplication();
            }
        } else {
            $this->line('Aborted....');
            return 0;
        }
    }

    public function getAllApplication()
    {
        $businesses = Business::where('status', '!=', 'PRESUBMIT')->get();

        DB::beginTransaction();

        try {
            $businesses->each(function($business) {
                $this->insertToTable($business);
            });

            DB::commit();
        } catch(Exception $e) {
            DB::rollback();
            info($e);
        }

        $this->line('Completed');
        return 0;
    }

    public function insertToTable($business)
    {
        $company = CompanyInformation::where('business_id', $business->id)->first();

        if ($company) {
            $resource = json_decode(CompanyDefaultResource::make($company)->toJson(), true);
            $pending = PendingApplication::where('company_information_id', $company->id)->first();

            $address = array_merge(
                $resource['registered_address'],
                $resource['operational_address']
            );

            $allProducts = [];
            foreach ($resource['products'] as $product) {
                array_push($allProducts, [
                    'business_id' => $business->id,
                    'product_name' => $product['product_name'],
                    'is_selected' => true
                ]);
            }

            $documents = [
                'general_documents' => $resource['general_documents'] ?? [],
                'iban4u_payment_account_documents' => $resource['iban4u_payment_account_documents'] ?? [],
                'sepa_direct_debit_documents' => $resource['sepa_direct_debit_documents'] ?? []
            ];

            $data = [
                'company_information_id' => $company->id,
                'company_name' => $resource['company_details']['name'],
                'status' =>  $business->status,
                'company_trading_as' => $resource['company_details']['trading_name'],
                'tax_name' => $resource['tax_name'],
                'is_same_address' => $resource['is_same_address'],
                'company_products' => json_encode($allProducts),
                'company_details' => json_encode($resource['company_details']),
                'company_address' => json_encode($address),
                'company_sources' => json_encode($resource['company_sources']),
                'sepa_direct_debit' =>  json_encode($resource['sepa_direct_debit']),
                'iban4u_payment_account' => json_encode($resource['iban4u_payment_account']),
                'acquiring_services' => json_encode($resource['credit_card_processing']),
                'company_representatives' => json_encode($resource['company_representative']),
                'senior_management_officer' => json_encode($resource['senior_management_officer']),
                'data_protection_and_marketing' => json_encode($resource['data_protection_marketing']),
                'required_documents' => json_encode($documents),
                'indicias' => json_encode($resource['indicias']),
                'entities' => json_encode($resource['entities']),
                'declaration' => json_encode($resource['declaration_agreement'])
            ];

            if (!$pending){
                $app = PendingApplication::create($data);
                $this->line('Added '. $app->id);
            } else {
                $app =PendingApplication::update($data);
                $this->line('Updated '. $app->id);
            }
        }
    }
}
