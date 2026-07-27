<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence;

final readonly class PersistenceCapabilities
{
    /**
     * @var array<string, PersistenceCapability>
     */
    private array $capabilities;

    /**
     * @param list<PersistenceCapability> $capabilities
     */
    public function __construct(array $capabilities = [])
    {
        $indexed = [];

        foreach ($capabilities as $capability) {
            $indexed[$capability->value] = $capability;
        }

        ksort($indexed, SORT_STRING);

        $this->capabilities = $indexed;
    }

    public static function none(): self
    {
        return new self();
    }

    /**
     * @param list<PersistenceCapability> $capabilities
     */
    public static function of(array $capabilities): self
    {
        return new self($capabilities);
    }

    public function supports(PersistenceCapability $capability): bool
    {
        return isset($this->capabilities[$capability->value]);
    }

    /**
     * @return list<PersistenceCapability>
     */
    public function all(): array
    {
        return array_values($this->capabilities);
    }

    public function count(): int
    {
        return count($this->capabilities);
    }

    public function isEmpty(): bool
    {
        return $this->capabilities === [];
    }

    public function with(PersistenceCapability $capability): self
    {
        return new self([...$this->all(), $capability]);
    }

    public function without(PersistenceCapability $capability): self
    {
        return new self(
            array_values(
                array_filter(
                    $this->all(),
                    static fn (
                        PersistenceCapability $candidate,
                    ): bool => $candidate !== $capability,
                ),
            ),
        );
    }
}
