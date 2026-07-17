<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Metadata;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Metadata\Exception\DuplicateMetadataIdentifierException;
use Sif\Builder\Metadata\MetadataDocument;
use Sif\Builder\Metadata\MetadataRegistry;

final class MetadataRegistryTest extends TestCase
{
    public function testRegistersAndRetrievesDocuments(): void
    {
        $registry = new MetadataRegistry();
        $registry->register(new MetadataDocument('/repo/ES-002.md', ['id' => 'ES-002']));

        self::assertTrue($registry->has('ES-002'));
        self::assertSame('/repo/ES-002.md', $registry->get('ES-002')?->path);
        self::assertSame(1, $registry->count());
    }

    public function testRejectsDuplicateIdentifiers(): void
    {
        $registry = new MetadataRegistry();
        $registry->register(new MetadataDocument('/repo/one.md', ['id' => 'ES-002']));

        $this->expectException(DuplicateMetadataIdentifierException::class);
        $registry->register(new MetadataDocument('/repo/two.md', ['id' => 'ES-002']));
    }
}
