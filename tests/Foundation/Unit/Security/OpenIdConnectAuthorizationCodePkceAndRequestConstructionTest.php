<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OidcNonceGeneratorInterface;
use Sif\Foundation\Security\Contracts\OidcProviderMetadataProviderInterface;
use Sif\Foundation\Security\Contracts\OidcStateGeneratorInterface;
use Sif\Foundation\Security\Contracts\PkceCodeVerifierGeneratorInterface;
use Sif\Foundation\Security\Oidc\NativeS256PkceCodeChallengeFactory;
use Sif\Foundation\Security\Oidc\OidcAuthorizationRequestFactory;
use Sif\Foundation\Security\Oidc\OidcClientRegistration;
use Sif\Foundation\Security\Oidc\OidcNonce;
use Sif\Foundation\Security\Oidc\OidcProviderMetadata;
use Sif\Foundation\Security\Oidc\OidcState;
use Sif\Foundation\Security\Oidc\PkceCodeVerifier;

final class OpenIdConnectAuthorizationCodePkceAndRequestConstructionTest extends TestCase
{
    public function testNativeS256FactoryProducesExpectedRfc7636Challenge(): void
    {
        $verifier = new PkceCodeVerifier(
            'dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk'
        );

        $challenge = (new NativeS256PkceCodeChallengeFactory())->create(
            $verifier
        );

        self::assertSame(
            'E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM',
            $challenge->value()
        );
    }

    public function testAuthorizationRequestContainsCodeStateNonceAndPkce(): void
    {
        $transaction = $this->factory()->create(
            new OidcClientRegistration(
                'sif-client',
                'https://app.example/oidc/callback'
            ),
            ['openid', 'profile', 'email']
        );

        $parameters = $transaction->request()->parameters();

        self::assertSame('code', $parameters['response_type']);
        self::assertSame('sif-client', $parameters['client_id']);
        self::assertSame(
            'https://app.example/oidc/callback',
            $parameters['redirect_uri']
        );
        self::assertSame('openid profile email', $parameters['scope']);
        self::assertSame(
            $transaction->state()->value(),
            $parameters['state']
        );
        self::assertSame(
            $transaction->nonce()->value(),
            $parameters['nonce']
        );
        self::assertSame('S256', $parameters['code_challenge_method']);
    }

    public function testTransactionRetainsVerifierButRequestDoesNotExposeIt(): void
    {
        $transaction = $this->factory()->create(
            new OidcClientRegistration(
                'sif-client',
                'https://app.example/oidc/callback'
            )
        );

        self::assertSame(
            'abcdefghijklmnopqrstuvwxyz0123456789-._~VERIFIER',
            $transaction->codeVerifier()->value()
        );

        self::assertArrayNotHasKey(
            'code_verifier',
            $transaction->request()->parameters()
        );
    }

    public function testAuthorizationRequestRequiresOpenIdScope(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->factory()->create(
            new OidcClientRegistration(
                'sif-client',
                'https://app.example/oidc/callback'
            ),
            ['profile']
        );
    }

    public function testAuthorizationFlowDoesNotExchangeCodeOrPersistSessionYet(): void
    {
        $directory = dirname(__DIR__, 4)
            . '/src/Foundation/Security/Oidc';

        foreach (glob($directory . '/*.php') ?: [] as $file) {
            $source = file_get_contents($file);

            self::assertIsString($source);
            self::assertStringNotContainsString(
                'grant_type',
                $source
            );
            self::assertStringNotContainsString(
                'client_secret',
                $source
            );
            self::assertStringNotContainsString(
                'Session',
                $source
            );
        }
    }

    public function testAuthorizationRequestFactoryIsProviderNeutral(): void
    {
        $reflection = new \ReflectionClass(
            OidcAuthorizationRequestFactory::class
        );

        $source = file_get_contents(
            (string) $reflection->getFileName()
        );

        self::assertIsString($source);
        self::assertStringNotContainsString('Keycloak', $source);
        self::assertStringNotContainsString('Microsoft', $source);
        self::assertStringNotContainsString('Auth0', $source);
        self::assertStringNotContainsString('Okta', $source);
    }

    private function factory(): OidcAuthorizationRequestFactory
    {
        $metadata = new class implements OidcProviderMetadataProviderInterface {
            public function get(): OidcProviderMetadata
            {
                return new OidcProviderMetadata(
                    'https://identity.example',
                    'https://identity.example/authorize',
                    'https://identity.example/token',
                    'https://identity.example/jwks',
                    ['code'],
                    ['public'],
                    ['RS256']
                );
            }

            public function refresh(): OidcProviderMetadata
            {
                return $this->get();
            }
        };

        $state = new class implements OidcStateGeneratorInterface {
            public function generate(): OidcState
            {
                return new OidcState(
                    'abcdefghijklmnopqrstuvwxyz0123456789_STATE'
                );
            }
        };

        $nonce = new class implements OidcNonceGeneratorInterface {
            public function generate(): OidcNonce
            {
                return new OidcNonce(
                    'abcdefghijklmnopqrstuvwxyz0123456789_NONCE'
                );
            }
        };

        $verifier = new class implements PkceCodeVerifierGeneratorInterface {
            public function generate(): PkceCodeVerifier
            {
                return new PkceCodeVerifier(
                    'abcdefghijklmnopqrstuvwxyz0123456789-._~VERIFIER'
                );
            }
        };

        return new OidcAuthorizationRequestFactory(
            $metadata,
            $state,
            $nonce,
            $verifier,
            new NativeS256PkceCodeChallengeFactory()
        );
    }
}
