<?php

declare(strict_types=1);

namespace Sif\Foundation\Container;

final readonly class ResolutionPath
{
    /**
     * @var list<ServiceIdentifier>
     */
    private array $identifiers;

    /**
     * @param list<ServiceIdentifier> $identifiers
     */
    public function __construct(array $identifiers = [])
    {
        $this->identifiers = array_values($identifiers);
    }

    /**
     * @return list<ServiceIdentifier>
     */
    public function all(): array
    {
        return $this->identifiers;
    }

    public function isEmpty(): bool
    {
        return $this->identifiers === [];
    }

    public function count(): int
    {
        return count($this->identifiers);
    }

    public function contains(ServiceIdentifier $identifier): bool
    {
        foreach ($this->identifiers as $candidate) {
            if ($candidate->equals($identifier)) {
                return true;
            }
        }

        return false;
    }

    public function append(ServiceIdentifier $identifier): self
    {
        return new self([...$this->identifiers, $identifier]);
    }

    public function format(string $separator = ' -> '): string
    {
        return implode(
            $separator,
            array_map(
                static fn (
                    ServiceIdentifier $identifier,
                ): string => $identifier->value(),
                $this->identifiers,
            ),
        );
    }
}
