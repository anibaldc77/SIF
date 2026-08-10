<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\VerifiableCredential;

interface VerifiableCredentialVerifierInterface
{
    public function verify(
        string $serializedCredential
    ): VerifiableCredential;
}
