<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\AuthorizationServer;

use InvalidArgumentException;

final readonly class OAuthUserCode
{
    public function __construct(private string $value)
    {
        if (trim($this->value) === '') {
            throw new InvalidArgumentException(
                'OAuth user code is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
