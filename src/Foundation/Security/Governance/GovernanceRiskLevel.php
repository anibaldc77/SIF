<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Governance;

use InvalidArgumentException;
use LogicException;

final readonly class GovernanceRiskLevel
{
    public const LOW = 'low';
    public const MEDIUM = 'medium';
    public const HIGH = 'high';
    public const CRITICAL = 'critical';

    public function __construct(private string $value)
    {
        if (!in_array(
            $this->value,
            [
                self::LOW,
                self::MEDIUM,
                self::HIGH,
                self::CRITICAL,
            ],
            true
        )) {
            throw new InvalidArgumentException(
                'Governance risk level is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function weight(): int
    {
        return match ($this->value) {
            self::LOW => 10,
            self::MEDIUM => 30,
            self::HIGH => 60,
            self::CRITICAL => 100,
            default => throw new LogicException(
                'Governance risk level is in an invalid internal state.'
            ),
        };
    }
}
