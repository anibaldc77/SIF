<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\CredentialEnvelope;
use Sif\Foundation\Security\VerifiableCredentials\CredentialFormat;
use Sif\Foundation\Security\VerifiableCredentials\VerifiableCredential;

interface CredentialFormatHandlerInterface
{
    public function supports(
        CredentialFormat $format
    ): bool;

    public function parse(
        CredentialEnvelope $envelope
    ): VerifiableCredential;
}
