<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Status\Issuer\CredentialStatusPublicationPlan;

interface CredentialStatusPublicationPlannerInterface
{
    public function plan(
        string $statusListId
    ): CredentialStatusPublicationPlan;
}
