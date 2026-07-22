<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Analyzer\RepositoryPolicy;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sif\Builder\Analyzer\RepositoryPolicy\Policy\RequiredCategoryPolicy;
use Sif\Builder\Analyzer\RepositoryPolicy\RepositoryPolicySet;

final class RepositoryPolicySetTest extends TestCase
{
    public function testOrdersPoliciesByStableIdentifier(): void
    {
        $set = new RepositoryPolicySet([
            new RequiredCategoryPolicy('repository.zeta', 'Policy'),
            new RequiredCategoryPolicy('repository.alpha', 'Constitution'),
        ]);

        self::assertSame(['repository.alpha', 'repository.zeta'], array_map(static fn ($rule): string => $rule->id(), $set->all()));
    }

    public function testRejectsDuplicateIdentifiers(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new RepositoryPolicySet([
            new RequiredCategoryPolicy('repository.required', 'Policy'),
            new RequiredCategoryPolicy('repository.required', 'Constitution'),
        ]);
    }
}
