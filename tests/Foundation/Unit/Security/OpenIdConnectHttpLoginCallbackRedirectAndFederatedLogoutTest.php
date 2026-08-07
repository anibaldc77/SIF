<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OidcNonceGeneratorInterface;
use Sif\Foundation\Security\Contracts\OidcProviderMetadataProviderInterface;
use Sif\Foundation\Security\Contracts\OidcStateGeneratorInterface;
use Sif\Foundation\Security\Contracts\PkceCodeVerifierGeneratorInterface;
use Sif\Foundation\Security\Oidc\Http\OidcHttpLoginStartService;
use Sif\Foundation\Security\Oidc\Logout\OidcLogoutRequest;
use Sif\Foundation\Security\Oidc\Logout\StandardOidcLogoutRedirectProvider;
use Sif\Foundation\Security\Oidc\NativeS256PkceCodeChallengeFactory;
use Sif\Foundation\Security\Oidc\OidcAuthorizationRequestFactory;
use Sif\Foundation\Security\Oidc\OidcClientRegistration;
use Sif\Foundation\Security\Oidc\OidcIdToken;
use Sif\Foundation\Security\Oidc\OidcNonce;
use Sif\Foundation\Security\Oidc\OidcProviderMetadata;
use Sif\Foundation\Security\Oidc\OidcState;
use Sif\Foundation\Security\Oidc\PkceCodeVerifier;

final class OpenIdConnectHttpLoginCallbackRedirectAndFederatedLogoutTest extends TestCase
{
    public function testLoginStartProducesRedirectInstructionWithoutResponse(): void
    {
        $result = (new OidcHttpLoginStartService(
            $this->requestFactory()
        ))->start(
            new OidcClientRegistration(
                'sif-client',
                'https://app.example/oidc/callback'
            ),
            ['openid', 'profile']
        );

        self::assertSame(
            'https://identity.example/authorize',
            $result->redirect()->location()
        );
        self::assertSame(
            'code',
            $result->redirect()->query()['response_type']
        );
        self::assertSame(
            'openid profile',
            $result->redirect()->query()['scope']
        );
    }

    public function testRedirectInstructionDoesNotExecuteRedirect(): void
    {
        $reflection = new \ReflectionClass(
            \Sif\Foundation\Security\Oidc\Http\OidcRedirectInstruction::class
        );

        $source = file_get_contents(
            (string) $reflection->getFileName()
        );

        self::assertIsString($source);
        self::assertStringNotContainsString('header(', strtolower($source));
        self::assertStringNotContainsString('Response', $source);
        self::assertStringNotContainsString('redirect(', strtolower($source));
    }

    public function testStandardLogoutRedirectIncludesIdTokenHintAndPostLogoutUri(): void
    {
        $redirect = (new StandardOidcLogoutRedirectProvider())->createRedirect(
            new OidcLogoutRequest(
                'https://identity.example/logout',
                new OidcIdToken(
                    'header.payload.signature-material'
                ),
                'https://app.example/logout-complete'
            )
        );

        self::assertSame(
            'https://identity.example/logout',
            $redirect->location()
        );
        self::assertSame(
            'header.payload.signature-material',
            $redirect->query()['id_token_hint']
        );
        self::assertSame(
            'https://app.example/logout-complete',
            $redirect->query()['post_logout_redirect_uri']
        );
    }

    public function testLogoutProviderIsProviderNeutral(): void
    {
        $reflection = new \ReflectionClass(
            StandardOidcLogoutRedirectProvider::class
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

    public function testHttpLayerDoesNotCreateSessionDirectly(): void
    {
        $directory = dirname(__DIR__, 4)
            . '/src/Foundation/Security/Oidc/Http';

        foreach (glob($directory . '/*.php') ?: [] as $file) {
            $source = file_get_contents($file);

            self::assertIsString($source);
            self::assertStringNotContainsString('setcookie(', strtolower($source));
            self::assertStringNotContainsString('session_start(', strtolower($source));
        }
    }

    public function testIdTokenHintRemainsProtectedOutsideRedirectConstructionBoundary(): void
    {
        $token = new OidcIdToken(
            'header.payload.signature-material'
        );

        self::assertSame('[REDACTED]', (string) $token);
        self::assertSame(
            64,
            strlen($token->fingerprint())
        );
    }

    private function requestFactory(): OidcAuthorizationRequestFactory
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
