<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Formats\IsoMdoc;

final readonly class IsoMdocDeviceResponse
{
    /**
     * @param list<IsoMdocDocument> $documents
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private string $version,
        private array $documents = [],
        private array $metadata = []
    ) {
    }

    public function version(): string
    {
        return $this->version;
    }

    /**
     * @return list<IsoMdocDocument>
     */
    public function documents(): array
    {
        return $this->documents;
    }

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }
}
