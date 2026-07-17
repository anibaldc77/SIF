<?php

declare(strict_types=1);

namespace Sif\Builder\Metadata;

interface RepositoryScannerInterface
{
    public function scan(string $root): MetadataScanResult;
}
