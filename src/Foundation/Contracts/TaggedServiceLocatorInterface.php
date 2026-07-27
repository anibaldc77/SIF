<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Container\TaggedService;

interface TaggedServiceLocatorInterface
{
    /**
     * @return list<TaggedService>
     */
    public function tagged(string $tag): array;

    /**
     * @return list<object>
     */
    public function resolveTagged(string $tag): array;
}
