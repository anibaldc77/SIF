<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Trust\Metadata\CredentialTrustMetadataSnapshot;

interface CredentialTrustMetadataSnapshotResolverInterface
{
    public function resolve(string $entityId): ?CredentialTrustMetadataSnapshot;
}
