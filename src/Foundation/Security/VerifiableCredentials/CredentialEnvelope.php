<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials;

use InvalidArgumentException;

final readonly class CredentialEnvelope
{
    public function __construct(
        private CredentialFormat $format,
        private string $serializedCredential
    ) {
        if (trim($this->serializedCredential) === '') {
            throw new InvalidArgumentException(
                'Credential envelope is invalid.'
            );
        }
    }

    public function format(): CredentialFormat
    {
        return $this->format;
    }

    public function serializedCredential(): string
    {
        return $this->serializedCredential;
    }
}
