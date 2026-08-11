<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Status\Issuer;

use DateTimeImmutable;

final readonly class CredentialStatusPublicationResult
{
    /**
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $published,
        private string $statusListId,
        private string $version,
        private DateTimeImmutable $publishedAt,
        private array $warnings = []
    ) {
    }

    public function published(): bool
    {
        return $this->published;
    }

    public function statusListId(): string
    {
        return $this->statusListId;
    }

    public function version(): string
    {
        return $this->version;
    }

    public function publishedAt(): DateTimeImmutable
    {
        return $this->publishedAt;
    }

    /** @return list<string> */
    public function warnings(): array
    {
        return $this->warnings;
    }
}
