<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Scim\Versioning;

use InvalidArgumentException;

final readonly class ScimEntityTag
{
    public function __construct(
        private string $opaqueTag,
        private bool $weak = true
    ) {
        if (
            $this->opaqueTag === ''
            || strlen($this->opaqueTag) > 255
            || preg_match('/[\x00-\x1F\x7F]/', $this->opaqueTag) === 1
        ) {
            throw new InvalidArgumentException(
                'SCIM entity tag is invalid.'
            );
        }
    }

    public function opaqueTag(): string
    {
        return $this->opaqueTag;
    }

    public function weak(): bool
    {
        return $this->weak;
    }

    public function headerValue(): string
    {
        $prefix = $this->weak ? 'W/' : '';

        return $prefix . '"'
            . addcslashes($this->opaqueTag, "\\\"")
            . '"';
    }

    public function matches(ScimResourceVersion $version): bool
    {
        return hash_equals(
            $this->opaqueTag,
            $version->value()
        );
    }
}
