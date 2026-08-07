<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization\Requirement;

use InvalidArgumentException;
use Sif\Foundation\Security\Authorization\Permission\ResolvedAuthorizationGrants;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;

final readonly class AllOfRequirement implements AuthorizationRequirementInterface
{
    /** @var list<AuthorizationRequirementInterface> */
    private array $requirements;

    /**
     * @param iterable<AuthorizationRequirementInterface> $requirements
     */
    public function __construct(iterable $requirements)
    {
        $normalized = array_values(
            is_array($requirements)
                ? $requirements
                : iterator_to_array($requirements, false)
        );

        if ($normalized === []) {
            throw new InvalidArgumentException(
                'AllOfRequirement requires at least one nested requirement.'
            );
        }

        $this->requirements = $normalized;
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
}
