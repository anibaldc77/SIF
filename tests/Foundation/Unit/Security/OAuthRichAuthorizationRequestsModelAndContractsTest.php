<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OAuthAuthorizationDetailsNormalizerInterface;
use Sif\Foundation\Security\Contracts\OAuthAuthorizationDetailsValidatorInterface;
use Sif\Foundation\Security\Contracts\OAuthAuthorizationDetailTypePolicyInterface;
use Sif\Foundation\Security\OAuth\Advanced\OAuthAuthorizationDetail;
use Sif\Foundation\Security\OAuth\Advanced\OAuthAuthorizationDetailType;
use Sif\Foundation\Security\OAuth\Advanced\OAuthRichAuthorizationRequest;

final class OAuthRichAuthorizationRequestsModelAndContractsTest extends TestCase
{
    public function testAuthorizationDetailKeepsTypeAndAttributesExplicit(): void
    {
        $detail = new OAuthAuthorizationDetail(
            new OAuthAuthorizationDetailType('payment_initiation'),
            [
                'instructedAmount' => [
                    'currency' => 'EUR',
                    'amount' => '123.50',
                ],
            ]
        );

        self::assertSame(
            'payment_initiation',
            $detail->type()->value()
        );
        self::assertSame(
            'EUR',
            $detail->attributes()['instructedAmount']['currency']
        );
    }

    public function testRichAuthorizationRequestRequiresAtLeastOneDetail(): void
    {
        $request = new OAuthRichAuthorizationRequest(
            [
                new OAuthAuthorizationDetail(
                    new OAuthAuthorizationDetailType('document_access'),
                    ['actions' => ['read']]
                ),
            ]
        );

        self::assertCount(1, $request->authorizationDetails());
    }

    public function testRichAuthorizationRequestCanCheckDetailTypes(): void
    {
        $request = new OAuthRichAuthorizationRequest(
            [
                new OAuthAuthorizationDetail(
                    new OAuthAuthorizationDetailType('document_access'),
                    ['actions' => ['read']]
                ),
            ]
        );

        self::assertTrue(
            $request->hasType(
                new OAuthAuthorizationDetailType('document_access')
            )
        );

        self::assertFalse(
            $request->hasType(
                new OAuthAuthorizationDetailType('payment_initiation')
            )
        );
    }

    public function testNormalizerReturnsTypedRichAuthorizationRequest(): void
    {
        $method = new \ReflectionMethod(
            OAuthAuthorizationDetailsNormalizerInterface::class,
            'normalize'
        );

        self::assertSame(
            OAuthRichAuthorizationRequest::class,
            (string) $method->getReturnType()
        );
    }

    public function testRarContractsRemainInfrastructureAndDomainNeutral(): void
    {
        foreach ([
            OAuthAuthorizationDetailsValidatorInterface::class,
            OAuthAuthorizationDetailTypePolicyInterface::class,
            OAuthAuthorizationDetailsNormalizerInterface::class,
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
                'payment_initiation',
                $source
            );
            self::assertStringNotContainsString(
                'document_access',
                $source
            );
        }
    }

    public function testRarDomainDoesNotOwnHttpOrStorage(): void
    {
        foreach ([
            OAuthAuthorizationDetail::class,
            OAuthAuthorizationDetailType::class,
            OAuthRichAuthorizationRequest::class,
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
            self::assertStringNotContainsString('PDO', $source);
        }
    }
}
