<?php

namespace App\Services\KYCP\Client;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Arr;
use App\Services\KYCP\Traits\Entities;
use App\Services\KYCP\Traits\CorporateEntities;
use App\Services\KYCP\Traits\Application;
use App\Services\KYCP\Traits\OnboardingApplication;
use App\Services\KYCP\Traits\Documents;
use App\Services\KYCP\Traits\CorporateDocuments;

class Factory
{
    use Entities;
    use Application;
    use OnboardingApplication;
    use Documents;
    use CorporateDocuments;
    use CorporateEntities;

    protected const API_ENDPOINT = 'KYCPApi';

    protected const HEADERS = [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json'
    ];

    /**
     * Base API Url
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * API Credentials
     *
     * @var array
     */
    protected $apiCredentials;

    public function __construct()
    {
        $this->baseUrl = config('kycp.base_url') . '/' . self::API_ENDPOINT;
        $this->apiCredentials = config('kycp.credentials', []);
    }

    /**
     * Add an application on the KYCP
     *
     * @see /application/add
     */
    public function addApplication(array $data)
    {
        $payload = array_merge($this->apiCredentials, $data);
        $endpoint = $this->baseUrl . '/application/add';

        return Http::withHeaders(self::HEADERS)->post($endpoint, $payload);
    }

    /**
     * Add or Update an application on the KYCP
     *
     * @see /application/addorupdate
     */
    public function addOrUpdateApplication(array $data)
    {
        $payload = array_merge($this->apiCredentials, $data);
        $endpoint = $this->baseUrl . '/application/addorupdate';

        return Http::withHeaders(self::HEADERS)->post($endpoint, $payload);
    }

    /**
     * Update an existing application on KYCP
     *
     * @see /application/update
     */
    public function updateApplication(array $data)
    {
        $payload = array_merge($this->apiCredentials, $data);
        $endpoint = $this->baseUrl . '/application/update';

        return Http::withHeaders(self::HEADERS)->post($endpoint, $payload);
    }

    /**
     * Get application values
     *
     * @see /application/getstructure/{applicationUid}
     */
    public function getApplication(
        string $uid,
        array $payload = ['includeAllFields' => true, 'includeAllEmptyFields' => true]
    ) {
        $payload = array_merge($payload, $this->apiCredentials);
        $endpoint = $this->baseUrl . "/application/getstructure/{$uid}";

        return Http::withHeaders(self::HEADERS)->post($endpoint, $payload);
    }

    /**
     * Get the list of entities on the given program
     *
     * @see /entity/getentitytypes/{programId}
     */
    public function getEntities(int $programId)
    {
        $query = [
            'apiRequest' => $this->apiCredentials
        ];

        return Http::withHeaders(self::HEADERS)
            ->get(
                $this->baseUrl . "/entity/getentitytypes/{$programId}",
                Arr::dot($query)
            );
    }

     /**
     * Get the fields on entity
     *
     * @see /entity/getfields/{programId}/{applicationId}/{entityTypeId}
     */
    public function getEntityFields(int $programId, int $entityTypeId)
    {
        $query = [
            'apiRequest' => $this->apiCredentials
        ];

        return Http::withHeaders(self::HEADERS)
            ->get(
                $this->baseUrl . "/entity/getfields/{$programId}/0/{$entityTypeId}",
                Arr::dot($query)
            );
    }

     /**
     * Get the lookup values of fields
     *
     * @see /field/get/{lookupId}
     */
    public function getLookupOptions(int $lookupId)
    {
        $query = [
            'apiRequest' => $this->apiCredentials
        ];

        return Http::withHeaders(self::HEADERS)
            ->get(
                $this->baseUrl . "/field/get/{$lookupId}",
                Arr::dot($query)
            );
    }
     /**
     *  Proxy Upload Documents on KYCP
     *
     * @see /document/add
     */
    public function uploadEntityDocument($file, $fileName, array $document)
    {
        $payload = [
            'apiDocumentModel' => array_merge($this->apiCredentials, $document)
        ];

        $queryParams = http_build_query(Arr::dot($payload));

        return Http::attach('apiDocumentModel.file', $file, $fileName)
        ->post(
            $this->baseUrl . '/document/add?' . $queryParams
        );
    }

    /**
     * Get application status
     *
     * @see /application/getstructure/{applicationUid}
     */
    public function getApplicationStatus(string $uid)
    {
        $payload = [ 'applicationUid' => $uid ];
        $payload = array_merge($payload, $this->apiCredentials);

        return Http::withHeaders(self::HEADERS)
            ->post(
                $this->baseUrl . "/application/getstatus",
                $payload
            );
    }

    public function updateStatus($applicationUid, $status)
    {
        $payload = array_merge([
            'applicationUid' => $applicationUid,
            'message' => 'Updated by BO Application'
        ], $this->apiCredentials);

        return Http::withHeaders(self::HEADERS)
            ->post(
                $this->baseUrl . "/application/setstatus/{$status}",
                $payload
            );
    }
}
