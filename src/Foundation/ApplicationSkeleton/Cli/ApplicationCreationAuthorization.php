<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Cli;

use Sif\Foundation\ApplicationSkeleton\Exceptions\ApplicationSkeletonException;

final readonly class ApplicationCreationAuthorization
{
    public function __construct(
        private string $planFingerprint,
        private bool $executionAllowed,
    ) {
        if (preg_match('/^[a-f0-9]{64}$/', $this->planFingerprint) !== 1) {
            throw new ApplicationSkeletonException('Application creation authorization requires a SHA-256 plan fingerprint.');
        }
    }

    public function planFingerprint(): string { return $this->planFingerprint; }
    public function executionAllowed(): bool { return $this->executionAllowed; }

    public function authorizes(string $fingerprint): bool
    {
        return $this->executionAllowed && hash_equals($this->planFingerprint, $fingerprint);
    }
}
