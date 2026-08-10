<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\SharedSignals;

use InvalidArgumentException;

final readonly class ContinuousAccessAction
{
    public const NONE = 'none';
    public const REEVALUATE = 'reevaluate';
    public const REQUIRE_REAUTHENTICATION = 'require-reauthentication';
    public const REVOKE_SESSION = 'revoke-session';
    public const REVOKE_TOKENS = 'revoke-tokens';
    public const DISABLE_ACCESS = 'disable-access';

    public function __construct(private string $value)
    {
        if (!in_array($this->value, [
            self::NONE,
            self::REEVALUATE,
            self::REQUIRE_REAUTHENTICATION,
            self::REVOKE_SESSION,
            self::REVOKE_TOKENS,
            self::DISABLE_ACCESS,
        ], true)) {
            throw new InvalidArgumentException(
                'Continuous access action is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
