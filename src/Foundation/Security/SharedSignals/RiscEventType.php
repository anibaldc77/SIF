<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\SharedSignals;

use InvalidArgumentException;

final readonly class RiscEventType
{
    public const ACCOUNT_DISABLED = 'account-disabled';
    public const ACCOUNT_ENABLED = 'account-enabled';
    public const CREDENTIAL_COMPROMISE = 'credential-compromise';
    public const CREDENTIAL_CHANGE = 'credential-change';
    public const IDENTIFIER_CHANGE = 'identifier-change';
    public const RECOVERY_ACTIVATED = 'recovery-activated';
    public const RECOVERY_INFORMATION_CHANGED = 'recovery-information-changed';

    public function __construct(private string $value)
    {
        if (!in_array($this->value, [
            self::ACCOUNT_DISABLED,
            self::ACCOUNT_ENABLED,
            self::CREDENTIAL_COMPROMISE,
            self::CREDENTIAL_CHANGE,
            self::IDENTIFIER_CHANGE,
            self::RECOVERY_ACTIVATED,
            self::RECOVERY_INFORMATION_CHANGED,
        ], true)) {
            throw new InvalidArgumentException(
                'RISC event type is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
