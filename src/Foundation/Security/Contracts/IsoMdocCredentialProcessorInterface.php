<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\CredentialEnvelope;
use Sif\Foundation\Security\VerifiableCredentials\Formats\CredentialFormatProcessingAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Formats\CredentialFormatProcessingContext;
use Sif\Foundation\Security\VerifiableCredentials\Formats\CredentialFormatProfile;

interface IsoMdocCredentialProcessorInterface
{
    public function assess(
        CredentialEnvelope $envelope,
        CredentialFormatProfile $profile,
        CredentialFormatProcessingContext $context
    ): CredentialFormatProcessingAssessment;
}
