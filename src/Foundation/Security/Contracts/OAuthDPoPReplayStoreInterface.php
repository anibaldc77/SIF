<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\Contracts;
use DateTimeImmutable;
interface OAuthDPoPReplayStoreInterface
{
    public function hasBeenUsed(string $publicKeyThumbprint, string $tokenId): bool;
    public function markUsed(string $publicKeyThumbprint, string $tokenId, DateTimeImmutable $expiresAt): void;
}
