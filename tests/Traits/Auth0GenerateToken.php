<?php

namespace Tests\Traits;

use Auth0\SDK\Configuration\SdkConfiguration;
use Auth0\SDK\Token;
use Auth0\SDK\Token\Generator;
use RuntimeException;

trait Auth0GenerateToken {
    public $secret = '';

    public function mockIdToken(
        string $id,
        string $email,
        string $algorithm = Token::ALGO_RS256,
        array $claims = [],
        array $headers = []
    ): string {
        $secret = $this->createRsaKeys()->private;

        $claims = array_merge([
            "iss" => config('auth0.guards.default.domain') . '/',
            'sub' => $id,
            'aud' => config('auth0.guards.default.clientId'),
            'exp' => time() + 60,
            'iat' => time(),
            'email' => $email
        ], $claims);

        return (string) Generator::create($secret, $algorithm, $claims, $headers);
    }

    public function mockAccessToken(
        string $id,
        string $algorithm = Token::ALGO_RS256,
        array $claims = [],
        array $headers = []
    ): string {
        $secret = $this->createRsaKeys()->private;

        $claims = array_merge([
            "iss" => 'https://' . config('auth0.guards.default.domain') . '/',
            'sub' => $id,
            'aud' => [
                'https://' . config('auth0.guards.default.domain') . '/api/v1'
            ],
            'iat' => time(),
            'exp' => time() + (60 * 60 * 2),
            'azp' => config('auth0.guards.default.clientId'),
            'scope' => 'openid profile email',
        ], $claims);

        return (string) Generator::create($secret, $algorithm, $claims, $headers);
    }

    public function createRsaKeys(
        string $digestAlg = 'sha256',
        int $keyType = OPENSSL_KEYTYPE_RSA,
        int $bitLength = 2048
    ): object
    {
        $config = [
            'digest_alg' => $digestAlg,
            'private_key_type' => $keyType,
            'private_key_bits' => $bitLength,
        ];

        $privateKeyResource = openssl_pkey_new($config);

        if ($privateKeyResource === false) {
            throw new RuntimeException("OpenSSL reported an error: " . $this->getSslError());
        }

        $export = openssl_pkey_export($privateKeyResource, $privateKey);

        if ($export === false) {
            throw new RuntimeException("OpenSSL reported an error: " . $this->getSslError());
        }

        $publicKey = openssl_pkey_get_details($privateKeyResource);

        $resCsr = openssl_csr_new([], $privateKeyResource);
        $resCert = openssl_csr_sign($resCsr, null, $privateKeyResource, 30);
        openssl_x509_export($resCert, $x509);

        return (object) [
            'private' => $privateKey,
            'public' => $publicKey['key'],
            'cert' => $x509,
            'resource' => $privateKeyResource,
        ];
    }

    public function getSslError(): string
    {
        $errors = [];

        while ($error = openssl_error_string()) {
            $errors[] = $error;
        }

        return implode(', ', $errors);
    }

    public function setAuth0Config()
    {
        $this->secret = uniqid();

        config([
            'auth0.AUTH0_CONFIG_VERSION' => 2,
            'auth0.guards.default.strategy' => SdkConfiguration::STRATEGY_API,
            'auth0.guards.default.domain' => uniqid() . '.auth0.com',
            'auth0.guards.default.clientId' => uniqid(),
            'auth0.guards.default.audience' => [uniqid()],
            'auth0.guards.default.clientSecret' => $this->secret,
            'auth0.guards.default.cookieSecret' => uniqid(),
            'auth0.guards.default.tokenAlgorithm' => Token::ALGO_HS256,
        ]);
    }
}
