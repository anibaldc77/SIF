<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Metadata;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Metadata\CoreMetadataValidator;

final class CoreMetadataValidatorTest extends TestCase
{
    private CoreMetadataValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new CoreMetadataValidator();
    }

    public function testValidEngineeringStandardPasses(): void
    {
        self::assertTrue($this->validator->validate($this->validMetadata())->isValid());
    }

    public function testMissingRequiredFieldsAreCollected(): void
    {
        $result = $this->validator->validate(['id' => 'ES-999']);

        self::assertFalse($result->isValid());
        self::assertGreaterThan(1, count($result->errors()));
    }

    public function testReleaseCandidateRequiresRcVersion(): void
    {
        $metadata = $this->validMetadata();
        $metadata['status'] = 'Release Candidate';
        $metadata['version'] = '1.0.0';

        self::assertContains('META_RC_VERSION', $this->errorCodes($this->validator->validate($metadata)->errors()));
    }

    public function testApprovedRequiresStableVersion(): void
    {
        $metadata = $this->validMetadata();
        $metadata['status'] = 'Approved';
        $metadata['version'] = '1.0.0-rc.1';

        self::assertContains('META_APPROVED_VERSION', $this->errorCodes($this->validator->validate($metadata)->errors()));
    }

    public function testSupersededRequiresSupersededBy(): void
    {
        $metadata = $this->validMetadata();
        $metadata['status'] = 'Superseded';
        $metadata['superseded_by'] = null;

        self::assertContains('META_SUPERSEDED_BY', $this->errorCodes($this->validator->validate($metadata)->errors()));
    }

    public function testCategoryAndDocumentClassMustBeCompatible(): void
    {
        $metadata = $this->validMetadata();
        $metadata['document_class'] = 'ReviewDocument';

        self::assertContains('META_CLASS_CATEGORY', $this->errorCodes($this->validator->validate($metadata)->errors()));
    }

    /** @return array<string, mixed> */
    private function validMetadata(): array
    {
        return [
            'id' => 'ES-003',
            'title' => 'Document Class Model',
            'status' => 'Draft for Review',
            'version' => '0.1.0',
            'category' => 'Engineering Standard',
            'document_class' => 'NormativeDocument',
            'authors' => ['SIF Architecture Board'],
            'created' => '2026-07-17',
            'updated' => '2026-07-17',
            'tags' => ['documentation', 'document-class'],
            'work_package' => 'WP-100',
            'depends_on' => ['ES-001', 'ES-002'],
            'related_adrs' => [],
            'supersedes' => null,
            'superseded_by' => null,
        ];
    }

    /** @param list<\Sif\Builder\Metadata\MetadataValidationError> $errors @return list<string> */
    private function errorCodes(array $errors): array
    {
        return array_map(static fn ($error): string => $error->code, $errors);
    }
}
