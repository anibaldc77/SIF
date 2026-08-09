<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OAuthAuthorizationRequestObjectReplayStoreInterface;
use Sif\Foundation\Security\Contracts\OAuthAuthorizationRequestObjectSignerInterface;
use Sif\Foundation\Security\Contracts\OAuthAuthorizationRequestObjectVerifierInterface;
use Sif\Foundation\Security\OAuth\Advanced\OAuthAuthorizationRequestObject;
use Sif\Foundation\Security\OAuth\Advanced\OAuthAuthorizationRequestObjectVerificationResult;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthAuthorizationRequest;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClientId;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthRedirectUri;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthScope;

final class OAuthJwtSecuredAuthorizationRequestsModelAndContractsTest extends TestCase
{
    public function testRequestObjectCarriesSignedRequestMetadataAndLifetime(): void
    {
        $object = new OAuthAuthorizationRequestObject(
            'header.payload.signature',
            $this->authorizationRequest(),
            'client-001',
            'https://authorization.example.test',
            new DateTimeImmutable('2026-08-08T10:00:00Z'),
            new DateTimeImmutable('2026-08-08T10:05:00Z'),
            'jar-jti-001',
            'kid-001'
        );

        self::assertSame('client-001', $object->issuer());
        self::assertSame(
            'https://authorization.example.test',
            $object->audience()
        );
        self::assertSame('jar-jti-001', $object->tokenId());
        self::assertSame('kid-001', $object->keyId());
        self::assertFalse(
            $object->expiredAt(
                new DateTimeImmutable('2026-08-08T10:04:59Z')
            )
        );
    }

    public function testVerificationResultReturnsTypedAuthorizationRequest(): void
    {
        $result = new OAuthAuthorizationRequestObjectVerificationResult(
            $this->authorizationRequest(),
            'client-001',
            'https://authorization.example.test',
            'jar-jti-001'
        );

        self::assertSame(
            'client-001',
            $result->authorizationRequest()->clientId()->value()
        );
        self::assertSame('jar-jti-001', $result->tokenId());
    }

    public function testSignerReturnsTypedRequestObject(): void
    {
        $method = new \ReflectionMethod(
            OAuthAuthorizationRequestObjectSignerInterface::class,
            'sign'
        );

        self::assertSame(
            OAuthAuthorizationRequestObject::class,
            (string) $method->getReturnType()
        );
    }

    public function testVerifierReturnsTypedVerificationResult(): void
    {
        $method = new \ReflectionMethod(
            OAuthAuthorizationRequestObjectVerifierInterface::class,
            'verify'
        );

        self::assertSame(
            OAuthAuthorizationRequestObjectVerificationResult::class,
            (string) $method->getReturnType()
        );
    }

    public function testJarContractsRemainCryptoAndInfrastructureNeutral(): void
    {
        foreach ([
            OAuthAuthorizationRequestObjectSignerInterface::class,
            OAuthAuthorizationRequestObjectVerifierInterface::class,
            OAuthAuthorizationRequestObjectReplayStoreInterface::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('firebase', strtolower($source));
            self::assertStringNotContainsString('lcobucci', strtolower($source));
            self::assertStringNotContainsString('openssl_', strtolower($source));
        }
    }

    public function testJarDomainDoesNotOwnHttpOrJwksPublication(): void
    {
        foreach ([
            OAuthAuthorizationRequestObject::class,
            OAuthAuthorizationRequestObjectVerificationResult::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString(
                'http_response_code',
                strtolower($source)
            );
            self::assertStringNotContainsString(
                'jwks_uri',
                strtolower($source)
            );
        }
    }

    private function authorizationRequest(): OAuthAuthorizationRequest
    {
        return new OAuthAuthorizationRequest(
            new OAuthClientId('client-001'),
            new OAuthRedirectUri('https://example.test/callback'),
            'code',
            [new OAuthScope('profile')],
            'state-001'
        );
    }
}
