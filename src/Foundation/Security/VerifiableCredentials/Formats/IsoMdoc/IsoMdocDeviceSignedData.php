<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Formats\IsoMdoc;

final readonly class IsoMdocDeviceSignedData
{
    /**
     * @param list<IsoMdocNamespace> $namespaces
     * @param array<string, mixed> $deviceAuthentication
     */
    public function __construct(
        private array $namespaces = [],
        private array $deviceAuthentication = []
    ) {
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
    public function deviceAuthentication(): array
    {
        return $this->deviceAuthentication;
    }
}
