<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\MultiFactor\RecoveryCode;

use DateTimeImmutable;
use Sif\Foundation\Security\Contracts\RecoveryCodeStoreInterface;
use Sif\Foundation\Security\Identity\IdentityId;

final readonly class RecoveryCodeEnrollmentService
{
    public function __construct(
        private RecoveryCodeGenerator $generator,
        private RecoveryCodeStoreInterface $store
    ) {
    }

    public function replaceForIdentity(IdentityId $identityId, DateTimeImmutable $issuedAt): RecoveryCodeBatch
    {
        $batch = $this->generator->generate();

        $records = $batch->expose(
            static fn (array $codes): array => array_map(
                static fn (RecoveryCode $code): RecoveryCodeRecord => new RecoveryCodeRecord(
                    $identityId,
                    $code->digest(),
                    $issuedAt
                ),
                $codes
            )
        );

        $this->store->replaceForIdentity($identityId, $records);

        return $batch;
    }
}
