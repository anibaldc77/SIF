<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Reference\Parser;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sif\Builder\Metadata\MetadataDocument;
use Sif\Builder\Reference\Exception\ReferenceParseException;
use Sif\Builder\Reference\Parser\FrontMatterReferenceParser;
use Sif\Builder\Reference\ReferenceType;

final class FrontMatterReferenceParserTest extends TestCase
{
    public function testParsesSupportedFieldsAndNormalizesIdentifiers(): void
    {
        $document = new MetadataDocument('/repo/WP-102.md', [
            'id' => 'wp-102',
            'references' => [' adr-001 ', 'SPEC-002'],
            'implements' => 'RFC-004',
            'extends' => null,
            'supersedes' => 'WP-099',
            'related_adrs' => ['ADR-005'],
        ]);

        $references = (new FrontMatterReferenceParser())->parse($document)->all();

        self::assertCount(5, $references);
        self::assertSame('WP-102', $references[0]->sourceIdentifier);
        self::assertSame(
            [
                'ADR-001' => ReferenceType::REFERENCE,
                'ADR-005' => ReferenceType::RELATED,
                'RFC-004' => ReferenceType::IMPLEMENTS,
                'SPEC-002' => ReferenceType::REFERENCE,
                'WP-099' => ReferenceType::SUPERSEDES,
            ],
            $this->targetsAndTypes($references),
        );
        self::assertSame('front-matter:references', $references[0]->context);
    }

    public function testIgnoresMissingAndNullReferenceFields(): void
    {
        $document = new MetadataDocument('/repo/WP-102.md', [
            'id' => 'WP-102',
            'supersedes' => null,
        ]);

        self::assertTrue((new FrontMatterReferenceParser())->parse($document)->isEmpty());
    }

    #[DataProvider('invalidFieldValues')]
    public function testRejectsInvalidFieldValues(mixed $value): void
    {
        $document = new MetadataDocument('/repo/WP-102.md', [
            'id' => 'WP-102',
            'references' => $value,
        ]);

        $this->expectException(ReferenceParseException::class);
        $this->expectExceptionMessage('must be null, a string, or a list of strings');

        (new FrontMatterReferenceParser())->parse($document);
    }

    /** @return iterable<string, array{mixed}> */
    public static function invalidFieldValues(): iterable
    {
        yield 'integer' => [42];
        yield 'boolean' => [true];
        yield 'mixed list' => [['ADR-001', 42]];
    }

    public function testRejectsInvalidSourceIdentifier(): void
    {
        $document = new MetadataDocument('/repo/document.md', [
            'id' => 'invalid identifier',
            'references' => 'ADR-001',
        ]);

        $this->expectException(ReferenceParseException::class);
        $this->expectExceptionMessage('Invalid reference identifier');

        (new FrontMatterReferenceParser())->parse($document);
    }

    public function testRejectsInvalidTargetIdentifier(): void
    {
        $document = new MetadataDocument('/repo/WP-102.md', [
            'id' => 'WP-102',
            'references' => 'not valid',
        ]);

        $this->expectException(ReferenceParseException::class);
        $this->expectExceptionMessage('Invalid reference identifier');

        (new FrontMatterReferenceParser())->parse($document);
    }

    public function testRejectsDuplicateReferencesInSameField(): void
    {
        $document = new MetadataDocument('/repo/WP-102.md', [
            'id' => 'WP-102',
            'references' => ['ADR-001', ' adr-001 '],
        ]);

        $this->expectException(ReferenceParseException::class);
        $this->expectExceptionMessage('Duplicate reference "ADR-001"');

        (new FrontMatterReferenceParser())->parse($document);
    }

    /**
     * @param list<\Sif\Builder\Reference\Reference> $references
     * @return array<string, ReferenceType>
     */
    private function targetsAndTypes(array $references): array
    {
        $result = [];
        foreach ($references as $reference) {
            $result[$reference->targetIdentifier] = $reference->type;
        }
        ksort($result);

        return $result;
    }
}
