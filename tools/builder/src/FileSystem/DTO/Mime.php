<?php
declare(strict_types=1);

namespace Sif\Builder\FileSystem\DTO;

final readonly class Mime
{
    public function __construct(public string $value) {}
}
