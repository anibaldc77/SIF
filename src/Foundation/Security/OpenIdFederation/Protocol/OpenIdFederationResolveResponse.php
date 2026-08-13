<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OpenIdFederation\Protocol;

use InvalidArgumentException;

final readonly class OpenIdFederationResolveResponse
{
    /**
     * @param array<string, mixed> $resolvedMetadata
     * @param list<string> $trustChainStatementIds
     * @param list<string> $verifiedTrustMarkIds
     */
    public function __construct(
        private string $subjectEntityId,
        private string $trustAnchorEntityId,
        private array $resolvedMetadata,
        private array $trustChainStatementIds,
        private array $verifiedTrustMarkIds = []
    ) {
        if (
            trim($this->subjectEntityId) === ''
            || trim($this->trustAnchorEntityId) === ''
        ) {
            throw new InvalidArgumentException(
                'OpenID Federation resolve response is invalid.'
            );
        }
    }

    public function subjectEntityId(): string
    {
        return $this->subjectEntityId;
    }

    public function trustAnchorEntityId(): string
    {
        return $this->trustAnchorEntityId;
    }

    /** @return array<string, mixed> */
    public function resolvedMetadata(): array
    {
        return $this->resolvedMetadata;
    }

    /** @return list<string> */
    public function trustChainStatementIds(): array
    {
        return $this->trustChainStatementIds;
    }

    /** @return list<string> */
    public function verifiedTrustMarkIds(): array
    {
        return $this->verifiedTrustMarkIds;
    }
}
