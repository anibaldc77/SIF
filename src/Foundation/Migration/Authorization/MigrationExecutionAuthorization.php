<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Authorization;

use Sif\Foundation\Migration\Exceptions\InvalidMigrationExecutionAuthorizationException;
use Sif\Foundation\Migration\MigrationDirection;
use Sif\Foundation\Migration\MigrationExecutionMode;

final readonly class MigrationExecutionAuthorization
{
    public function __construct(
        private string $authorizationId,
        private string $planFingerprint,
        private MigrationDirection $direction,
        private MigrationExecutionMode $mode,
        private bool $executionAllowed,
    ) {
        if (preg_match('/^[A-Za-z0-9][A-Za-z0-9._:-]*$/D', $authorizationId) !== 1) {
            throw new InvalidMigrationExecutionAuthorizationException('Authorization id must be a safe non-empty token.');
        }
        if (preg_match('/^[a-f0-9]{64}$/D', $planFingerprint) !== 1) {
            throw new InvalidMigrationExecutionAuthorizationException('Authorization fingerprint must be a SHA-256 digest.');
        }
    }

    public function authorizationId(): string
    {
        return $this->authorizationId;
    }

    public function planFingerprint(): string
    {
        return $this->planFingerprint;
    }

    public function direction(): MigrationDirection
    {
        return $this->direction;
    }

    public function mode(): MigrationExecutionMode
    {
        return $this->mode;
    }

    public function executionAllowed(): bool
    {
        return $this->executionAllowed;
    }
}
