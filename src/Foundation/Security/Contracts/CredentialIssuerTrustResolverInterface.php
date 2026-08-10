<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

interface CredentialIssuerTrustResolverInterface
{
    public function isTrusted(
        string $issuer
    ): bool;
}
