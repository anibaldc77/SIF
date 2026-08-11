<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Status\Issuer;

use DateTimeImmutable;
use InvalidArgumentException;
use Sif\Foundation\Security\VerifiableCredentials\Status\CredentialStatusMechanism;

final readonly class CredentialStatusPublicationPlan
{
    /**
     * @param list<CredentialStatusAllocation> $allocations
     */
    public function __construct(
        private string $statusListId,
        private CredentialStatusMechanism $mechanism,
        private string $version,
        private DateTimeImmutable $publishAt,
        private array $allocations = []
    ) {
        if (
            trim($this->statusListId) === ''
            || trim($this->version) === ''
        ) {
            throw new InvalidArgumentException(
                'Credential status publication plan is invalid.'
            );
        }
    }

    public function statusListId(): string
    {
        return $this->statusListId;
    }

    public function mechanism(): CredentialStatusMechanism
    {
        return $this->mechanism;
    }

    public function version(): string
    {
        return $this->version;
    }

    public function publishAt(): DateTimeImmutable
    {
        return $this->publishAt;
    }

    /**
     * @return list<CredentialStatusAllocation>
     */
    public function allocations(): array
    {
        return $this->allocations;
    }
}
