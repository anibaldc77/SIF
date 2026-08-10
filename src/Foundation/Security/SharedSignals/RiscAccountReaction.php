<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\SharedSignals;

use InvalidArgumentException;

final readonly class RiscAccountReaction
{
    public const NONE = 'none';
    public const FLAG_ACCOUNT = 'flag-account';
    public const REQUIRE_REAUTHENTICATION = 'require-reauthentication';
    public const REVOKE_SESSIONS = 'revoke-sessions';
    public const REVOKE_TOKENS = 'revoke-tokens';
    public const DISABLE_ACCESS = 'disable-access';

    public function __construct(private string $action)
    {
        if (!in_array($this->action, [
            self::NONE,
            self::FLAG_ACCOUNT,
            self::REQUIRE_REAUTHENTICATION,
            self::REVOKE_SESSIONS,
            self::REVOKE_TOKENS,
            self::DISABLE_ACCESS,
        ], true)) {
            throw new InvalidArgumentException(
                'RISC account reaction is invalid.'
            );
        }
    }

    public function action(): string
    {
        return $this->action;
    }
}
