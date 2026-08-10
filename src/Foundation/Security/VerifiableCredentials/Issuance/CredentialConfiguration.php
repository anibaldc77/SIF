<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Issuance;

use InvalidArgumentException;

final readonly class CredentialConfiguration
{
    /**
     * @param list<string> $cryptographicBindingMethods
     * @param list<string> $proofTypes
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private string $configurationId,
        private string $format,
        private array $cryptographicBindingMethods = [],
        private array $proofTypes = [],
        private array $metadata = []
    ) {
        if (
            trim($this->configurationId) === ''
            || trim($this->format) === ''
        ) {
            throw new InvalidArgumentException(
                'Credential configuration is invalid.'
            );
        }
    }

    public function configurationId(): string
    {
        return $this->configurationId;
    }

    public function format(): string
    {
        return $this->format;
    }

    /**
     * @return list<string>
     */
    public function cryptographicBindingMethods(): array
    {
        return $this->cryptographicBindingMethods;
    }

    /**
     * @return list<string>
     */
    public function proofTypes(): array
    {
        return $this->proofTypes;
    }

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }
}
