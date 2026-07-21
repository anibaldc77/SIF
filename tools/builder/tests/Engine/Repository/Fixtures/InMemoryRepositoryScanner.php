<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Engine\Repository\Fixtures;

use Sif\Builder\Metadata\MetadataScanResult;
use Sif\Builder\Metadata\RepositoryScannerInterface;

final readonly class InMemoryRepositoryScanner implements RepositoryScannerInterface
{
    public function __construct(private MetadataScanResult $result)
    {
    }

    public function scan(string $root): MetadataScanResult
    {
        return $this->result;
    }
}
