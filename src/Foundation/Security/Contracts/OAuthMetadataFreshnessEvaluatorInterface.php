<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use DateTimeImmutable;
use Sif\Foundation\Security\OAuth\Metadata\OAuthAuthorizationServerMetadataCacheEntry;

interface OAuthMetadataFreshnessEvaluatorInterface
{
    public function isFresh(
        OAuthAuthorizationServerMetadataCacheEntry $entry,
        DateTimeImmutable $instant
    ): bool;
}
