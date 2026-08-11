<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Status\Issuer;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class CredentialStatusLifecycleTransition
{
    public function __construct(
        private CredentialStatusLifecycleState $from,
        private CredentialStatusLifecycleState $to,
        private DateTimeImmutable $effectiveAt,
        private string $reason
    ) {
        if (trim($this->reason) === '') {
            throw new InvalidArgumentException(
                'Credential status lifecycle transition reason is invalid.'
            );
        }
    }

    public function from(): CredentialStatusLifecycleState
    {
        return $this->from;
    }

    public function to(): CredentialStatusLifecycleState
    {
        return $this->to;
    }

    public function effectiveAt(): DateTimeImmutable
    {
        return $this->effectiveAt;
    }

    public function reason(): string
    {
        return $this->reason;
    }
}
