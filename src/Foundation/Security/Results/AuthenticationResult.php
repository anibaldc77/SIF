<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Results;

use Sif\Foundation\Security\Exceptions\InvalidAuthenticationResultException;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;

final readonly class AuthenticationResult
{
    private function __construct(
        private ?AuthenticatedPrincipal $principal,
        private ?AuthenticationFailure $failure
    ) {
        if (($principal === null) === ($failure === null)) {
            throw new InvalidAuthenticationResultException(
                'Authentication result must contain exactly one outcome.'
            );
        }
    }

    public static function succeeded(AuthenticatedPrincipal $principal): self
    {
        return new self($principal, null);
    }

    public static function failed(AuthenticationFailureReason $reason): self
    {
        return new self(null, new AuthenticationFailure($reason));
    }

    public function isSuccessful(): bool
    {
        return $this->principal !== null;
    }

    public function principal(): ?AuthenticatedPrincipal
    {
        return $this->principal;
    }

    public function failure(): ?AuthenticationFailure
    {
        return $this->failure;
    }
}
