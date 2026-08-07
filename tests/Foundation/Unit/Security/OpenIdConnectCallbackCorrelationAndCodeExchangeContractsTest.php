<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OidcTokenExchangerInterface;
use Sif\Foundation\Security\Exceptions\InvalidOidcAuthorizationCallbackException;
use Sif\Foundation\Security\Oidc\OidcAuthorizationCallback;
use Sif\Foundation\Security\Oidc\OidcAuthorizationCallbackValidator;
use Sif\Foundation\Security\Oidc\OidcAuthorizationCode;
use Sif\Foundation\Security\Oidc\OidcAuthorizationRequest;
use Sif\Foundation\Security\Oidc\OidcAuthorizationTransaction;
use Sif\Foundation\Security\Oidc\OidcClientRegistration;
use Sif\Foundation\Security\Oidc\OidcClientSecret;
use Sif\Foundation\Security\Oidc\OidcNonce;
use Sif\Foundation\Security\Oidc\OidcState;
use Sif\Foundation\Security\Oidc\OidcTokenExchangeRequestFactory;
use Sif\Foundation\Security\Oidc\PkceCodeChallenge;
use Sif\Foundation\Security\Oidc\PkceCodeVerifier;
use Sif\Foundation\Security\Oidc\PkceMethod;

final class OpenIdConnectCallbackCorrelationAndCodeExchangeContractsTest extends TestCase
{
    public function testMatchingStateValidatesSuccessfully(): void
    {
        $transaction = $this->transaction();

        (new OidcAuthorizationCallbackValidator())->validate(
            $transaction,
            new OidcAuthorizationCallback(
                $transaction->state(),
                new OidcAuthorizationCode(
                    'authorization-code-material-abcdefghijklmnopqrstuvwxyz'
                )
            )
        );

        self::addToAssertionCount(1);
    }

    public function testMismatchedStateFailsClosed(): void
    {
        $this->expectException(
            InvalidOidcAuthorizationCallbackException::class
        );

        (new OidcAuthorizationCallbackValidator())->validate(
            $this->transaction(),
            new OidcAuthorizationCallback(
                new OidcState(
                    'differentabcdefghijklmnopqrstuvwxyz012345_STATE'
                ),
                new OidcAuthorizationCode(
                    'authorization-code-material-abcdefghijklmnopqrstuvwxyz'
                )
            )
        );
    }

    public function testAuthorizationCodeAndClientSecretAreRedactedAndNonSerializable(): void
    {
        $code = new OidcAuthorizationCode(
            'authorization-code-material-abcdefghijklmnopqrstuvwxyz'
        );
        $secret = new OidcClientSecret(
            'confidential-client-secret-material'
        );

        self::assertSame('[REDACTED]', (string) $code);
        self::assertSame('[REDACTED]', (string) $secret);

        try {
            serialize($code);
            self::fail('Authorization code serialization must fail.');
        } catch (\LogicException) {
            self::addToAssertionCount(1);
        }

        $this->expectException(\LogicException::class);
        serialize($secret);
    }

    public function testTokenExchangeRequestUsesOriginalPkceVerifierAndCallbackCode(): void
    {
        $transaction = $this->transaction();
        $callback = new OidcAuthorizationCallback(
            $transaction->state(),
            new OidcAuthorizationCode(
                'authorization-code-material-abcdefghijklmnopqrstuvwxyz'
            )
        );

        $request = (new OidcTokenExchangeRequestFactory())->create(
            $transaction,
            $callback,
            new OidcClientRegistration(
                'sif-client',
                'https://app.example/oidc/callback',
                true
            ),
            new OidcClientSecret(
                'confidential-client-secret-material'
            )
        );

        self::assertSame(
            $transaction->codeVerifier()->value(),
            $request->codeVerifier()->value()
        );
        self::assertSame(
            'authorization-code-material-abcdefghijklmnopqrstuvwxyz',
            $request->authorizationCode()->expose(
                static fn (string $value): string => $value
            )
        );
        self::assertNotNull($request->clientSecret());
    }

    public function testTokenExchangeContractRemainsTransportNeutral(): void
    {
        $reflection = new \ReflectionClass(
            OidcTokenExchangerInterface::class
        );

        $source = file_get_contents(
            (string) $reflection->getFileName()
        );

        self::assertIsString($source);
        self::assertStringNotContainsString('curl', strtolower($source));
        self::assertStringNotContainsString('HttpClient', $source);
        self::assertStringNotContainsString('Keycloak', $source);
        self::assertStringNotContainsString('client_secret=', $source);
    }

    public function testI3DoesNotValidateIdTokenOrCreateSessionYet(): void
    {
        $directory = dirname(__DIR__, 4)
            . '/src/Foundation/Security/Oidc';

        foreach (glob($directory . '/*.php') ?: [] as $file) {
            $source = file_get_contents($file);

            self::assertIsString($source);
            self::assertStringNotContainsString(
                'AuthenticatedPrincipal',
                $source
            );
            self::assertStringNotContainsString(
                'Session',
                $source
            );
            self::assertStringNotContainsString(
                'validateIdToken',
                $source
            );
        }
    }

    private function transaction(): OidcAuthorizationTransaction
    {
        $state = new OidcState(
            'abcdefghijklmnopqrstuvwxyz0123456789_STATE'
        );
        $nonce = new OidcNonce(
            'abcdefghijklmnopqrstuvwxyz0123456789_NONCE'
        );
        $verifier = new PkceCodeVerifier(
            'abcdefghijklmnopqrstuvwxyz0123456789-._~VERIFIER'
        );

        return new OidcAuthorizationTransaction(
            $state,
            $nonce,
            $verifier,
            new OidcAuthorizationRequest(
                'https://identity.example/authorize',
                'sif-client',
                'https://app.example/oidc/callback',
                $state,
                $nonce,
                new PkceCodeChallenge(
                    'abcdefghijklmnopqrstuvwxyz0123456789_CHALLENGE'
                ),
                PkceMethod::S256,
                ['openid']
            )
        );
    }
}
