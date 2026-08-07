<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Operations\Revocation;

use InvalidArgumentException;

final readonly class FederatedRevocationReason
{
    public function __construct(
        private string $code,
        private ?string $detail = null
    ) {
        if (
            $this->code === ''
            || strlen($this->code) > 120
            || preg_match('/^[a-z0-9._:-]+$/', $this->code) !== 1
        ) {
            throw new InvalidArgumentException(
                'Federated revocation reason code is invalid.'
            );
        }

        if ($this->detail !== null && strlen($this->detail) > 1000) {
            throw new InvalidArgumentException(
                'Federated revocation reason detail is too long.'
            );
        }
    }

    public function code(): string
    {
        return $this->code;
    }

    public function detail(): ?string
    {
        return $this->detail;
    }
}
