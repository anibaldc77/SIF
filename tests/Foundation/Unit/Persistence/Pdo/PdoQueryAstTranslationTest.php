<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Persistence\Pdo;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Persistence\Pagination;
use Sif\Foundation\Persistence\Pdo\Ast\PdoSqlComparisonPredicate;
use Sif\Foundation\Persistence\Pdo\Ast\PdoSqlInPredicate;
use Sif\Foundation\Persistence\Pdo\Ast\PdoSqlLikePredicate;
use Sif\Foundation\Persistence\Pdo\Ast\PdoSqlNullPredicate;
use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlIdentifier;
use Sif\Foundation\Persistence\Pdo\Translation\PdoParameterNameGenerator;
use Sif\Foundation\Persistence\Pdo\Translation\PdoQueryTranslator;
use Sif\Foundation\Persistence\Projection;
use Sif\Foundation\Persistence\Query;
use Sif\Foundation\Persistence\QueryCriterion;
use Sif\Foundation\Persistence\QueryOperator;
use Sif\Foundation\Persistence\SortDirection;
use Sif\Foundation\Persistence\SortField;

final class PdoQueryAstTranslationTest extends TestCase
{
    public function testTranslatesProviderNeutralQueryIntoImmutableSelectAst(): void
    {
        $query = (new Query())
            ->withCriterion(new QueryCriterion('tenant_id', QueryOperator::Equal, 12))
            ->withCriterion(new QueryCriterion('deleted_at', QueryOperator::IsNull))
            ->withSortField(new SortField('created_at', SortDirection::Descending))
            ->withPagination(new Pagination(3, 25))
            ->withProjection(new Projection(['id', 'name', 'id']));

        $ast = (new PdoQueryTranslator())->translate(
            new PdoSqlIdentifier('public.accounts'),
            $query,
        );

        self::assertSame('public.accounts', $ast->source()->value());
        self::assertFalse($ast->projection()->selectsAll());
        self::assertSame(['id', 'name'], array_map(
            static fn (PdoSqlIdentifier $field): string => $field->value(),
            $ast->projection()->fields(),
        ));
        self::assertCount(2, $ast->criteria()->predicates());
        self::assertInstanceOf(PdoSqlComparisonPredicate::class, $ast->criteria()->predicates()[0]);
        self::assertInstanceOf(PdoSqlNullPredicate::class, $ast->criteria()->predicates()[1]);
        $pagination = $ast->pagination();
        self::assertNotNull($pagination);
        self::assertSame(25, $pagination->limit());
        self::assertSame(50, $pagination->offset());
        self::assertSame(SortDirection::Descending, $ast->sortTerms()[0]->direction());
        self::assertCount(1, $ast->parameters());
    }

    public function testInCriteriaCreateOneUniqueParameterPerValue(): void
    {
        $query = (new Query())->withCriterion(
            new QueryCriterion('status', QueryOperator::In, ['draft', 'active', 'closed']),
        );

        $ast = (new PdoQueryTranslator())->translate(new PdoSqlIdentifier('records'), $query);
        $predicate = $ast->criteria()->predicates()[0];

        self::assertInstanceOf(PdoSqlInPredicate::class, $predicate);
        self::assertCount(3, $predicate->parameters());
        self::assertSame(
            ['p_status_0', 'p_status_1', 'p_status_2'],
            array_map(static fn ($parameter): string => $parameter->name(), $predicate->parameters()->all()),
        );
    }

    public function testLikeTranslationEscapesUserWildcardsBeforeAddingPattern(): void
    {
        $query = (new Query())->withCriterion(
            new QueryCriterion('name', QueryOperator::Contains, '50%_done\\path'),
        );

        $ast = (new PdoQueryTranslator())->translate(new PdoSqlIdentifier('records'), $query);
        $predicate = $ast->criteria()->predicates()[0];

        self::assertInstanceOf(PdoSqlLikePredicate::class, $predicate);
        self::assertSame('%50\\%\\_done\\\\path%', $predicate->parameter()->value());
        self::assertSame('\\', $predicate->escapeCharacter());
    }

    public function testEmptyProjectionRepresentsAllColumnsWithoutRawWildcardIdentifier(): void
    {
        $ast = (new PdoQueryTranslator())->translate(
            new PdoSqlIdentifier('records'),
            new Query(),
        );

        self::assertTrue($ast->projection()->selectsAll());
        self::assertSame([], $ast->projection()->fields());
    }

    public function testParameterNameGeneratorIsDeterministicAndCanonical(): void
    {
        $generator = new PdoParameterNameGenerator();

        self::assertSame('p_profile_name_0', $generator->next('profile.name'));
        self::assertSame('p_value_123_1', $generator->next('123'));
    }
}
