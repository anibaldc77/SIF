<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceGrantType;

interface CredentialIssuanceGrantResolverInterface
{
    public function resolve(string $grantType): CredentialIssuanceGrantType;
}
