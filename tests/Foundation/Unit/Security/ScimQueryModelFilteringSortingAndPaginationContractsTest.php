<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\ScimFilterParserInterface;
use Sif\Foundation\Security\Contracts\ScimQueryExecutorInterface;
use Sif\Foundation\Security\Scim\Filter\ScimComparisonFilter;
use Sif\Foundation\Security\Scim\Filter\ScimLogicalFilter;
use Sif\Foundation\Security\Scim\Filter\ScimNotFilter;
use Sif\Foundation\Security\Scim\ScimPagination;
use Sif\Foundation\Security\Scim\ScimQuery;
use Sif\Foundation\Security\Scim\ScimSort;

final class ScimQueryModelFilteringSortingAndPaginationContractsTest extends TestCase
{
    public function testComparisonFilterRepresentsAttributeOperatorAndValue(): void
    {
        $filter = new ScimComparisonFilter(
            'userName',
            'eq',
            'alice@example.com'
        );

        self::assertSame('userName', $filter->attributePath());
        self::assertSame('eq', $filter->operator());
        self::assertSame('alice@example.com', $filter->value());
    }

    public function testLogicalAndNotFiltersComposeAst(): void
    {
        $left = new ScimComparisonFilter('active', 'eq', true);
        $right = new ScimComparisonFilter('userName', 'sw', 'alice');

        $logical = new ScimLogicalFilter(
            'and',
            $left,
            new ScimNotFilter($right)
        );

        self::assertSame('and', $logical->operator());
        self::assertSame($left, $logical->left());
        self::assertInstanceOf(
            ScimNotFilter::class,
            $logical->right()
        );
    }

    public function testSortAndPaginationAreExplicitValueObjects(): void
    {
        $sort = new ScimSort(
            'userName',
            'descending'
        );
        $pagination = new ScimPagination(
            11,
            25
        );

        self::assertSame('userName', $sort->attributePath());
        self::assertSame('descending', $sort->order());
        self::assertSame(11, $pagination->startIndex());
        self::assertSame(25, $pagination->count());
    }

    public function testQueryCarriesProjectionSortPaginationAndFilter(): void
    {
        $filter = new ScimComparisonFilter(
            'active',
            'eq',
            true
        );

        $query = new ScimQuery(
            $filter,
            new ScimSort('userName'),
            new ScimPagination(1, 50),
            ['userName', 'active'],
            ['password']
        );

        self::assertSame($filter, $query->filter());
        self::assertSame(
            ['userName', 'active'],
            $query->attributes()
        );
        self::assertSame(
            ['password'],
            $query->excludedAttributes()
        );
    }

    public function testFilterParserAndExecutorContractsRemainNeutral(): void
    {
        foreach ([
            ScimFilterParserInterface::class,
            ScimQueryExecutorInterface::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('SQL', strtoupper($source));
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
        }
    }

    public function testQueryModelDoesNotParseOrExecuteTextInternally(): void
    {
        foreach ([
            ScimQuery::class,
            ScimComparisonFilter::class,
            ScimLogicalFilter::class,
            ScimNotFilter::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('preg_match', $source);
            self::assertStringNotContainsString('query(', strtolower($source));
            self::assertStringNotContainsString('execute(', strtolower($source));
        }
    }
}
