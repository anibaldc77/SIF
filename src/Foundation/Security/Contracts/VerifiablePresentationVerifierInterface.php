<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\VerifiablePresentation;

interface VerifiablePresentationVerifierInterface
{
    public function verify(
        string $serializedPresentation,
        string $expectedAudience,
        ?string $expectedNonce = null
    ): VerifiablePresentation;
}
