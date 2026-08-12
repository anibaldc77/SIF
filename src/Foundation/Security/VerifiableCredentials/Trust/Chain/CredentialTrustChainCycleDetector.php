<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Trust\Chain;

final class CredentialTrustChainCycleDetector
{
    public function hasCycle(CredentialTrustChain $chain): bool
    {
        $seen = [];

        foreach ($chain->links() as $link) {
            $entityId = $link->entity()->entityId();

            if (isset($seen[$entityId])) {
                return true;
            }

            $seen[$entityId] = true;
        }

        return false;
    }
}
