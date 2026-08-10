<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceNotification;

interface CredentialIssuanceNotificationHandlerInterface
{
    public function handle(
        CredentialIssuanceNotification $notification
    ): void;
}
