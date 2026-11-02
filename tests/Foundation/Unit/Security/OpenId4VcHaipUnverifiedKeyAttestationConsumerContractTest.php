<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionNamedType;
use Sif\Foundation\Security\Contracts\OpenId4VcHaipUnverifiedKeyAttestationEvidenceInterface;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipExplicitX509TrustEvaluator;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipUntrustedEs256SignatureMatcher;

final class OpenId4VcHaipUnverifiedKeyAttestationConsumerContractTest extends TestCase
{
    public function testEs256MatcherAcceptsContractWithoutGrantingTrust(): void
    {
        self::assertEvidenceParameter(
            OpenId4VcHaipUntrustedEs256SignatureMatcher::class,
            'matches'
        );

        $matcher = new OpenId4VcHaipUntrustedEs256SignatureMatcher();

        self::assertFalse(
            $matcher->matches(self::invalidEvidence())
        );
    }

    public function testX509EvaluatorAcceptsContractWithoutGrantingTrust(): void
    {
        self::assertEvidenceParameter(
            OpenId4VcHaipExplicitX509TrustEvaluator::class,
            'chainMatchesTrustedAnchors'
        );

        $evaluator = new OpenId4VcHaipExplicitX509TrustEvaluator();

        self::assertFalse(
            $evaluator->chainMatchesTrustedAnchors(
                self::invalidEvidence(),
                ['not-a-trusted-anchor']
            )
        );
    }

    private static function assertEvidenceParameter(
        string $className,
        string $methodName
    ): void {
        $method = new ReflectionMethod($className, $methodName);
        $parameters = $method->getParameters();

        self::assertNotEmpty($parameters);

        $type = $parameters[0]->getType();

        self::assertInstanceOf(ReflectionNamedType::class, $type);

        self::assertSame(
            OpenId4VcHaipUnverifiedKeyAttestationEvidenceInterface::class,
            $type->getName()
        );
    }

    private static function invalidEvidence(): OpenId4VcHaipUnverifiedKeyAttestationEvidenceInterface
    {
        return new class implements OpenId4VcHaipUnverifiedKeyAttestationEvidenceInterface {
            public function signingInput(): string
            {
                return 'header.payload';
            }

            public function rawSignature(): string
            {
                return str_repeat("\x01", 64);
            }

            public function unverifiedHeader(): array
            {
                return ['alg' => 'ES256'];
            }

            public function unverifiedClaims(): array
            {
                return ['iat' => 1710000000];
            }

            public function unverifiedCertificateDerChain(): array
            {
                return ['not-a-certificate'];
            }
        };
    }
}
