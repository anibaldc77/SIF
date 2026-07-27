<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

/**
 * @template T of object
 */
interface ReadRepositoryInterface extends RepositoryInterface
{
    /**
     * @return T|null
     */
    public function findById(string|int $identifier): ?object;

    /**
     * @return ResultSetInterface<T>
     */
    public function query(QueryInterface $query): ResultSetInterface;
}