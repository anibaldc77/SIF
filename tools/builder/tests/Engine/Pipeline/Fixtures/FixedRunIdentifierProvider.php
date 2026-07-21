<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Engine\Pipeline\Fixtures;

use Sif\Builder\Engine\Contract\RunIdentifierProviderInterface;

final class FixedRunIdentifierProvider implements RunIdentifierProviderInterface
{
    public function __construct(private readonly string $identifier = 'test-run')
    {
    }

    public function next(): string
    {
        return $this->identifier;
    }
}
