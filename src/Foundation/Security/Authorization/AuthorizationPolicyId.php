<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization;

use Sif\Foundation\Security\Exceptions\InvalidAuthorizationPolicyException;

final readonly class AuthorizationPolicyId
{
    public function __construct(private string $value)
    {
        if ($value === '' || preg_match('/^[a-z][a-z0-9._:-]{0,127}$/D', $value) !== 1) {
            throw new InvalidAuthorizationPolicyException('Authorization policy identifier is invalid.');
        }
    }

    public function value(): string { return $this->value; }
}
