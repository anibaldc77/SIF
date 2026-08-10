<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\SharedSignals;

use InvalidArgumentException;

final readonly class CaepAccessReaction
{
    public const NONE = 'none';
    public const REAUTHENTICATE = 'reauthenticate';
    public const REEVALUATE = 'reevaluate';
    public const REVOKE_SESSION = 'revoke-session';
    public const REVOKE_TOKENS = 'revoke-tokens';

    public function __construct(private string $action)
    {
        if (!in_array($this->action, [
            self::NONE,
            self::REAUTHENTICATE,
            self::REEVALUATE,
            self::REVOKE_SESSION,
            self::REVOKE_TOKENS,
        ], true)) {
            throw new InvalidArgumentException(
                'CAEP access reaction is invalid.'
            );
        }
    }

    public function action(): string
    {
        return $this->action;
    }
}
