<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\OAuth\Advanced;
final readonly class OAuthResourceServerPolicy
{
    public function __construct(
        private bool $requireSenderConstraint = true,
        private bool $requireDpopProof = true
    ) {
    }
    public function requireSenderConstraint(): bool { return $this->requireSenderConstraint; }
    public function requireDpopProof(): bool { return $this->requireDpopProof; }
}
