<?php

namespace Tests\ApplyCorporate\Feature\AgentCompany;

use App\Models\AgentCompany;
use Illuminate\Http\Response;
use Tests\TestCase;

class AgentCompanyControllerTest extends TestCase
{
    protected $imposter = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->swapAuth0Mock();

        $m2mToken = $this->getTokenPayload('client', 'client_id.app');

        $sub = $m2mToken['sub'];

        $this->imposter = $this->createImposterUser(
            id: $sub,
            email: '',
            accessTokenPayload: $m2mToken,
        );
    }

    public function testShouldCreateAgentCompany()
    {
        $this->impersonate(credential: $this->imposter, guard: 'auth0-api')
            ->postJson(route('portal.companies.store'), [
                'name' => 'FinXP Ltd ' . fake()->lexify('????')
            ])
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'status',
                'message',
                'data'
            ]);
    }

    public function testShouldGetCompanyList()
    {
        $companies = AgentCompany::factory(10)->create()->toArray();

        $request = $this->impersonate(credential: $this->imposter, guard: 'auth0-api')
            ->getJson(route('portal.companies.index'));

        $request->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['data']);

        $this->assertEquals($companies[0]['name'], $request->json()['data'][0]['name']);

        $this->assertCount(count($companies), $request->json()['data']);
    }
}
