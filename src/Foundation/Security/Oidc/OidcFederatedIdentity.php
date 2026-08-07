<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc;

use InvalidArgumentException;

final readonly class OidcFederatedIdentity
{
    /**
     * @param array<string, scalar|null> $claims
     */
    public function __construct(
        private string $issuer,
        private string $subject,
        private array $claims = []
    ) {
        if ($this->issuer === '' || $this->subject === '') {
            throw new InvalidArgumentException(
                'Federated identity requires issuer and subject.'
            );
        }
    }

    public function issuer(): string
    {
        return $this->issuer;
    }

    public function subject(): string
    {
        return $this->subject;
    }

    public function stableKey(): string
    {
        return hash(
            'sha256',
            $this->issuer . "\0" . $this->subject
        );
    }

    /** @return array<string, scalar|null> */
    public function claims(): array
    {
        return $this->claims;
    }
}
