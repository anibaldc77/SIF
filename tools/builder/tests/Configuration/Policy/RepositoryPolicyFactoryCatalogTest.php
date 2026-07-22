<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Configuration\Policy;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Configuration\Policy\RepositoryPolicyFactoryCatalog;

final class RepositoryPolicyFactoryCatalogTest extends TestCase
{
    public function testBuiltInCatalogIsDeterministic(): void
    {
        self::assertSame(
            ['required.category', 'required.metadata'],
            RepositoryPolicyFactoryCatalog::builtIn()->identifiers(),
        );
    }
}
