<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization;

use Sif\Foundation\Security\Exceptions\InvalidAuthorizationRequestException;

final readonly class AuthorizationAction
{
    public function __construct(private string $value)
    {
        if ($value === '' || preg_match('/^[a-z][a-z0-9._:-]{0,127}$/D', $value) !== 1) {
            throw new InvalidAuthorizationRequestException('Authorization action must be a stable lowercase identifier.');
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
