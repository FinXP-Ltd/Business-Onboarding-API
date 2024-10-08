<?php

namespace Tests;

use App\Models\AgentCompany;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\Auth\User;
use App\Models\Person\NaturalPerson;
use App\Models\NonNaturalPerson\NonNaturalPerson;
use Auth0\Laravel\Entities\CredentialEntity;
use Auth0\Laravel\Facade\Auth0;
use Auth0\Laravel\Traits\Impersonate;
use Auth0\Laravel\Users\ImposterUser;
use Auth0\SDK\Token;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Mockery;
use OpenSSLAsymmetricKey;
use Tests\Traits\Auth0GenerateToken;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, Auth0GenerateToken, Impersonate;

    public OpenSSLAsymmetricKey $privateKey;
    public string $publicKey;
    public array $payload;
    public string $token;

    protected static $migrationsRun = false;

    protected $connectionsToTransact = ['mysql_testing'];

    protected $auth0Mock;

    public function setUp(): void
    {
        parent::setUp();

        $this->setAuth0Config();

        config(['app.saving_secret' => 'test']);

        config(['portal.server_credentials' => collect([
            ['keykey', 'secret']
        ])]);


        config()->set('api-logger', [
            'enabled' => false,
            'application' => 'Business-Onboarding-API'
        ]);

        config('client_id', [
            'zbx' => '1231312321',
            'bp' => '1231312321',
            'app' => '1231312321'
        ]);

        if (!env('USE_SCHEMA_DUMP')) {
            $this->artisan('migrate:fresh');
        }

        $this->artisan('db:seed');

        User::query()->forceDelete();
        NaturalPerson::query()->forceDelete();
        NonNaturalPerson::query()->forceDelete();
        AgentCompany::query()->forceDelete();

        $this->auth0Mock = Mockery::mock(app('auth0'));
    }

    protected function user()
    {
        $user = User::factory()->create();
        return $user;
    }

    protected function getTokenPayload(string $role = "operation", string $clienTypeConfig = "client_id.bp")
    {
        return [
            'https://finxp.com/roles' => [
                $role
            ],
            'iss' => 'https://' . config('auth0.guards.default.domain'),
            'sub' => 'auth0|' . uniqid(),
            'aud' => [
                'https://' . config('auth0.guards.default.domain') . '/api/v2'
            ],
            'iat' => time(),
            'exp' => now()->add('hours', 2)->timestamp,
            'azp' => config($clienTypeConfig),
            'scope' => 'openid profile email',
            'gty' => 'client-credentials'
        ];
    }

    protected function createImposterUser(
        string $id,
        string $email,
        array $accessTokenPayload = [],
        array $idTokenPayload = [],
        $scope = ['openid', 'profile', 'email']
    )
    {
        return CredentialEntity::create(
            user: new ImposterUser($accessTokenPayload),
            idToken: $this->mockIdToken(algorithm: Token::ALGO_HS256, claims: $idTokenPayload, id: $id, email: $email),
            accessToken: $this->mockAccessToken(algorithm: Token::ALGO_HS256, claims: $accessTokenPayload, id: $id),
            accessTokenScope: $scope,
            accessTokenExpiration: time() + 3600
        );
    }

    protected function swapAuth0Mock()
    {
        Auth0::swap($this->auth0Mock);
    }
}
