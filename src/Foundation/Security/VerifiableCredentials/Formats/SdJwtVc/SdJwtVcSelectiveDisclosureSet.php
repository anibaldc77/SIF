<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Formats\SdJwtVc;

final readonly class SdJwtVcSelectiveDisclosureSet
{
    /**
     * @param list<SdJwtVcDisclosure> $disclosures
     * @param list<string> $undisclosedDigests
     */
    public function __construct(
        private array $disclosures = [],
        private array $undisclosedDigests = []
    ) {
    }

    /**
     * @return list<SdJwtVcDisclosure>
     */
    public function disclosures(): array
    {
        return $this->disclosures;
    }

    /**
     * @return list<string>
     */
    public function undisclosedDigests(): array
    {
        return $this->undisclosedDigests;
    }
}
