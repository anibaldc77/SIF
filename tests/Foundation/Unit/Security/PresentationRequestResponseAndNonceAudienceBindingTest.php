<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\PresentationBindingPolicyInterface;
use Sif\Foundation\Security\Contracts\PresentationReplayStoreInterface;
use Sif\Foundation\Security\Contracts\PresentationRequestFactoryInterface;
use Sif\Foundation\Security\Contracts\PresentationResponseMapperInterface;
use Sif\Foundation\Security\VerifiableCredentials\PresentationBindingAssessment;
use Sif\Foundation\Security\VerifiableCredentials\PresentationRequest;
use Sif\Foundation\Security\VerifiableCredentials\PresentationResponse;
use Sif\Foundation\Security\VerifiableCredentials\VerifiableCredential;
use Sif\Foundation\Security\VerifiableCredentials\VerifiableCredentialSubject;
use Sif\Foundation\Security\VerifiableCredentials\VerifiablePresentation;

final class PresentationRequestResponseAndNonceAudienceBindingTest extends TestCase
{
    public function testPresentationRequestKeepsSecurityBindingInputsExplicit(): void
    {
        $request = new PresentationRequest(
            'request-001',
            'verifier-001',
            'nonce-001',
            ['IdentityCredential'],
            ['given_name', 'family_name'],
            'state-001'
        );

        self::assertSame('request-001', $request->requestId());
        self::assertSame('verifier-001', $request->audience());
        self::assertSame('nonce-001', $request->nonce());
        self::assertSame(
            ['IdentityCredential'],
            $request->requestedCredentialTypes()
        );
        self::assertSame(
            ['given_name', 'family_name'],
            $request->requestedClaims()
        );
        self::assertSame('state-001', $request->state());
    }

    public function testPresentationResponseKeepsRequestAudienceNonceAndStateExplicit(): void
    {
        $response = new PresentationResponse(
            'request-001',
            $this->presentation(),
            'verifier-001',
            'nonce-001',
            'state-001'
        );

        self::assertSame('request-001', $response->requestId());
        self::assertSame('verifier-001', $response->audience());
        self::assertSame('nonce-001', $response->nonce());
        self::assertSame('state-001', $response->state());
        self::assertSame(
            'wallet-holder-001',
            $response->presentation()->holder()
        );
    }

    public function testBindingAssessmentRepresentsViolationsExplicitly(): void
    {
        $assessment = new PresentationBindingAssessment(
            false,
            ['nonce_mismatch', 'audience_mismatch']
        );

        self::assertFalse($assessment->valid());
        self::assertSame(
            ['nonce_mismatch', 'audience_mismatch'],
            $assessment->violations()
        );
    }

    public function testBindingPolicyReturnsTypedAssessment(): void
    {
        $method = new \ReflectionMethod(
            PresentationBindingPolicyInterface::class,
            'assess'
        );

        self::assertSame(
            PresentationBindingAssessment::class,
            (string) $method->getReturnType()
        );
    }

    public function testFactoryAndMapperContractsAreTyped(): void
    {
        $factory = new \ReflectionMethod(
            PresentationRequestFactoryInterface::class,
            'create'
        );
        $mapper = new \ReflectionMethod(
            PresentationResponseMapperInterface::class,
            'map'
        );

        self::assertSame(
            PresentationRequest::class,
            (string) $factory->getReturnType()
        );
        self::assertSame(
            PresentationResponse::class,
            (string) $mapper->getReturnType()
        );
    }

    public function testReplayProtectionRemainsStorageNeutral(): void
    {
        self::assertTrue(
            (new \ReflectionClass(
                PresentationReplayStoreInterface::class
            ))->isInterface()
        );
    }

    public function testPresentationBindingLayerRemainsTransportAndWalletNeutral(): void
    {
        foreach ([
            PresentationRequestFactoryInterface::class,
            PresentationBindingPolicyInterface::class,
            PresentationResponseMapperInterface::class,
            PresentationReplayStoreInterface::class,
            PresentationRequest::class,
            PresentationResponse::class,
            PresentationBindingAssessment::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Guzzle', $source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('wallet://', strtolower($source));
        }
    }

    private function presentation(): VerifiablePresentation
    {
        return new VerifiablePresentation(
            'wallet-holder-001',
            [
                new VerifiableCredential(
                    'https://issuer.example.test',
                    new VerifiableCredentialSubject(
                        'user-001',
                        ['given_name' => 'Alice']
                    ),
                    ['IdentityCredential']
                ),
            ],
            'nonce-001',
            'verifier-001'
        );
    }
}
