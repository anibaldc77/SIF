<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\CredentialFormat;

interface CredentialFormatRegistryInterface
{
    public function has(
        CredentialFormat $format
    ): bool;

    public function handler(
        CredentialFormat $format
    ): CredentialFormatHandlerInterface;
}
