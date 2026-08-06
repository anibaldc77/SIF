<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Recovery;

use Sif\Foundation\Security\Exceptions\InvalidRecoveryChallengeException;

final readonly class RecoverySubjectKey
{
    private string $value;

    public function __construct(string $value)
    {
        $normalized = trim($value);

        if (
            $normalized === ''
            || strlen($normalized) > 255
            || preg_match('/[\x00-\x1F\x7F]/', $normalized) === 1
        ) {
            throw new InvalidRecoveryChallengeException(
                'Recovery subject key must be non-empty, bounded and free of control characters.'
            );
        }

        $this->value = $normalized;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function fingerprint(): string
    {
        return hash('sha256', $this->value);
    }
}
