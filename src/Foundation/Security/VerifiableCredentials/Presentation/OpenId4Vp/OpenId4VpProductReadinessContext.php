<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp;

final readonly class OpenId4VpProductReadinessContext
{
    /**
     * @param list<string> $capabilities
     * @param list<string> $securityControls
     * @param list<string> $operationalControls
     */
    public function __construct(
        private array $capabilities = [],
        private array $securityControls = [],
        private array $operationalControls = []
    ) {
    }

    /** @return list<string> */
    public function capabilities(): array
    {
        return $this->capabilities;
    }

    /** @return list<string> */
    public function securityControls(): array
    {
        return $this->securityControls;
    }

    /** @return list<string> */
    public function operationalControls(): array
    {
        return $this->operationalControls;
    }
}
