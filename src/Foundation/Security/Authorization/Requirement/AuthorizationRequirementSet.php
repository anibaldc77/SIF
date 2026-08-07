<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization\Requirement;

use Sif\Foundation\Security\Authorization\Permission\ResolvedAuthorizationGrants;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;

final readonly class AuthorizationRequirementSet
{
    /** @var list<AuthorizationRequirementInterface> */
    private array $requirements;

    /**
     * @param iterable<AuthorizationRequirementInterface> $requirements
     */
    public function __construct(iterable $requirements = [])
    {
        $this->requirements = array_values(
            is_array($requirements)
                ? $requirements
                : iterator_to_array($requirements, false)
        );
    }

    public function isSatisfiedBy(
        AuthenticatedPrincipal $principal,
        ResolvedAuthorizationGrants $grants
    ): bool {
        foreach ($this->requirements as $requirement) {
            if (!$requirement->isSatisfiedBy($principal, $grants)) {
                return false;
            }
        }

        return true;
    }

    public function count(): int
    {
        return count($this->requirements);
    }
}
