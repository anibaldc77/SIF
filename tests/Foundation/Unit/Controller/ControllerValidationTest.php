<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Controller\Argument\ActionArgumentSource;
use Sif\Foundation\Controller\Input\RequestInput;
use Sif\Foundation\Controller\Validation\Rules\InRule;
use Sif\Foundation\Controller\Validation\Rules\MaxRule;
use Sif\Foundation\Controller\Validation\Rules\MinRule;
use Sif\Foundation\Controller\Validation\Rules\NullableRule;
use Sif\Foundation\Controller\Validation\Rules\PatternRule;
use Sif\Foundation\Controller\Validation\Rules\RequiredRule;
use Sif\Foundation\Controller\Validation\Rules\TypeRule;
use Sif\Foundation\Controller\Validation\ValidationContext;
use Sif\Foundation\Controller\Validation\ValidationField;
use Sif\Foundation\Controller\Validation\ValidationSchema;
use Sif\Foundation\Controller\Validation\Validator;

final class ControllerValidationTest extends TestCase
{
    public function testValidatesMultipleSourcesAndReturnsDeterministicIssues(): void
    {
        $input = new RequestInput([
            'route' => ['id' => 0],
            'body' => ['email' => 'invalid', 'role' => 'owner'],
        ]);
        $schema = new ValidationSchema([
            new ValidationField('body.role', ActionArgumentSource::Body, 'role', [new InRule(['admin', 'user'])]),
            new ValidationField('route.id', ActionArgumentSource::Route, 'id', [new TypeRule('integer'), new MinRule(1)]),
            new ValidationField('body.email', ActionArgumentSource::Body, 'email', [new RequiredRule(), new TypeRule('string'), new PatternRule('/^[^@]+@[^@]+$/')]),
        ]);

        $result = (new Validator())->validate($schema, new ValidationContext($input));

        self::assertFalse($result->valid());
        self::assertSame(['body.email', 'body.role', 'route.id'], array_map(static fn ($issue): string => $issue->path(), $result->issues()));
        self::assertSame(['validation.pattern', 'validation.in', 'validation.min'], array_map(static fn ($issue): string => $issue->code(), $result->issues()));
    }

    public function testNullableValueSkipsRemainingRules(): void
    {
        $schema = new ValidationSchema([
            new ValidationField('body.name', ActionArgumentSource::Body, 'name', [new NullableRule(), new TypeRule('string'), new MinRule(3)]),
        ]);
        $result = (new Validator())->validate($schema, new ValidationContext(new RequestInput(['body' => ['name' => null]])));
        self::assertTrue($result->valid());
    }

    public function testRequiredAndMaximumRulesCanProduceMultipleIssues(): void
    {
        $schema = new ValidationSchema([
            new ValidationField('body.name', ActionArgumentSource::Body, 'name', [new RequiredRule(), new TypeRule('string'), new MaxRule(3)]),
        ]);
        $validator = new Validator();
        self::assertSame('validation.required', $validator->validate($schema, new ValidationContext(new RequestInput()))->issues()[0]->code());
        self::assertSame('validation.max', $validator->validate($schema, new ValidationContext(new RequestInput(['body' => ['name' => 'long']])))->issues()[0]->code());
    }

    public function testSchemaRejectsDuplicatePaths(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ValidationSchema([
            new ValidationField('body.name', ActionArgumentSource::Body, 'name', []),
            new ValidationField('body.name', ActionArgumentSource::Body, 'other', []),
        ]);
    }
}
