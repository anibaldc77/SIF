<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Formats\IsoMdoc;

use InvalidArgumentException;

final readonly class IsoMdocDocument
{
    public function __construct(
        private string $documentType,
        private IsoMdocIssuerSignedData $issuerSigned,
        private ?IsoMdocDeviceSignedData $deviceSigned = null
    ) {
        if (trim($this->documentType) === '') {
            throw new InvalidArgumentException(
                'ISO mdoc document is invalid.'
            );
        }
    }

    public function documentType(): string
    {
        return $this->documentType;
    }

    public function issuerSigned(): IsoMdocIssuerSignedData
    {
        return $this->issuerSigned;
    }

    public function deviceSigned(): ?IsoMdocDeviceSignedData
    {
        return $this->deviceSigned;
    }
}
