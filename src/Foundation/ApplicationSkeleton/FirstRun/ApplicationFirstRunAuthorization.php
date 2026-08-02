<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\FirstRun;

final readonly class ApplicationFirstRunAuthorization
{
    public function __construct(
        private string $planFingerprint,
        private bool $allowed,
    ) {
    }

    public function authorizes(string $fingerprint): bool
    {
        return $this->allowed
            && preg_match('/^[a-f0-9]{64}$/', $this->planFingerprint) === 1
            && hash_equals($this->planFingerprint, $fingerprint);
    }
}
