<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

interface VerifiableCredentialCapabilityProviderInterface
{
    /**
     * @return list<string>
     */
    public function capabilities(): array;
}
