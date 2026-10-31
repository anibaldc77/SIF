<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance;

use InvalidArgumentException;
use OutOfBoundsException;

final readonly class OpenId4VcHaipBatchCredentialProofAssessment
{
    /** @var non-empty-list<OpenId4VcHaipCredentialProofAssessment> */
    private array $assessments;

    /**
     * @param list<OpenId4VcHaipCredentialProofAssessment> $assessments
     */
    public function __construct(array $assessments)
    {
        if ($assessments === []) {
            throw new InvalidArgumentException(
                'HAIP batch proof assessment requires individual assessments.'
            );
        }

        if (!array_is_list($assessments)) {
            throw new InvalidArgumentException(
                'HAIP batch proof assessments must be an ordered list.'
            );
        }

        $this->assessments = $assessments;
    }

    /**
     * @return non-empty-list<OpenId4VcHaipCredentialProofAssessment>
     */
    public function assessments(): array
    {
        return $this->assessments;
    }

    public function assessmentAt(int $index): OpenId4VcHaipCredentialProofAssessment
    {
        if (!isset($this->assessments[$index])) {
            throw new OutOfBoundsException(
                'HAIP batch proof assessment index is invalid.'
            );
        }

        return $this->assessments[$index];
    }

    public function count(): int
    {
        return count($this->assessments);
    }

    public function conformant(): bool
    {
        foreach ($this->assessments as $assessment) {
            if (!$assessment->conformant()) {
                return false;
            }
        }

        return true;
    }
}