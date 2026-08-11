<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Formats;

use InvalidArgumentException;

final readonly class HighAssuranceCredentialProfile
{
    /**
     * @param list<HighAssuranceCredentialFormat> $allowedFormats
     * @param list<string> $requiredControls
     * @param list<string> $requiredClaims
     */
    public function __construct(
        private string $name,
        private array $allowedFormats,
        private array $requiredControls = [],
        private array $requiredClaims = [],
        private bool $holderBindingRequired = true,
        private bool $statusValidationRequired = true
    ) {
        if (
            trim($this->name) === ''
            || $this->allowedFormats === []
        ) {
            throw new InvalidArgumentException(
                'High assurance credential profile is invalid.'
            );
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return list<HighAssuranceCredentialFormat>
     */
    public function allowedFormats(): array
    {
        return $this->allowedFormats;
    }

    /**
     * @return list<string>
     */
    public function requiredControls(): array
    {
        return $this->requiredControls;
    }

    /**
     * @return list<string>
     */
    public function requiredClaims(): array
    {
        return $this->requiredClaims;
    }

    public function holderBindingRequired(): bool
    {
        return $this->holderBindingRequired;
    }

    public function statusValidationRequired(): bool
    {
        return $this->statusValidationRequired;
    }
}
