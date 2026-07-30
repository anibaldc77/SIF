<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration;

use Sif\Foundation\Migration\Exceptions\InvalidMigrationRequestException;

final readonly class MigrationRequest
{
    private MigrationDirection $direction;

    private MigrationExecutionMode $mode;

    private ?MigrationId $target;

    private ?int $limit;

    /** @var list<string> */
    private array $tags;

    /**
     * @param iterable<string> $tags
     */
    public function __construct(
        MigrationDirection $direction,
        MigrationExecutionMode $mode,
        ?MigrationId $target = null,
        ?int $limit = null,
        iterable $tags = [],
    ) {
        if ($limit !== null && $limit < 1) {
            throw new InvalidMigrationRequestException('Migration request limit must be greater than zero.');
        }

        $normalizedTags = [];
        $seen = [];
        foreach ($tags as $tag) {
            if (!is_string($tag)) {
                throw new InvalidMigrationRequestException('Migration request tags must contain only strings.');
            }

            $tag = strtolower(trim($tag));
            if ($tag === '' || preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $tag) !== 1) {
                throw new InvalidMigrationRequestException('Migration request tag contains unsafe vocabulary.');
            }

            if (isset($seen[$tag])) {
                throw new InvalidMigrationRequestException(sprintf('Duplicate migration request tag "%s".', $tag));
            }

            $seen[$tag] = true;
            $normalizedTags[] = $tag;
        }

        $this->direction = $direction;
        $this->mode = $mode;
        $this->target = $target;
        $this->limit = $limit;
        $this->tags = $normalizedTags;
    }

    public function direction(): MigrationDirection
    {
        return $this->direction;
    }

    public function mode(): MigrationExecutionMode
    {
        return $this->mode;
    }

    public function target(): ?MigrationId
    {
        return $this->target;
    }

    public function limit(): ?int
    {
        return $this->limit;
    }

    /** @return list<string> */
    public function tags(): array
    {
        return $this->tags;
    }

    /**
     * @return array{
     *   direction: string,
     *   mode: string,
     *   target: string|null,
     *   limit: int|null,
     *   tags: list<string>
     * }
     */
    public function summary(): array
    {
        return [
            'direction' => $this->direction->value(),
            'mode' => $this->mode->value(),
            'target' => $this->target?->value(),
            'limit' => $this->limit,
            'tags' => $this->tags,
        ];
    }
}
