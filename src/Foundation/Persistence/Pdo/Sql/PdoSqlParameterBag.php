<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Sql;

use Countable;
use IteratorAggregate;
use Sif\Foundation\Persistence\Pdo\Exception\InvalidPdoSqlParameterBagException;
use Traversable;

/** @implements IteratorAggregate<int, PdoSqlParameter> */
final readonly class PdoSqlParameterBag implements Countable, IteratorAggregate
{
    /** @var list<PdoSqlParameter> */
    private array $parameters;

    /** @param iterable<PdoSqlParameter> $parameters */
    public function __construct(iterable $parameters = [])
    {
        $values = [];
        $names = [];
        foreach ($parameters as $parameter) {
            if (!$parameter instanceof PdoSqlParameter) {
                throw new InvalidPdoSqlParameterBagException('Parameter bag must contain PdoSqlParameter values.');
            }
            if (isset($names[$parameter->name()])) {
                throw new InvalidPdoSqlParameterBagException('Parameter names must be unique.');
            }
            $names[$parameter->name()] = true;
            $values[] = $parameter;
        }
        $this->parameters = $values;
    }

    public function with(PdoSqlParameter $parameter): self
    {
        return new self([...$this->parameters, $parameter]);
    }

    public function has(string $name): bool
    {
        $name = ltrim(trim($name), ':');
        foreach ($this->parameters as $parameter) {
            if ($parameter->name() === $name) { return true; }
        }
        return false;
    }

    /** @return list<PdoSqlParameter> */ public function all(): array { return $this->parameters; }
    public function count(): int { return count($this->parameters); }
    /** @return Traversable<int, PdoSqlParameter> */ public function getIterator(): Traversable { yield from $this->parameters; }
    /** @return list<array{name: string, placeholder: string, type: int}> */ public function summary(): array { return array_map(static fn (PdoSqlParameter $p): array => $p->summary(), $this->parameters); }
}
