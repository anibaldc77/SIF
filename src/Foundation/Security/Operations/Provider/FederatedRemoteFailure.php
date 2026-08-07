<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Operations\Provider;

use InvalidArgumentException;

final readonly class FederatedRemoteFailure
{
    public function __construct(
        private FederatedRemoteFailureKind $kind,
        private string $code,
        private ?string $detail = null
    ) {
        if (
            $this->code === ''
            || strlen($this->code) > 160
            || preg_match('/^[a-z0-9._:-]+$/', $this->code) !== 1
        ) {
            throw new InvalidArgumentException(
                'Federated remote failure code is invalid.'
            );
        }

        if ($this->detail !== null && strlen($this->detail) > 1000) {
            throw new InvalidArgumentException(
                'Federated remote failure detail is too long.'
            );
        }
    }

    public function kind(): FederatedRemoteFailureKind
    {
        return $this->kind;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function detail(): ?string
    {
        return $this->detail;
    }

    public function retryable(): bool
    {
        return $this->kind === FederatedRemoteFailureKind::Transient;
    }
}
