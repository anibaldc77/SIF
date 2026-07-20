<?php

declare(strict_types=1);

namespace Sif\Builder\Reference\Resolution;

use Sif\Builder\Reference\ReferenceType;

final readonly class ResolutionStatistics
{
    public int $total;
    public int $resolved;
    public int $broken;

    /** @var array<string, int> */
    public array $byType;

    public function __construct(ResolutionResult $result)
    {
        $byType = array_fill_keys(
            array_map(static fn (ReferenceType $type): string => $type->value, ReferenceType::cases()),
            0,
        );

        foreach ($result->resolved as $item) {
            ++$byType[$item->reference->type->value];
        }

        foreach ($result->broken as $item) {
            ++$byType[$item->reference->type->value];
        }

        $this->total = $result->total();
        $this->resolved = $result->resolvedCount();
        $this->broken = $result->brokenCount();
        $this->byType = $byType;
    }

    public function resolutionRate(): float
    {
        if ($this->total === 0) {
            return 1.0;
        }

        return $this->resolved / $this->total;
    }
}
