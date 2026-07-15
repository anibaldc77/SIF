<?php
declare(strict_types=1);

namespace Sif\Builder\FileSystem\DTO;

final readonly class Checksum
{
    public function __construct(public string $algorithm, public string $value) {}
}
