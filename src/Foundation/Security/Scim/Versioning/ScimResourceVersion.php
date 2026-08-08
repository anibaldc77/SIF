<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Scim\Versioning;

use InvalidArgumentException;

final readonly class ScimResourceVersion
{
    public function __construct(private string $value)
    {
        if (
            $this->value === ''
            || strlen($this->value) > 255
            || preg_match('/[\x00-\x1F\x7F]/', $this->value) === 1
        ) {
            throw new InvalidArgumentException(
                'SCIM resource version is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function weakEtag(): string
    {
        return 'W/"' . addcslashes($this->value, "\\\"") . '"';
    }
}
