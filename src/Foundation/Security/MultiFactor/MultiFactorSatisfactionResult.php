<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\MultiFactor;

use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;

final readonly class MultiFactorSatisfactionResult
{
    private function __construct(private bool $satisfied, private ?AuthenticatedPrincipal $principal)
    {
    }

    public static function satisfied(AuthenticatedPrincipal $principal): self { return new self(true, $principal); }
    public static function rejected(): self { return new self(false, null); }
    public function isSatisfied(): bool { return $this->satisfied; }
    public function principal(): ?AuthenticatedPrincipal { return $this->principal; }
}
