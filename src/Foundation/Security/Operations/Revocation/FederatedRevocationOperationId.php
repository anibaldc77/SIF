<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Operations\Revocation;

use InvalidArgumentException;

final readonly class FederatedRevocationOperationId
{
    public function __construct(private string $value)
    {
        if (
            strlen($this->value) < 16
            || strlen($this->value) > 160
            || preg_match('/^[A-Za-z0-9._:-]+$/', $this->value) !== 1
        ) {
            throw new InvalidArgumentException(
                'Federated revocation operation id is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
