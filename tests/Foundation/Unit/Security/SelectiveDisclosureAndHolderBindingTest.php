<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\DisclosedClaimsExtractorInterface;
use Sif\Foundation\Security\Contracts\HolderBindingVerifierInterface;
use Sif\Foundation\Security\Contracts\SelectiveDisclosurePolicyInterface;
use Sif\Foundation\Security\VerifiableCredentials\HolderBindingAssessment;
use Sif\Foundation\Security\VerifiableCredentials\HolderBindingContext;
use Sif\Foundation\Security\VerifiableCredentials\SelectiveDisclosureAssessment;
use Sif\Foundation\Security\VerifiableCredentials\SelectiveDisclosureRequest;
use Sif\Foundation\Security\VerifiableCredentials\VerifiableCredential;

final class SelectiveDisclosureAndHolderBindingTest extends TestCase
{
    public function testSelectiveDisclosureRequestSeparatesRequiredAndOptionalClaims(): void
    {
        $request = new SelectiveDisclosureRequest(
            ['given_name', 'birthdate'],
            ['address']
        );

        self::assertSame(
            ['given_name', 'birthdate'],
            $request->requiredClaims()
        );
        self::assertSame(['address'], $request->optionalClaims());
    }

    public function testDisclosureAssessmentSeparatesMissingAndExcessClaims(): void
    {
        $assessment = new SelectiveDisclosureAssessment(
            false,
            ['birthdate'],
            ['national_id'],
            ['optional address omitted']
        );

        self::assertFalse($assessment->valid());
        self::assertSame(['birthdate'], $assessment->missingClaims());
        self::assertSame(['national_id'], $assessment->excessClaims());
        self::assertSame(
            ['optional address omitted'],
            $assessment->warnings()
        );
    }

    public function testHolderBindingContextKeepsHolderAudienceAndNonceExplicit(): void
    {
        $context = new HolderBindingContext(
            'wallet-holder-001',
            'verifier-001',
            'nonce-001'
        );

        self::assertSame(
            'wallet-holder-001',
            $context->expectedHolder()
        );
        self::assertSame('verifier-001', $context->audience());
        self::assertSame('nonce-001', $context->nonce());
    }

    public function testHolderBindingAssessmentRepresentsViolations(): void
    {
        $assessment = new HolderBindingAssessment(
            false,
            ['holder_mismatch']
        );

        self::assertFalse($assessment->valid());
        self::assertSame(
            ['holder_mismatch'],
            $assessment->violations()
        );
    }

    public function testSelectiveDisclosurePolicyPreservesExistingValidateMethod(): void
    {
        $validate = new \ReflectionMethod(
            SelectiveDisclosurePolicyInterface::class,
            'validate'
        );
        $assess = new \ReflectionMethod(
            SelectiveDisclosurePolicyInterface::class,
            'assess'
        );

        self::assertSame('void', (string) $validate->getReturnType());
        self::assertSame(
            SelectiveDisclosureAssessment::class,
            (string) $assess->getReturnType()
        );
    }

    public function testHolderBindingAndClaimsExtractionContractsAreTyped(): void
    {
        $holder = new \ReflectionMethod(
            HolderBindingVerifierInterface::class,
            'verify'
        );
        $claims = new \ReflectionMethod(
            DisclosedClaimsExtractorInterface::class,
            'extract'
        );

        self::assertSame(
            HolderBindingAssessment::class,
            (string) $holder->getReturnType()
        );
        self::assertSame('array', (string) $claims->getReturnType());
    }

    public function testSelectiveDisclosureLayerRemainsCryptoAndFormatNeutral(): void
    {
        foreach ([
            SelectiveDisclosurePolicyInterface::class,
            HolderBindingVerifierInterface::class,
            DisclosedClaimsExtractorInterface::class,
            SelectiveDisclosureRequest::class,
            SelectiveDisclosureAssessment::class,
            HolderBindingContext::class,
            HolderBindingAssessment::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('openssl_', strtolower($source));
            self::assertStringNotContainsString('kb-jwt', strtolower($source));
            self::assertStringNotContainsString('sd-jwt', strtolower($source));
            self::assertStringNotContainsString('CBOR', $source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
        }
    }
}
