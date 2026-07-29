<?php

declare(strict_types=1);

namespace Sif\Foundation\Installer\Execution;

use Sif\Foundation\Installer\Exceptions\InvalidMutationExecutionStatusException;

final readonly class MutationExecutionStatus
{
    private const APPLIED = 'applied';
    private const FAILED = 'failed';
    private const COMPENSATED = 'compensated';
    private const COMPENSATION_FAILED = 'compensation-failed';
    private const COMPENSATION_UNSUPPORTED = 'compensation-unsupported';

    private string $value;

    public function __construct(string $value)
    {
        $value = strtolower(trim($value));
        if (!in_array($value, [
            self::APPLIED,
            self::FAILED,
            self::COMPENSATED,
            self::COMPENSATION_FAILED,
            self::COMPENSATION_UNSUPPORTED,
        ], true)) {
            throw new InvalidMutationExecutionStatusException(
                sprintf('Invalid mutation execution status "%s".', $value),
            );
        }

        $this->value = $value;
    }

    public static function applied(): self { return new self(self::APPLIED); }
    public static function failed(): self { return new self(self::FAILED); }
    public static function compensated(): self { return new self(self::COMPENSATED); }
    public static function compensationFailed(): self { return new self(self::COMPENSATION_FAILED); }
    public static function compensationUnsupported(): self { return new self(self::COMPENSATION_UNSUPPORTED); }

    public function value(): string { return $this->value; }
    public function equals(self $other): bool { return $this->value === $other->value; }
    public function __toString(): string { return $this->value; }
}
