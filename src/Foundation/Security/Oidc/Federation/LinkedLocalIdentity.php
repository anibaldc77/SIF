<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc\Federation;

use InvalidArgumentException;
use Sif\Foundation\Security\Identity\IdentityId;

final readonly class LinkedLocalIdentity
{
    public function __construct(
        private IdentityId $identityId,
        private string $providerKey
    ) {
        if ($this->providerKey === '' || strlen($this->providerKey) > 512) {
            throw new InvalidArgumentException(
                'Federated provider link key is invalid.'
            );
        }
    }

    public function identityId(): IdentityId
    {
        return $this->identityId;
    }

    public function providerKey(): string
    {
        return $this->providerKey;
    }
}
