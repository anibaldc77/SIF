<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceNotification;

interface CredentialIssuanceNotificationPublisherInterface
{
    public function publish(
        CredentialIssuanceNotification $notification
    ): void;
}
