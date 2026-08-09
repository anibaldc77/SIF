<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OAuthPushedAuthorizationRequestRepositoryInterface;
use Sif\Foundation\Security\Contracts\OAuthPushedAuthorizationRequestServiceInterface;
use Sif\Foundation\Security\Contracts\OAuthPushedAuthorizationRequestUriGeneratorInterface;
use Sif\Foundation\Security\Contracts\OAuthPushedAuthorizationRequestValidatorInterface;
use Sif\Foundation\Security\OAuth\Advanced\OAuthPushedAuthorizationRequest;
use Sif\Foundation\Security\OAuth\Advanced\OAuthPushedAuthorizationRequestUri;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthAuthorizationRequest;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClientId;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthRedirectUri;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthScope;

final class OAuthPushedAuthorizationRequestsModelAndContractsTest extends TestCase
{
    public function testPushedAuthorizationRequestHasOpaqueUriAndExplicitLifetime(): void
    {
        $request = new OAuthPushedAuthorizationRequest(
            new OAuthPushedAuthorizationRequestUri(
                'urn:ietf:params:oauth:request_uri:abc123'
            ),
            $this->authorizationRequest(),
            new DateTimeImmutable('2026-08-08T10:00:00Z'),
            new DateTimeImmutable('2026-08-08T10:05:00Z')
        );

        self::assertSame(
            'urn:ietf:params:oauth:request_uri:abc123',
            $request->requestUri()->value()
        );

        self::assertFalse(
            $request->expiredAt(
                new DateTimeImmutable('2026-08-08T10:04:59Z')
            )
        );

        self::assertTrue(
            $request->expiredAt(
                new DateTimeImmutable('2026-08-08T10:05:00Z')
            )
        );
    }

    public function testPushedRequestRetainsOriginalAuthorizationRequest(): void
    {
        $authorizationRequest = $this->authorizationRequest();

        $request = new OAuthPushedAuthorizationRequest(
            new OAuthPushedAuthorizationRequestUri('request-uri-001'),
            $authorizationRequest,
            new DateTimeImmutable('2026-08-08T10:00:00Z'),
            new DateTimeImmutable('2026-08-08T10:05:00Z')
        );

        self::assertSame(
            'client-001',
            $request->authorizationRequest()->clientId()->value()
        );

        self::assertSame(
            'https://example.test/callback',
            $request->authorizationRequest()->redirectUri()->value()
        );
    }

    public function testParServiceReturnsTypedPushedRequest(): void
    {
        $method = new \ReflectionMethod(
            OAuthPushedAuthorizationRequestServiceInterface::class,
            'push'
        );

        self::assertSame(
            OAuthPushedAuthorizationRequest::class,
            (string) $method->getReturnType()
        );
    }

    public function testRepositorySupportsSaveLookupAndConsumption(): void
    {
        $reflection = new \ReflectionClass(
            OAuthPushedAuthorizationRequestRepositoryInterface::class
        );

        self::assertTrue($reflection->hasMethod('save'));
        self::assertTrue($reflection->hasMethod('find'));
        self::assertTrue($reflection->hasMethod('consume'));
    }

    public function testParContractsRemainInfrastructureNeutral(): void
    {
        foreach ([
            OAuthPushedAuthorizationRequestServiceInterface::class,
            OAuthPushedAuthorizationRequestRepositoryInterface::class,
            OAuthPushedAuthorizationRequestUriGeneratorInterface::class,
            OAuthPushedAuthorizationRequestValidatorInterface::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString(
                'http_response_code',
                strtolower($source)
            );
        }
    }

    public function testParDomainDoesNotOwnHttpEndpointTranslation(): void
    {
        foreach ([
            OAuthPushedAuthorizationRequest::class,
            OAuthPushedAuthorizationRequestUri::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString(
                'header(',
                strtolower($source)
            );
            self::assertStringNotContainsString(
                'http_response_code',
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
