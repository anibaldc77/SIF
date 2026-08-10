<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\SelectiveDisclosureAssessment;
use Sif\Foundation\Security\VerifiableCredentials\SelectiveDisclosureRequest;
use Sif\Foundation\Security\VerifiableCredentials\VerifiableCredential;

interface SelectiveDisclosurePolicyInterface
{
    public function validate(
        VerifiableCredential $credential
    ): void;

    public function assess(
        VerifiableCredential $credential,
        SelectiveDisclosureRequest $request
    ): SelectiveDisclosureAssessment;
}
