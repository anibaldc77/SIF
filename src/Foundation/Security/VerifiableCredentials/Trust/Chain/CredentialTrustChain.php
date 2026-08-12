<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Trust\Chain;

use InvalidArgumentException;

final readonly class CredentialTrustChain
{
    /** @param list<CredentialTrustChainLink> $links */
    public function __construct(private array $links)
    {
        if ($this->links === []) {
            throw new InvalidArgumentException(
                'Credential trust chain cannot be empty.'
            );
        }
    }

    /** @return list<CredentialTrustChainLink> */
    public function links(): array
    {
        return $this->links;
    }

    public function depth(): int
    {
        return count($this->links);
    }

    public function leaf(): CredentialTrustChainLink
    {
        return $this->links[0];
    }

    public function root(): CredentialTrustChainLink
    {
        return $this->links[array_key_last($this->links)];
    }
}
