<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Fapi;

use InvalidArgumentException;

final readonly class FapiSenderConstraintMethod
{
    public const DPOP = 'dpop';
    public const MTLS = 'mtls';

    public function __construct(private string $value)
    {
        if (!in_array($this->value, [self::DPOP, self::MTLS], true)) {
            throw new InvalidArgumentException(
                'FAPI sender constraint method is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
