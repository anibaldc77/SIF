<?php

declare(strict_types=1);

namespace Sif\Builder\Metadata;

final class CoreMetadataValidator implements MetadataValidatorInterface
{
    private const REQUIRED_FIELDS = [
        'id', 'title', 'status', 'version', 'category', 'authors',
        'created', 'updated', 'tags', 'depends_on', 'related_adrs',
    ];

    private const STATUSES = [
        'Draft', 'Draft for Review', 'Technical Review', 'Release Candidate',
        'Approved', 'Deprecated', 'Superseded', 'Archived',
    ];

    private const CATEGORIES = [
        'Constitution', 'Architecture Specification', 'Engineering Standard',
        'Policy', 'Architecture Decision Record', 'Request for Comments',
        'Work Package', 'Normative Specification', 'Architecture Review',
        'Implementation Review', 'Informative Document', 'Template',
    ];

    private const CATEGORY_CLASSES = [
        'Constitution' => DocumentClass::Normative,
        'Architecture Specification' => DocumentClass::Normative,
        'Engineering Standard' => DocumentClass::Normative,
        'Policy' => DocumentClass::Governance,
        'Architecture Decision Record' => DocumentClass::Governance,
        'Request for Comments' => DocumentClass::Governance,
        'Work Package' => DocumentClass::Governance,
        'Normative Specification' => DocumentClass::Normative,
        'Architecture Review' => DocumentClass::Review,
        'Implementation Review' => DocumentClass::Review,
        'Informative Document' => DocumentClass::Informative,
        'Template' => DocumentClass::Template,
    ];

    /** @param array<string, mixed> $metadata */
    public function validate(array $metadata): MetadataValidationResult
    {
        $errors = [];

        foreach (self::REQUIRED_FIELDS as $field) {
            if (!array_key_exists($field, $metadata)) {
                $errors[] = $this->error('META_REQUIRED', $field, sprintf('Required field "%s" is missing.', $field));
            }
        }

        $this->validateIdentifier($metadata, $errors);
        $this->validateEnum($metadata, 'status', self::STATUSES, $errors);
        $this->validateEnum($metadata, 'category', self::CATEGORIES, $errors);
        $this->validateVersion($metadata, $errors);
        $this->validateDocumentClass($metadata, $errors);
        $this->validateStringList($metadata, 'authors', true, $errors);
        $this->validateStringList($metadata, 'tags', false, $errors);
        $this->validateStringList($metadata, 'depends_on', false, $errors);
        $this->validateStringList($metadata, 'related_adrs', false, $errors);
        $this->validateLifecycle($metadata, $errors);
        $this->validateSelfReferences($metadata, $errors);

        return MetadataValidationResult::fromErrors($errors);
    }

    /** @param array<string, mixed> $metadata @param list<MetadataValidationError> $errors */
    private function validateIdentifier(array $metadata, array &$errors): void
    {
        $id = $metadata['id'] ?? null;
        if (!is_string($id) || preg_match('/^[A-Z](?:[A-Z0-9]|-(?!-))*[A-Z0-9]$|^[A-Z]$/', $id) !== 1) {
            $errors[] = $this->error('META_ID_FORMAT', 'id', 'Identifier must use uppercase ASCII letters, digits and single hyphens.');
        }
    }

    /** @param array<string, mixed> $metadata @param list<string> $allowed @param list<MetadataValidationError> $errors */
    private function validateEnum(array $metadata, string $field, array $allowed, array &$errors): void
    {
        if (array_key_exists($field, $metadata) && (!is_string($metadata[$field]) || !in_array($metadata[$field], $allowed, true))) {
            $errors[] = $this->error('META_ENUM', $field, sprintf('Field "%s" contains an unregistered value.', $field));
        }
    }

    /** @param array<string, mixed> $metadata @param list<MetadataValidationError> $errors */
    private function validateVersion(array $metadata, array &$errors): void
    {
        $version = $metadata['version'] ?? null;
        $pattern = '/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-[0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*)?$/';
        if (!is_string($version) || preg_match($pattern, $version) !== 1) {
            $errors[] = $this->error('META_VERSION_FORMAT', 'version', 'Version must conform to Semantic Versioning syntax.');
        }
    }

