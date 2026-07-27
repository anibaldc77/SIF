<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

/**
 * @template T of object
 */
interface WriteRepositoryInterface extends RepositoryInterface
{
    /**
     * @param T $object
     */
    public function save(object $object): void;

    /**
     * @param T $object
     */
    public function remove(object $object): void;
}