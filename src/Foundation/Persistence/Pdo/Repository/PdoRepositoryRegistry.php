<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Repository;

use Sif\Foundation\Persistence\Pdo\Exception\InvalidPdoRepositoryDefinitionException;

final readonly class PdoRepositoryRegistry
{
    /** @var array<string, PdoManagedRepository> */
    private array $repositories;

    /** @param iterable<PdoManagedRepository> $repositories */
    public function __construct(iterable $repositories)
    {
        $indexed = [];
        foreach ($repositories as $repository) {
            if (!$repository instanceof PdoManagedRepository) {
                throw new InvalidPdoRepositoryDefinitionException('PDO repository registry accepts write repositories only.');
            }

            $type = $repository->managedType();
            if (isset($indexed[$type])) {
                throw new InvalidPdoRepositoryDefinitionException('Managed types must be unique in the PDO repository registry.');
            }
            $indexed[$type] = $repository;
        }
        $this->repositories = $indexed;
    }

    public function for(object $object): PdoManagedRepository
    {
        foreach ($this->repositories as $type => $repository) {
            if ($repository->supports($object)) {
                return $repository;
            }
        }

        throw new InvalidPdoRepositoryDefinitionException(
            sprintf('No PDO repository is registered for object type "%s".', $object::class),
        );
    }

    public function count(): int
    {
        return count($this->repositories);
    }
}
