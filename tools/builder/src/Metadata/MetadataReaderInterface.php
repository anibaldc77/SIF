<?php

declare(strict_types=1);

namespace Sif\Builder\Metadata;

interface MetadataReaderInterface
{
    public function supports(string $path): bool;

    public function read(string $path): MetadataDocument;
}
