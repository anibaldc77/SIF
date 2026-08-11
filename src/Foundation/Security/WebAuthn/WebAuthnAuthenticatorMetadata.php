<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\WebAuthn;

use InvalidArgumentException;

final readonly class WebAuthnAuthenticatorMetadata
{
    /**
     * @param list<string> $certificationDescriptors
     * @param list<string> $authenticationAlgorithms
     * @param list<string> $attestationTypes
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        private string $authenticatorIdentifier,
        private string $description,
        private array $certificationDescriptors = [],
        private array $authenticationAlgorithms = [],
        private array $attestationTypes = [],
        private array $attributes = []
    ) {
        if (
            trim($this->authenticatorIdentifier) === ''
            || trim($this->description) === ''
        ) {
            throw new InvalidArgumentException(
                'WebAuthn authenticator metadata is invalid.'
            );
        }
    }

    public function authenticatorIdentifier(): string
    {
        return $this->authenticatorIdentifier;
    }

    public function description(): string
    {
        return $this->description;
    }

    /**
     * @return list<string>
     */
    public function certificationDescriptors(): array
    {
        return $this->certificationDescriptors;
    }

    /**
     * @return list<string>
     */
    public function authenticationAlgorithms(): array
    {
        return $this->authenticationAlgorithms;
    }

    /**
     * @return list<string>
     */
    public function attestationTypes(): array
    {
        return $this->attestationTypes;
    }

    /**
     * @return array<string, mixed>
     */
    public function attributes(): array
    {
        return $this->attributes;
    }
}
