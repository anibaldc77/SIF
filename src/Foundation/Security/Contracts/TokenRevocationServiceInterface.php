<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

interface TokenRevocationServiceInterface
{
    public function revoke(string $tokenId): void;
}
