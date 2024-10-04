<?php

namespace Tests\Unit\Commands;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Illuminate\Http\Response;
use App\Services\KYCP\Facades\KYCP;
use App\Models\Business;
use App\Models\BusinessComposition;

class SyncApplicationStatusCommandTest extends TestCase
{
    const API_ENDPOINT = 'KYCPApi';

    protected $apiUrl;

    public function setUp(): void
    {
        parent::setUp();

        $this->apiUrl = config('kycp.base_url') . '/' . self::API_ENDPOINT;

        $this->mockHttp();
    }

    public function testItShouldGetAndUpdateApplicationStatus()
    {
        $b = Business::factory()->hasBusinessDetails([
            'number_shareholder' => 5,
            'number_directors' => 1
        ])->hasTaxInformation()->create([
            'application_id' => 'EMGVK3',
            'status' => 'SUBMITTED'
        ]);

        $kycp = KYCP::getApplicationStatus($b->application_id);
        $status = $kycp->json();

        $b->update([
           'status' => strtoupper($status['StatusName'])
        ]);

        $this->assertEquals(strtoupper($status['StatusName']), $b->status);
    }

    private function mockHttp()
    {
        Http::fake(
            [
                $this->apiUrl . '/application/getstatus' => Http::response([
                    "ApplicationId" => 5722,
                    "ApplicationUid" => "EMGVK3",
                    "StatusId" => 1,
                    "StatusName" => "Inputting"
                ])
            ]
        );
    }
}
