<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use InvalidArgumentException;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipBatchCredentialProofAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipCredentialProofAssessment;

final class OpenId4VcHaipBatchCredentialProofAssessmentTest extends TestCase
{
    public function testAllConformantAssessmentsProduceConformantBatch(): void
    {
        $first = new OpenId4VcHaipCredentialProofAssessment(true);
        $second = new OpenId4VcHaipCredentialProofAssessment(true);

        $batch = new OpenId4VcHaipBatchCredentialProofAssessment([
            $first,
            $second,
        ]);

        self::assertTrue($batch->conformant());
        self::assertSame(2, $batch->count());
        self::assertSame($first, $batch->assessmentAt(0));
        self::assertSame($second, $batch->assessmentAt(1));
    }

    public function testOneNonconformantAssessmentRejectsEntireBatch(): void
    {
        $batch = new OpenId4VcHaipBatchCredentialProofAssessment([
            new OpenId4VcHaipCredentialProofAssessment(true),
            new OpenId4VcHaipCredentialProofAssessment(
                false,
                ['key_attestation_key_coverage_incomplete']
            ),
        ]);

        self::assertFalse($batch->conformant());
        self::assertSame(
            ['key_attestation_key_coverage_incomplete'],
            $batch->assessmentAt(1)->violations()
        );
    }

    public function testViolationsOverrideConformantFlag(): void
    {
        $batch = new OpenId4VcHaipBatchCredentialProofAssessment([
            new OpenId4VcHaipCredentialProofAssessment(
                true,
                ['credential_proof_not_verified']
            ),
        ]);

        self::assertFalse($batch->conformant());
    }

    public function testWarningsRemainAssociatedWithOriginalAssessment(): void
    {
        $batch = new OpenId4VcHaipBatchCredentialProofAssessment([
            new OpenId4VcHaipCredentialProofAssessment(
                true,
                [],
                ['ecosystem_recommendation']
            ),
        ]);

        self::assertTrue($batch->conformant());
        self::assertSame(
            ['ecosystem_recommendation'],
            $batch->assessmentAt(0)->warnings()
        );
    }

    public function testEmptyBatchIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OpenId4VcHaipBatchCredentialProofAssessment([]);
    }

    public function testNonListAssessmentsAreRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OpenId4VcHaipBatchCredentialProofAssessment([
            1 => new OpenId4VcHaipCredentialProofAssessment(true),
        ]);
    }

    public function testInvalidAssessmentIndexIsRejected(): void
    {
        $batch = new OpenId4VcHaipBatchCredentialProofAssessment([
            new OpenId4VcHaipCredentialProofAssessment(true),
        ]);

        $this->expectException(OutOfBoundsException::class);

        $batch->assessmentAt(1);
    }

    public function testAssessmentOrderIsPreserved(): void
    {
        $first = new OpenId4VcHaipCredentialProofAssessment(true);
        $second = new OpenId4VcHaipCredentialProofAssessment(false, ['failure']);

        $batch = new OpenId4VcHaipBatchCredentialProofAssessment([
            $first,
            $second,
        ]);

        self::assertSame([$first, $second], $batch->assessments());
    }
}