<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Countable;
use IteratorAggregate;

/** @extends IteratorAggregate<string, list<string>> */
interface HeaderBagInterface extends IteratorAggregate, Countable
{
    public function has(string $name): bool;
    /** @return list<string> */
    public function values(string $name): array;
    public function line(string $name): string;
    /** @return array<string, list<string>> */
    public function all(): array;
    /** @param string|list<string> $values */
    public function with(string $name, string|array $values): self;
    /** @param string|list<string> $values */
    public function withAdded(string $name, string|array $values): self;
    public function without(string $name): self;
}
