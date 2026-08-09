<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OAuthClientAuthenticatorInterface;
use Sif\Foundation\Security\Contracts\OAuthClientSecretVerifierInterface;
use Sif\Foundation\Security\Contracts\OAuthMutualTlsClientVerifierInterface;
use Sif\Foundation\Security\Contracts\OAuthPrivateKeyJwtVerifierInterface;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClient;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClientAuthenticationMethod;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClientAuthenticationRequest;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClientCredential;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClientId;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthRedirectUri;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthScope;

final class OAuth21ClientAuthenticationConfidentialPublicAndPrivateKeyJwtContractsTest extends TestCase
{
    public function testPublicClientCanUseNoneAuthenticationMethod(): void
    {
        $request = new OAuthClientAuthenticationRequest(
            new OAuthClientId('public-client'),
            new OAuthClientAuthenticationMethod(
                OAuthClientAuthenticationMethod::NONE
            )
        );

        self::assertSame(
            OAuthClientAuthenticationMethod::NONE,
            $request->method()->value()
        );
        self::assertNull($request->credential());
    }

    public function testConfidentialClientCanCarryClientSecretCredential(): void
    {
        $credential = new OAuthClientCredential(
            new OAuthClientAuthenticationMethod(
                OAuthClientAuthenticationMethod::CLIENT_SECRET
            ),
            'secret-value'
        );

        self::assertSame(
            OAuthClientAuthenticationMethod::CLIENT_SECRET,
            $credential->method()->value()
        );
        self::assertSame('secret-value', $credential->value());
    }

    public function testPrivateKeyJwtRemainsAContractBoundary(): void
    {
        $reflection = new \ReflectionClass(
            OAuthPrivateKeyJwtVerifierInterface::class
        );

        self::assertTrue($reflection->isInterface());

        $source = file_get_contents(
            (string) $reflection->getFileName()
        );

        self::assertIsString($source);
        self::assertStringNotContainsString('firebase', strtolower($source));
        self::assertStringNotContainsString('openssl_', strtolower($source));
    }

    public function testMtlsRemainsAContractBoundary(): void
    {
        $reflection = new \ReflectionClass(
            OAuthMutualTlsClientVerifierInterface::class
        );

        self::assertTrue($reflection->isInterface());

        $source = file_get_contents(
            (string) $reflection->getFileName()
        );

        self::assertIsString($source);
        self::assertStringNotContainsString('$_SERVER', $source);
        self::assertStringNotContainsString('curl_', strtolower($source));
    }

    public function testAuthenticationContractsRemainInfrastructureNeutral(): void
    {
        foreach ([
            OAuthClientAuthenticatorInterface::class,
            OAuthClientSecretVerifierInterface::class,
            OAuthPrivateKeyJwtVerifierInterface::class,
            OAuthMutualTlsClientVerifierInterface::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
        }
    }

    public function testClientModelStillDistinguishesPublicAndConfidentialClients(): void
    {
        $public = new OAuthClient(
            new OAuthClientId('public-client'),
            'Public',
            false,
            [new OAuthRedirectUri('https://example.test/callback')],
            [new OAuthScope('profile')]
        );

        $confidential = new OAuthClient(
            new OAuthClientId('confidential-client'),
            'Confidential',
            true,
            [new OAuthRedirectUri('https://example.test/callback')],
            [new OAuthScope('profile')]
        );

        self::assertFalse($public->confidential());
        self::assertTrue($confidential->confidential());
    }
}
