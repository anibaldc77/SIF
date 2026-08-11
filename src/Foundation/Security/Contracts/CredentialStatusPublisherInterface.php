<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Status\Issuer\CredentialStatusPublicationPlan;
use Sif\Foundation\Security\VerifiableCredentials\Status\Issuer\CredentialStatusPublicationResult;

interface CredentialStatusPublisherInterface
{
    public function publish(
        CredentialStatusPublicationPlan $plan
    ): CredentialStatusPublicationResult;
}
