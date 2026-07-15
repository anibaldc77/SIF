<?php
declare(strict_types=1);

namespace Sif\Builder\FileSystem\DTO;

use DateTimeImmutable;

final readonly class FileInfo
{
    public function __construct(public string $path, public int $size, public DateTimeImmutable $modified, public int $permissions, public Mime $mime) {}
}
