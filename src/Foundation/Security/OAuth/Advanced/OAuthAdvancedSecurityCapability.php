<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\Advanced;

use InvalidArgumentException;

final readonly class OAuthAdvancedSecurityCapability
{
    public const PAR = 'par';
    public const JAR = 'jar';
    public const RAR = 'rar';
    public const DPOP = 'dpop';
    public const DYNAMIC_CLIENT_REGISTRATION = 'dynamic_client_registration';
    public const AUTHORIZATION_SERVER_METADATA = 'authorization_server_metadata';

    public function __construct(private string $value)
    {
        if (!in_array(
            $this->value,
            [
                self::PAR,
                self::JAR,
                self::RAR,
                self::DPOP,
                self::DYNAMIC_CLIENT_REGISTRATION,
                self::AUTHORIZATION_SERVER_METADATA,
            ],
            true
        )) {
            throw new InvalidArgumentException(
                'OAuth advanced security capability is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
