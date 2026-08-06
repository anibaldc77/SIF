<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization;

use Sif\Foundation\Security\Exceptions\InvalidAuthorizationRequestException;

final readonly class AuthorizationContext
{
    /** @var array<string, bool|int|string|null> */
    private array $attributes;

    /** @param array<string, bool|int|string|null> $attributes */
    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $name => $value) {
            if ($name === '' || preg_match('/^[a-z][a-z0-9._:-]{0,127}$/D', $name) !== 1) {
                throw new InvalidAuthorizationRequestException('Authorization context attribute name is invalid.');
            }
        }

        ksort($attributes);
        $this->attributes = $attributes;
    }

    /** @return array<string, bool|int|string|null> */
    public function attributes(): array { return $this->attributes; }
}
