<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp;

use InvalidArgumentException;

final readonly class OpenId4VpRequestObject
{
    public function __construct(
        private string $serialized,
        private ?string $keyId = null,
        private ?string $algorithm = null
    ) {
        if (trim($this->serialized) === '') {
            throw new InvalidArgumentException('OpenID4VP request object is invalid.');
        }
    }

    public function serialized(): string
    {
        return $this->serialized;
    }

    public function keyId(): ?string
    {
        return $this->keyId;
    }

    public function algorithm(): ?string
    {
        return $this->algorithm;
    }
}
