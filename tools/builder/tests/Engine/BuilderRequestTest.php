<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Engine;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sif\Builder\Engine\BuilderRequest;
use Sif\Builder\Engine\Exception\InvalidBuilderRequestException;
use Sif\Builder\Engine\ExecutionPolicy;

final class BuilderRequestTest extends TestCase
{
    public function testNormalizesPathsProfileAndExtensionIdentifiers(): void
    {
        $request = new BuilderRequest(
            repositoryRoot: ' D:\\SIF\\ ',
            profile: ' CI.Full ',
            outputRoot: 'D:\\SIF\\build\\',
            policy: ExecutionPolicy::LENIENT,
            enabledAnalyzers: [' Reference.Broken ', 'repository.metadata'],
            enabledGenerators: [' Docs.Index '],
        );

        self::assertSame('D:/SIF', $request->repositoryRoot);
        self::assertSame('ci.full', $request->profile);
        self::assertSame('D:/SIF/build', $request->outputRoot);
        self::assertSame(ExecutionPolicy::LENIENT, $request->policy);
        self::assertSame(['reference.broken', 'repository.metadata'], $request->enabledAnalyzers);
        self::assertSame(['docs.index'], $request->enabledGenerators);
    }

    #[DataProvider('invalidRequests')]
    public function testRejectsInvalidRequest(callable $factory): void
    {
        $this->expectException(InvalidBuilderRequestException::class);
        $factory();
    }

    /** @return iterable<string, array{callable(): BuilderRequest}> */
    public static function invalidRequests(): iterable
    {
        yield 'empty repository root' => [static fn (): BuilderRequest => new BuilderRequest('')];
        yield 'invalid profile' => [static fn (): BuilderRequest => new BuilderRequest('/repo', 'invalid profile')];
        yield 'duplicate analyzer after normalization' => [
            static fn (): BuilderRequest => new BuilderRequest('/repo', enabledAnalyzers: ['a.b', ' A.B ']),
        ];
        yield 'invalid generator identifier' => [
            static fn (): BuilderRequest => new BuilderRequest('/repo', enabledGenerators: ['generator/value']),
        ];
    }
}
