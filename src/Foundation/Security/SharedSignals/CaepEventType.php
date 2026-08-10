<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\SharedSignals;

use InvalidArgumentException;

final readonly class CaepEventType
{
    public const SESSION_REVOKED = 'session-revoked';
    public const TOKEN_CLAIMS_CHANGE = 'token-claims-change';
    public const ASSURANCE_LEVEL_CHANGE = 'assurance-level-change';
    public const DEVICE_COMPLIANCE_CHANGE = 'device-compliance-change';

    public function __construct(private string $value)
    {
        if (!in_array($this->value, [
            self::SESSION_REVOKED,
            self::TOKEN_CLAIMS_CHANGE,
            self::ASSURANCE_LEVEL_CHANGE,
            self::DEVICE_COMPLIANCE_CHANGE,
        ], true)) {
            throw new InvalidArgumentException(
                'CAEP event type is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
