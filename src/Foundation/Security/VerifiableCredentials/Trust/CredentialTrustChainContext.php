<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Trust;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class CredentialTrustChainContext
{
    /**
     * @param list<string> $trustedAnchorIds
     */
    public function __construct(
        private DateTimeImmutable $evaluatedAt,
        private array $trustedAnchorIds,
        private int $maximumDepth = 8
    ) {
        if (
            $this->trustedAnchorIds === []
            || $this->maximumDepth < 1
        ) {
            throw new InvalidArgumentException(
                'Credential trust chain context is invalid.'
            );
        }
    }

    public function evaluatedAt(): DateTimeImmutable
    {
        return $this->evaluatedAt;
    }

    /** @return list<string> */
    public function trustedAnchorIds(): array
    {
        return $this->trustedAnchorIds;
    }

    public function maximumDepth(): int
    {
        return $this->maximumDepth;
    }
}
