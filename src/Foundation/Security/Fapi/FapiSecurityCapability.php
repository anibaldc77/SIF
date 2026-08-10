<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Fapi;

use InvalidArgumentException;

final readonly class FapiSecurityCapability
{
    public const CONFIDENTIAL_CLIENTS = 'confidential_clients';
    public const PKCE = 'pkce';
    public const PAR = 'par';
    public const SENDER_CONSTRAINED_TOKENS = 'sender_constrained_tokens';
    public const ISSUER_VALIDATION = 'issuer_validation';
    public const METADATA_DISCOVERY = 'metadata_discovery';
    public const JAR = 'jar';
    public const JARM = 'jarm';
    public const SIGNED_INTROSPECTION = 'signed_introspection';

    public function __construct(private string $value)
    {
        if (!in_array($this->value, [
            self::CONFIDENTIAL_CLIENTS,
            self::PKCE,
            self::PAR,
            self::SENDER_CONSTRAINED_TOKENS,
            self::ISSUER_VALIDATION,
            self::METADATA_DISCOVERY,
            self::JAR,
            self::JARM,
            self::SIGNED_INTROSPECTION,
        ], true)) {
            throw new InvalidArgumentException(
                'FAPI security capability is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
