<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\PresentationRequest;

interface PresentationRequestFactoryInterface
{
    /**
     * @param list<string> $requestedCredentialTypes
     * @param list<string> $requestedClaims
     */
    public function create(
        string $requestId,
        string $audience,
        string $nonce,
        array $requestedCredentialTypes = [],
        array $requestedClaims = [],
        ?string $state = null
    ): PresentationRequest;
}
