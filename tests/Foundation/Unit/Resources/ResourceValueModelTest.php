<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Resources;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Resources\Exceptions\InvalidResourceDescriptorException;
use Sif\Foundation\Resources\Exceptions\InvalidResourceIdentifierException;
use Sif\Foundation\Resources\Exceptions\InvalidResourceNamespaceException;
use Sif\Foundation\Resources\Exceptions\InvalidResourcePathException;
use Sif\Foundation\Resources\Exceptions\InvalidResourcePriorityException;
use Sif\Foundation\Resources\Exceptions\InvalidResourceTypeException;
use Sif\Foundation\Resources\ResourceDescriptor;
use Sif\Foundation\Resources\ResourceIdentifier;
use Sif\Foundation\Resources\ResourceNamespace;
use Sif\Foundation\Resources\ResourcePath;
use Sif\Foundation\Resources\ResourcePriority;
use Sif\Foundation\Resources\ResourceType;

final class ResourceValueModelTest extends TestCase
{
    public function testIdentifierIsTrimmedAndCaseSensitive(): void
    {
        $identifier = new ResourceIdentifier(' App.Main ');
        self::assertSame('App.Main', $identifier->value());
        self::assertFalse($identifier->equals(new ResourceIdentifier('app.main')));
    }

    public function testIdentifierRejectsWhitespace(): void
    {
        $this->expectException(InvalidResourceIdentifierException::class);
        new ResourceIdentifier('app main');
    }

    public function testNamespaceSupportsExplicitGlobalNamespace(): void
    {
        self::assertSame('global', ResourceNamespace::global()->value());
        self::assertSame('Vendor.Module', (new ResourceNamespace('Vendor.Module'))->value());
    }

    public function testNamespaceRejectsPathSyntax(): void
    {
        $this->expectException(InvalidResourceNamespaceException::class);
        new ResourceNamespace('../module');
    }

    public function testKnownAndExtensionResourceTypesAreCanonical(): void
    {
        self::assertSame('stylesheet', ResourceType::stylesheet()->value());
        self::assertSame('map-layer', (new ResourceType(' Map-Layer '))->value());
    }

    public function testResourceTypeRejectsUnsafeVocabulary(): void
    {
        $this->expectException(InvalidResourceTypeException::class);
        new ResourceType('image/svg+xml');
    }

    public function testPathNormalizesDirectorySeparators(): void
    {
        $path = new ResourcePath('assets\\css\\app.css');
        self::assertSame('assets/css/app.css', $path->value());
        self::assertSame(['assets', 'css', 'app.css'], $path->segments());
        self::assertSame('app.css', $path->basename());
    }

    /** @dataProvider unsafePathProvider */
    public function testPathRejectsUnsafeOrAbsoluteValues(string $path): void
    {
        $this->expectException(InvalidResourcePathException::class);
        new ResourcePath($path);
    }

    /** @return iterable<string, array{string}> */
    public static function unsafePathProvider(): iterable
    {
        yield 'parent traversal' => ['assets/../secret.txt'];
        yield 'current segment' => ['assets/./app.css'];
        yield 'unix absolute' => ['/var/app.css'];
        yield 'windows absolute' => ['C:\\assets\\app.css'];
        yield 'empty segment' => ['assets//app.css'];
        yield 'null byte' => ["assets/app.css\0.php"];
    }

    public function testPriorityComparisonIsDeterministic(): void
    {
        self::assertSame(1, (new ResourcePriority(20))->compare(new ResourcePriority(10)));
        self::assertSame(0, ResourcePriority::default()->value());
    }

    public function testPriorityIsBounded(): void
    {
        $this->expectException(InvalidResourcePriorityException::class);
        new ResourcePriority(ResourcePriority::MAXIMUM + 1);
    }

    public function testDescriptorPreservesTypedIdentityAndSafeMetadata(): void
    {
        $descriptor = new ResourceDescriptor(
            new ResourceIdentifier('app.main'),
            new ResourceNamespace('application'),
            ResourceType::stylesheet(),
            new ResourcePath('assets/app.css'),
            new ResourcePriority(100),
            '2.1.0',
            'core.application',
            ['defer' => false, 'weight' => 10, 'integrity' => null],
        );

        self::assertSame('application:app.main', $descriptor->qualifiedIdentifier());
        self::assertSame('assets/app.css', $descriptor->source()->value());
        self::assertSame(100, $descriptor->priority()->value());
        self::assertSame('2.1.0', $descriptor->logicalVersion());
        self::assertSame('core.application', $descriptor->owner());
        self::assertSame(false, $descriptor->metadata()['defer']);
    }

    public function testDescriptorSummaryIsStableAndPortable(): void
    {
        $descriptor = new ResourceDescriptor(
            new ResourceIdentifier('logo'),
            ResourceNamespace::global(),
            ResourceType::image(),
            new ResourcePath('images/logo.svg'),
        );

        self::assertSame([
            'identifier' => 'logo',
            'namespace' => 'global',
            'type' => 'image',
            'source' => 'images/logo.svg',
            'priority' => 0,
            'logical_version' => null,
            'owner' => null,
            'metadata' => [],
        ], $descriptor->summary());
    }

    public function testDescriptorRejectsObjectsInMetadata(): void
    {
        $this->expectException(InvalidResourceDescriptorException::class);
        new ResourceDescriptor(
            new ResourceIdentifier('app'),
            ResourceNamespace::global(),
            ResourceType::generic(),
            new ResourcePath('data/app.json'),
            metadata: ['object' => new \stdClass()],
        );
    }

    public function testDescriptorRejectsBlankLogicalVersion(): void
    {
        $this->expectException(InvalidResourceDescriptorException::class);
        new ResourceDescriptor(
            new ResourceIdentifier('app'),
            ResourceNamespace::global(),
            ResourceType::generic(),
            new ResourcePath('data/app.json'),
            logicalVersion: '   ',
        );
    }
}
