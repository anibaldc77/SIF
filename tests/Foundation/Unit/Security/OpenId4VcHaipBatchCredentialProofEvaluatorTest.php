<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OpenId4VcHaipCredentialProofPolicyInterface;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipBatchCredentialProofEvaluator;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipCredentialProofAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipCredentialProofContext;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\BatchCredentialIssuanceRequest;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceRequest;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceContext;

final class OpenId4VcHaipBatchCredentialProofEvaluatorTest extends TestCase
{
    public function testAllRequestsAreEvaluatedInOrder(): void
    {
        $visited = [];

        $policy = new class($visited) implements OpenId4VcHaipCredentialProofPolicyInterface {
            /** @var list<string> */
            public array $visited = [];

            /**
             * @param list<string> $visited
             */
            public function __construct(array $visited)
            {
                $this->visited = $visited;
            }

            public function assess(
                OpenId4VcHaipCredentialProofContext $context
            ): OpenId4VcHaipCredentialProofAssessment {
                $this->visited[] = $context->credentialConfigurationId();

                return new OpenId4VcHaipCredentialProofAssessment(true);
            }
        };

        $request = $this->batch(['identity', 'address']);

        $result = (new OpenId4VcHaipBatchCredentialProofEvaluator($policy))
            ->assess($request, [
                $this->context('identity'),
                $this->context('address'),
            ]);

        self::assertTrue($result->conformant());
        self::assertSame(2, $result->count());
        self::assertSame(['identity', 'address'], $policy->visited);
    }

    public function testOneFailedAssessmentRejectsBatchWithoutSkippingOthers(): void
    {
        $policy = new class implements OpenId4VcHaipCredentialProofPolicyInterface {
            public int $calls = 0;

            public function assess(
                OpenId4VcHaipCredentialProofContext $context
            ): OpenId4VcHaipCredentialProofAssessment {
                ++$this->calls;

                return new OpenId4VcHaipCredentialProofAssessment(
                    $context->credentialConfigurationId() !== 'address',
                    $context->credentialConfigurationId() === 'address'
                        ? ['credential_proof_not_verified']
                        : []
                );
            }
        };

        $result = (new OpenId4VcHaipBatchCredentialProofEvaluator($policy))
            ->assess(
                $this->batch(['identity', 'address', 'licence']),
                [
                    $this->context('identity'),
                    $this->context('address'),
                    $this->context('licence'),
                ]
            );

        self::assertFalse($result->conformant());
        self::assertSame(3, $policy->calls);
        self::assertTrue($result->assessmentAt(0)->conformant());
        self::assertFalse($result->assessmentAt(1)->conformant());
        self::assertTrue($result->assessmentAt(2)->conformant());
    }

    public function testMissingContextIsRejectedBeforePolicyExecution(): void
    {
        $policy = $this->countingPolicy();

        $this->expectException(InvalidArgumentException::class);

        (new OpenId4VcHaipBatchCredentialProofEvaluator($policy))
            ->assess(
                $this->batch(['identity', 'address']),
                [$this->context('identity')]
            );
    }

    public function testExtraContextIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new OpenId4VcHaipBatchCredentialProofEvaluator(
            $this->countingPolicy()
        ))->assess(
            $this->batch(['identity']),
            [
                $this->context('identity'),
                $this->context('address'),
            ]
        );
    }

    public function testConfigurationMismatchIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new OpenId4VcHaipBatchCredentialProofEvaluator(
            $this->countingPolicy()
        ))->assess(
            $this->batch(['identity', 'address']),
            [
                $this->context('identity'),
                $this->context('other'),
            ]
        );
    }

    public function testNonListContextsAreRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new OpenId4VcHaipBatchCredentialProofEvaluator(
            $this->countingPolicy()
        ))->assess(
            $this->batch(['identity']),
            [
                1 => $this->context('identity'),
            ]
        );
    }

    public function testMatchingIssuanceIssuerIsAccepted(): void
    {
        $result = (new OpenId4VcHaipBatchCredentialProofEvaluator(
            $this->countingPolicy()
        ))->assessForIssuance(
            $this->batch(['identity', 'address']),
            new CredentialIssuanceContext(
                'https://issuer.example',
                'subject-1',
                'client-1'
            ),
            [
                $this->context('identity'),
                $this->context('address'),
            ]
        );

        self::assertTrue($result->conformant());
        self::assertSame(2, $result->count());
    }

    public function testDifferentIssuanceIssuerIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'HAIP batch credential issuer mismatch.'
        );

        (new OpenId4VcHaipBatchCredentialProofEvaluator(
            $this->countingPolicy()
        ))->assessForIssuance(
            $this->batch(['identity']),
            new CredentialIssuanceContext(
                'https://other-issuer.example',
                'subject-1',
                'client-1'
            ),
            [$this->context('identity')]
        );
    }

    public function testIssuerMismatchInLaterContextIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $differentIssuer = new OpenId4VcHaipCredentialProofContext(
            issuerIdentifier: 'https://other-issuer.example',
            credentialConfigurationId: 'address',
            requestedKeyReferences: ['key-2'],
            proofVerified: true,
            nonceVerified: true,
            replaySafe: true,
            keyAttestationRequired: false
        );

        (new OpenId4VcHaipBatchCredentialProofEvaluator(
            $this->countingPolicy()
        ))->assessForIssuance(
            $this->batch(['identity', 'address']),
            new CredentialIssuanceContext(
                'https://issuer.example',
                'subject-1',
                'client-1'
            ),
            [
                $this->context('identity'),
                $differentIssuer,
            ]
        );
    }

    public function testIssuanceEvaluationRejectsMissingContext(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'HAIP batch requests and proof contexts must correspond.'
        );

        (new OpenId4VcHaipBatchCredentialProofEvaluator(
            $this->countingPolicy()
        ))->assessForIssuance(
            $this->batch(['identity', 'address']),
            new CredentialIssuanceContext(
                'https://issuer.example',
                'subject-1',
                'client-1'
            ),
            [$this->context('identity')]
        );
    }
    /**
     * @param non-empty-list<string> $configurations
     */
    private function batch(array $configurations): BatchCredentialIssuanceRequest
    {
        $requests = [];

        foreach ($configurations as $configuration) {
            $requests[] = new CredentialIssuanceRequest($configuration);
        }

        return new BatchCredentialIssuanceRequest($requests);
    }

    private function context(string $configuration): OpenId4VcHaipCredentialProofContext
    {
        return new OpenId4VcHaipCredentialProofContext(
            issuerIdentifier: 'https://issuer.example',
            credentialConfigurationId: $configuration,
            requestedKeyReferences: ['key-1'],
            proofVerified: true,
            nonceVerified: true,
            replaySafe: true,
            keyAttestationRequired: false
        );
    }

    private function countingPolicy(): OpenId4VcHaipCredentialProofPolicyInterface
    {
        return new class implements OpenId4VcHaipCredentialProofPolicyInterface {
            public function assess(
                OpenId4VcHaipCredentialProofContext $context
            ): OpenId4VcHaipCredentialProofAssessment {
                return new OpenId4VcHaipCredentialProofAssessment(true);
            }
        };
    }
}