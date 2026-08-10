<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

interface SessionRevocationServiceInterface
{
    public function revoke(string $sessionId): void;
}
