<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Persistence;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Exceptions\InvalidPaginationException;
use Sif\Foundation\Exceptions\InvalidProjectionException;
use Sif\Foundation\Exceptions\InvalidQueryCriterionException;
use Sif\Foundation\Exceptions\InvalidSortFieldException;
use Sif\Foundation\Persistence\Pagination;
use Sif\Foundation\Persistence\Projection;
use Sif\Foundation\Persistence\Query;
use Sif\Foundation\Persistence\QueryCriteria;
use Sif\Foundation\Persistence\QueryCriterion;
use Sif\Foundation\Persistence\QueryOperator;
use Sif\Foundation\Persistence\SortDirection;
use Sif\Foundation\Persistence\SortField;
use Sif\Foundation\Persistence\SortOrder;

final class QueryValueModelTest extends TestCase
{
    public function testCriterionPreservesFieldOperatorAndScalarValue(): void
    {
        $criterion = new QueryCriterion(
            'status',
            QueryOperator::Equal,
            'active',
        );

        self::assertSame('status', $criterion->field());
        self::assertSame(QueryOperator::Equal, $criterion->operator());
        self::assertSame('active', $criterion->value());
    }

    public function testNullOperatorsRejectExplicitValues(): void
    {
        $this->expectException(InvalidQueryCriterionException::class);

        new QueryCriterion(
            'deleted_at',
            QueryOperator::IsNull,
            'unexpected',
        );
    }

    public function testMembershipOperatorsRequireNonEmptyArrays(): void
    {
        $this->expectException(InvalidQueryCriterionException::class);

        new QueryCriterion(
            'status',
            QueryOperator::In,
            [],
        );
    }

    public function testScalarOperatorsRejectArrayValues(): void
    {
        $this->expectException(InvalidQueryCriterionException::class);

        new QueryCriterion(
            'status',
            QueryOperator::Equal,
            ['active'],
        );
    }

    public function testCriteriaCollectionIsImmutable(): void
    {
        $criteria = new QueryCriteria();
        $updated = $criteria->with(
            new QueryCriterion('status', QueryOperator::Equal, 'active'),
        );

        self::assertTrue($criteria->isEmpty());
        self::assertSame(0, $criteria->count());
        self::assertFalse($updated->isEmpty());
        self::assertSame(1, $updated->count());
    }

    public function testSortFieldAndOrderPreserveInsertionOrder(): void
    {
        $order = (new SortOrder())
            ->with(new SortField('created_at', SortDirection::Descending))
            ->with(new SortField('id'));

        self::assertSame('created_at', $order->all()[0]->field());
        self::assertSame(
            SortDirection::Descending,
            $order->all()[0]->direction(),
        );
        self::assertSame('id', $order->all()[1]->field());
        self::assertSame(
            SortDirection::Ascending,
            $order->all()[1]->direction(),
        );
    }

    public function testSortFieldRejectsEmptyField(): void
    {
        $this->expectException(InvalidSortFieldException::class);

        new SortField(' ');
    }

    public function testPaginationCalculatesOffsetAndNextPage(): void
    {
        $pagination = new Pagination(page: 3, perPage: 25);

        self::assertSame(3, $pagination->page());
        self::assertSame(25, $pagination->perPage());
        self::assertSame(50, $pagination->offset());
        self::assertSame(4, $pagination->next()->page());
        self::assertSame(25, $pagination->next()->perPage());
    }

    public function testPaginationRejectsInvalidPage(): void
    {
        $this->expectException(InvalidPaginationException::class);

        new Pagination(page: 0, perPage: 25);
    }

    public function testProjectionNormalizesDuplicateFields(): void
    {
        $projection = new Projection([
            'id',
            'status',
            'id',
        ]);

        self::assertSame(['id', 'status'], $projection->fields());
        self::assertTrue($projection->includes('id'));
        self::assertFalse($projection->includes('created_at'));
    }

    public function testProjectionRejectsEmptyField(): void
    {
        $this->expectException(InvalidProjectionException::class);

        new Projection(['id', ' ']);
    }

    public function testQueryCompositionIsImmutableAndTechnologyNeutral(): void
    {
        $base = new Query();

        $query = $base
            ->withCriterion(
                new QueryCriterion(
                    'status',
                    QueryOperator::Equal,
                    'active',
                ),
            )
            ->withSortField(
                new SortField(
                    'created_at',
                    SortDirection::Descending,
                ),
            )
            ->withPagination(
                Pagination::firstPage(20),
            )
            ->withProjection(
                new Projection(['id', 'status']),
            );

        self::assertTrue($base->criteria()->isEmpty());
        self::assertTrue($base->sortOrder()->isEmpty());
        self::assertNull($base->pagination());
        self::assertTrue($base->projection()->isEmpty());

        self::assertSame(1, $query->criteria()->count());
        self::assertSame(1, count($query->sortOrder()->all()));
        self::assertSame(20, $query->pagination()?->perPage());
        self::assertSame(['id', 'status'], $query->projection()->fields());
    }
}
