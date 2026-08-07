<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\PersistentAuthentication;

use Sif\Foundation\Security\Contracts\PersistentAuthenticationTokenGeneratorInterface;

final readonly class SecurePersistentAuthenticationTokenGenerator implements PersistentAuthenticationTokenGeneratorInterface
{
    public function __construct(
        private int $selectorBytes = 16,
        private int $validatorBytes = 32
    ) {
        if ($selectorBytes < 8 || $selectorBytes > 64) {
            throw new \InvalidArgumentException(
                'Persistent authentication selector entropy must be between 64 and 512 bits.'
            );
        }

        if ($validatorBytes < 16 || $validatorBytes > 64) {
            throw new \InvalidArgumentException(
                'Persistent authentication validator entropy must be between 128 and 512 bits.'
            );
        }
    }

    public function generate(): PersistentAuthenticationToken
    {
        $selectorBytes = max(1, $this->selectorBytes);
        $validatorBytes = max(1, $this->validatorBytes);

        return new PersistentAuthenticationToken(
            new PersistentAuthenticationSelector(
                bin2hex(random_bytes($selectorBytes))
            ),
            new PersistentAuthenticationValidator(
                self::base64UrlEncode(random_bytes($validatorBytes))
            )
        );
    }

    private static function base64UrlEncode(string $bytes): string
    {
        return rtrim(
            strtr(base64_encode($bytes), '+/', '-_'),
            '='
        );
    }
}
