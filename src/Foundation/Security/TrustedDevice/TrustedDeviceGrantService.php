<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\TrustedDevice;

use DateTimeImmutable;
use Sif\Foundation\Security\Contracts\TrustedDeviceGrantIdGeneratorInterface;
use Sif\Foundation\Security\Contracts\TrustedDeviceGrantLifecycleStoreInterface;
use Sif\Foundation\Security\Identity\IdentityId;

final readonly class TrustedDeviceGrantService
{
    public function __construct(
        private TrustedDeviceGrantLifecycleStoreInterface $store,
        private TrustedDeviceGrantIdGeneratorInterface $idGenerator
    ) {
    }

    public function issue(
        IdentityId $identityId,
        DateTimeImmutable $issuedAt,
        DateTimeImmutable $expiresAt
    ): TrustedDeviceGrant {
        $grant = new TrustedDeviceGrant(
            $this->idGenerator->generate(),
            $identityId,
            $issuedAt,
            $expiresAt
        );

        $this->store->save($grant);

        return $grant;
    }

    public function isTrusted(
        TrustedDeviceGrantId $id,
        IdentityId $identityId,
        DateTimeImmutable $now
    ): bool {
        $grant = $this->store->find($id);

        return $grant !== null
            && $grant->identityId()->value() === $identityId->value()
            && $grant->isUsableAt($now);
    }

    public function revoke(
        TrustedDeviceGrantId $id,
        DateTimeImmutable $at
    ): bool {
        return $this->store->revoke($id, $at);
    }

    public function revokeAllForIdentity(
        IdentityId $identityId,
        DateTimeImmutable $at
    ): int {
        return $this->store->revokeAllForIdentity($identityId, $at);
    }
}