    /** @param array<string, mixed> $metadata @param list<MetadataValidationError> $errors */
    private function validateDocumentClass(array $metadata, array &$errors): void
    {
        if (!array_key_exists('document_class', $metadata)) {
            return;
        }

        $class = is_string($metadata['document_class']) ? DocumentClass::tryFrom($metadata['document_class']) : null;
        if ($class === null) {
            $errors[] = $this->error('META_DOCUMENT_CLASS', 'document_class', 'Document class is not registered.');
            return;
        }

        $category = $metadata['category'] ?? null;
        if (is_string($category) && isset(self::CATEGORY_CLASSES[$category]) && self::CATEGORY_CLASSES[$category] !== $class) {
            $errors[] = $this->error('META_CLASS_CATEGORY', 'document_class', 'Document class is incompatible with the artifact category.');
        }
    }

    /** @param array<string, mixed> $metadata @param list<MetadataValidationError> $errors */
    private function validateStringList(array $metadata, string $field, bool $nonEmpty, array &$errors): void
    {
        if (!array_key_exists($field, $metadata)) {
            return;
        }

        $value = $metadata[$field];
        if (!is_array($value) || ($nonEmpty && $value === [])) {
            $errors[] = $this->error('META_LIST', $field, sprintf('Field "%s" must be %sa list.', $field, $nonEmpty ? 'a non-empty ' : ''));
            return;
        }

        $seen = [];
        foreach ($value as $index => $item) {
            if (!is_string($item) || trim($item) === '') {
                $errors[] = $this->error('META_LIST_ITEM', sprintf('%s.%d', $field, $index), 'List entries must be non-empty strings.');
                continue;
            }
            if (isset($seen[$item])) {
                $errors[] = $this->error('META_LIST_DUPLICATE', $field, sprintf('Duplicate value "%s" is not allowed.', $item));
            }
            $seen[$item] = true;
        }
    }

    /** @param array<string, mixed> $metadata @param list<MetadataValidationError> $errors */
    private function validateLifecycle(array $metadata, array &$errors): void
    {
        $status = $metadata['status'] ?? null;
        $version = $metadata['version'] ?? null;

        if ($status === 'Release Candidate' && (!is_string($version) || preg_match('/^\d+\.\d+\.\d+-rc\.(0|[1-9]\d*)$/', $version) !== 1)) {
            $errors[] = $this->error('META_RC_VERSION', 'version', 'Release Candidate status requires an -rc.N version.');
        }
        if ($status === 'Approved' && (!is_string($version) || str_contains($version, '-'))) {
            $errors[] = $this->error('META_APPROVED_VERSION', 'version', 'Approved status requires a stable version.');
        }
        if ($status === 'Superseded' && (!isset($metadata['superseded_by']) || !is_string($metadata['superseded_by']) || $metadata['superseded_by'] === '')) {
            $errors[] = $this->error('META_SUPERSEDED_BY', 'superseded_by', 'Superseded status requires a superseding artifact identifier.');
        }
    }

    /** @param array<string, mixed> $metadata @param list<MetadataValidationError> $errors */
    private function validateSelfReferences(array $metadata, array &$errors): void
    {
        $id = $metadata['id'] ?? null;
        if (!is_string($id)) {
            return;
        }
        foreach (['depends_on', 'related_adrs'] as $field) {
            if (isset($metadata[$field]) && is_array($metadata[$field]) && in_array($id, $metadata[$field], true)) {
                $errors[] = $this->error('META_SELF_REFERENCE', $field, sprintf('Artifact "%s" cannot reference itself through "%s".', $id, $field));
            }
        }
        foreach (['supersedes', 'superseded_by'] as $field) {
            if (($metadata[$field] ?? null) === $id) {
                $errors[] = $this->error('META_SELF_REFERENCE', $field, sprintf('Artifact "%s" cannot reference itself through "%s".', $id, $field));
            }
        }
    }

    private function error(string $code, string $path, string $message): MetadataValidationError
    {
        return new MetadataValidationError($code, $path, $message);
    }
}
