<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Formats\IsoMdoc;

use InvalidArgumentException;

final readonly class IsoMdocIssuerSignedData
{
    /**
     * @param list<IsoMdocNamespace> $namespaces
     * @param array<string, mixed> $issuerAuthentication
     */
    public function __construct(
        private string $documentType,
        private array $namespaces = [],
        private array $issuerAuthentication = []
    ) {
        if (trim($this->documentType) === '') {
            throw new InvalidArgumentException(
                'ISO mdoc issuer-signed data is invalid.'
            );
        }
    }

    public function documentType(): string
    {
        return $this->documentType;
    }

    /**
     * @return list<IsoMdocNamespace>
     */
    public function namespaces(): array
    {
        return $this->namespaces;
    }

    /**
     * @return array<string, mixed>
     */
    public function issuerAuthentication(): array
    {
        return $this->issuerAuthentication;
    }
}
