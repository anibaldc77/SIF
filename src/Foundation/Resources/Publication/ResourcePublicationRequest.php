<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Publication;

use Sif\Foundation\Resources\Exceptions\InvalidResourcePublicationRequestException;
use Sif\Foundation\Resources\ResourceDescriptor;
use Sif\Foundation\Resources\ResourcePath;
use Sif\Foundation\Resources\ResourceRootIdentifier;

final readonly class ResourcePublicationRequest
{
    public function __construct(
        private ResourceDescriptor $descriptor,
        private ResourceRootIdentifier $sourceRoot,
        private ResourcePath $targetPath,
        private ResourceContentFingerprint $contentFingerprint,
        private int $contentSize,
    ) {
        if ($contentSize < 0) {
            throw new InvalidResourcePublicationRequestException('Resource publication content size must be zero or greater.');
        }
    }

    public function descriptor(): ResourceDescriptor { return $this->descriptor; }
    public function sourceRoot(): ResourceRootIdentifier { return $this->sourceRoot; }
    public function targetPath(): ResourcePath { return $this->targetPath; }
    public function contentFingerprint(): ResourceContentFingerprint { return $this->contentFingerprint; }
    public function contentSize(): int { return $this->contentSize; }

    public function qualifiedIdentifier(): string
    {
        return $this->descriptor->qualifiedIdentifier();
    }
}
