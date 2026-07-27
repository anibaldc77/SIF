<?php

declare(strict_types=1);

namespace Sif\Foundation\Container;

final readonly class TaggedService
{
    public function __construct(
        private ServiceIdentifier $identifier,
        private ServiceTag $tag,
        private int $registrationOrder,
    ) {
    }

    public function identifier(): ServiceIdentifier
    {
        return $this->identifier;
    }

    public function tag(): ServiceTag
    {
        return $this->tag;
    }

    public function registrationOrder(): int
    {
        return $this->registrationOrder;
    }
}
