<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Formats\IsoMdoc;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class IsoMdocMobileSecurityObject
{
    /**
     * @param array<string, mixed> $digestAlgorithmMetadata
     * @param array<string, mixed> $deviceKeyInfo
     */
    public function __construct(
        private string $documentType,
        private DateTimeImmutable $validFrom,
        private DateTimeImmutable $validUntil,
        private array $digestAlgorithmMetadata = [],
        private array $deviceKeyInfo = []
    ) {
        if (trim($this->documentType) === '') {
            throw new InvalidArgumentException(
                'ISO mdoc Mobile Security Object is invalid.'
            );
        }
    }

    public function documentType(): string
    {
        return $this->documentType;
    }

    public function validFrom(): DateTimeImmutable
    {
        return $this->validFrom;
    }

    public function validUntil(): DateTimeImmutable
    {
        return $this->validUntil;
    }

    /**
     * @return array<string, mixed>
     */
    public function digestAlgorithmMetadata(): array
    {
        return $this->digestAlgorithmMetadata;
    }

    /**
     * @return array<string, mixed>
     */
    public function deviceKeyInfo(): array
    {
        return $this->deviceKeyInfo;
    }
}
