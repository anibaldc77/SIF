<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\AuthorizationServer;

use InvalidArgumentException;

final readonly class OAuthClientAuthenticationMethod
{
    public const NONE = 'none';
    public const CLIENT_SECRET = 'client_secret';
    public const PRIVATE_KEY_JWT = 'private_key_jwt';
    public const MTLS = 'mtls';

    public function __construct(private string $value)
    {
        if (!in_array(
            $this->value,
            [
                self::NONE,
                self::CLIENT_SECRET,
                self::PRIVATE_KEY_JWT,
                self::MTLS,
            ],
            true
        )) {
            throw new InvalidArgumentException(
                'OAuth client authentication method is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
