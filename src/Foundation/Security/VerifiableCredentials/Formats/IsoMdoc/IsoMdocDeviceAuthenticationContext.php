<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Formats\IsoMdoc;

use InvalidArgumentException;

final readonly class IsoMdocDeviceAuthenticationContext
{
    /**
     * @param array<string, mixed> $sessionTranscript
     */
    public function __construct(
        private string $documentType,
        private array $sessionTranscript,
        private ?string $deviceKeyId = null
    ) {
        if (
            trim($this->documentType) === ''
            || $this->sessionTranscript === []
        ) {
            throw new InvalidArgumentException(
                'ISO mdoc device authentication context is invalid.'
            );
        }
    }

    public function documentType(): string
    {
        return $this->documentType;
    }

    /**
     * @return array<string, mixed>
     */
    public function sessionTranscript(): array
    {
        return $this->sessionTranscript;
    }

    public function deviceKeyId(): ?string
    {
        return $this->deviceKeyId;
    }
}
