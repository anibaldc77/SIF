<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp;

use InvalidArgumentException;

final readonly class OpenId4VpResolvedPresentation
{
    /**
     * @param list<mixed> $presentations
     * @param list<string> $credentialIds
     */
    public function __construct(
        private array $presentations,
        private array $credentialIds = []
    ) {
        if ($this->presentations === []) {
            throw new InvalidArgumentException(
                'OpenID4VP resolved presentation is invalid.'
            );
        }
    }

    /**
     * @return list<mixed>
     */
    public function presentations(): array
    {
        return $this->presentations;
    }

    /**
     * @return list<string>
     */
    public function credentialIds(): array
    {
        return $this->credentialIds;
    }
}
