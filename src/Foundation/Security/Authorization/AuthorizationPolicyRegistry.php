<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization;

use Sif\Foundation\Security\Contracts\AuthorizationPolicyInterface;
use Sif\Foundation\Security\Exceptions\DuplicateAuthorizationPolicyException;

final class AuthorizationPolicyRegistry
{
    /** @var array<string, AuthorizationPolicyInterface> */
    private array $policies = [];

    public function register(AuthorizationPolicyInterface $policy): void
    {
        $id = $policy->id()->value();
        if (isset($this->policies[$id])) {
            throw new DuplicateAuthorizationPolicyException(sprintf('Authorization policy "%s" is already registered.', $id));
        }
        $this->policies[$id] = $policy;
    }

    /** @return list<AuthorizationPolicyInterface> */
    public function applicableTo(AuthorizationRequest $request): array
    {
        return array_values(array_filter($this->policies, static fn (AuthorizationPolicyInterface $policy): bool => $policy->supports($request)));
    }

    /** @return list<AuthorizationPolicyInterface> */
    public function all(): array { return array_values($this->policies); }
}
