<?php

declare(strict_types=1);

namespace Sif\Foundation\Session;

use Sif\Foundation\Session\Contracts\SessionIdGeneratorInterface;

final class CryptographicSessionIdGenerator implements SessionIdGeneratorInterface
{
    public function generate(): SessionId
    {
        return new SessionId(rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '='));
    }
}
