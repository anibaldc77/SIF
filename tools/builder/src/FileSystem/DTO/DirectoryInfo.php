<?php
declare(strict_types=1);

namespace Sif\Builder\FileSystem\DTO;

use DateTimeImmutable;

final readonly class DirectoryInfo
{
    public function __construct(public string $path, public DateTimeImmutable $modified, public int $permissions) {}
}
