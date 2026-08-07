<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization\Requirement;

use Sif\Foundation\Security\Authorization\Attribute\AuthorizationAttributeContext;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;

final readonly class ContextualRequirementSet
{
    /** @var list<ContextualAuthorizationRequirementInterface> */
    private array $requirements;

    /**
     * @param iterable<ContextualAuthorizationRequirementInterface> $requirements
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
        AuthorizationAttributeContext $attributes
    ): bool {
        foreach ($this->requirements as $requirement) {
            if (!$requirement->isSatisfiedBy($principal, $attributes)) {
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
