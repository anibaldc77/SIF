<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\Metadata\OAuthClientRegistrationRecord;

interface OAuthClientRegistrationRecordRepositoryInterface
{
    public function findByClientId(
        string $clientId
    ): ?OAuthClientRegistrationRecord;

    public function save(
        OAuthClientRegistrationRecord $record
    ): void;

    public function delete(
        string $clientId
    ): void;
}
