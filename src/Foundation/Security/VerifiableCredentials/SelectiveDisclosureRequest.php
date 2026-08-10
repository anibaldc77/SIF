<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials;

use InvalidArgumentException;

final readonly class SelectiveDisclosureRequest
{
    /**
     * @param list<string> $requiredClaims
     * @param list<string> $optionalClaims
     */
    public function __construct(
        private array $requiredClaims,
        private array $optionalClaims = []
    ) {
        if ($this->requiredClaims === []) {
            throw new InvalidArgumentException(
                'Selective disclosure request requires at least one claim.'
            );
        }
    }

    /** @return list<string> */
    public function requiredClaims(): array
    {
        return $this->requiredClaims;
    }

    /** @return list<string> */
    public function optionalClaims(): array
    {
        return $this->optionalClaims;
    }
}
