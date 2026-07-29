<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Filesystem;

use Sif\Foundation\Resources\Exceptions\DuplicateResourceRootException;
use Sif\Foundation\Resources\Exceptions\ResourceRootNotFoundException;
use Sif\Foundation\Resources\ResourceRootIdentifier;

final class AuthorizedResourceRootCollection
{
    /** @var array<string, AuthorizedResourceRoot> */
    private array $roots = [];

    public function add(AuthorizedResourceRoot $root): void
    {
        $key = $root->identifier()->value();

        if (isset($this->roots[$key])) {
            throw new DuplicateResourceRootException(sprintf('Authorized resource root "%s" is already registered.', $key));
        }

        $this->roots[$key] = $root;
    }

    public function has(ResourceRootIdentifier $identifier): bool
    {
        return isset($this->roots[$identifier->value()]);
    }

    public function get(ResourceRootIdentifier $identifier): AuthorizedResourceRoot
    {
        $key = $identifier->value();

        if (!isset($this->roots[$key])) {
            throw new ResourceRootNotFoundException(sprintf('Authorized resource root "%s" was not found.', $key));
        }

        return $this->roots[$key];
    }

    /** @return list<AuthorizedResourceRoot> */
    public function all(): array
    {
        return array_values($this->roots);
    }
}
