<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp;

final readonly class OpenId4VpVerifierAuthenticationResult
{
    /**
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $authenticated,
        private ?OpenId4VpVerifierIdentity $identity = null,
        private array $warnings = []
    ) {
    }

    public function authenticated(): bool
    {
        return $this->authenticated;
    }

    public function identity(): ?OpenId4VpVerifierIdentity
    {
        return $this->identity;
    }

    /**
     * @return list<string>
     */
    public function warnings(): array
    {
        return $this->warnings;
    }
}
