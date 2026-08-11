<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Formats;

use InvalidArgumentException;

final readonly class CredentialFormatProfile
{
    /**
     * @param list<string> $acceptedAlgorithms
     * @param array<string, mixed> $options
     */
    public function __construct(
        private HighAssuranceCredentialFormat $format,
        private string $profileVersion,
        private array $acceptedAlgorithms = [],
        private array $options = []
    ) {
        if (trim($this->profileVersion) === '') {
            throw new InvalidArgumentException(
                'Credential format profile version is invalid.'
            );
        }
    }

    public function format(): HighAssuranceCredentialFormat
    {
        return $this->format;
    }

    public function profileVersion(): string
    {
        return $this->profileVersion;
    }

    /**
     * @return list<string>
     */
    public function acceptedAlgorithms(): array
    {
        return $this->acceptedAlgorithms;
    }

    /**
     * @return array<string, mixed>
     */
    public function options(): array
    {
        return $this->options;
    }
}
