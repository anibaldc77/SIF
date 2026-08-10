<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\HolderBindingAssessment;
use Sif\Foundation\Security\VerifiableCredentials\HolderBindingContext;
use Sif\Foundation\Security\VerifiableCredentials\VerifiablePresentation;

interface HolderBindingVerifierInterface
{
    public function verify(
        VerifiablePresentation $presentation,
        HolderBindingContext $context
    ): HolderBindingAssessment;
}
