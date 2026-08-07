<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization\Requirement;

use InvalidArgumentException;
use Sif\Foundation\Security\Authorization\Attribute\AuthorizationAttributeComparison;
use Sif\Foundation\Security\Authorization\Attribute\AuthorizationAttributeContext;
use Sif\Foundation\Security\Authorization\Attribute\AuthorizationAttributeScope;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;

final readonly class AttributeRequirement implements ContextualAuthorizationRequirementInterface
{
    private string $name;

    public function __construct(
        AuthorizationAttributeScope $scope,
        string $name,
        private AuthorizationAttributeComparison $comparison,
        private string|int|float|bool|null $expected
    ) {
        $normalized = strtolower(trim($name));

        if (
            strlen($normalized) < 1
            || strlen($normalized) > 120
            || preg_match('/^[a-z0-9][a-z0-9._:-]*$/', $normalized) !== 1
        ) {
            throw new InvalidArgumentException(
                'Authorization attribute requirement name is invalid.'
            );
        }

        $this->scope = $scope;
        $this->name = $normalized;
    }

    private AuthorizationAttributeScope $scope;

    public function isSatisfiedBy(
        AuthenticatedPrincipal $principal,
        AuthorizationAttributeContext $attributes
    ): bool {
        $bag = $attributes->bag($this->scope);

        if (!$bag->has($this->name)) {
            return false;
        }

        $actual = $bag->get($this->name);

        return match ($this->comparison) {
            AuthorizationAttributeComparison::Equals =>
                $actual === $this->expected,
            AuthorizationAttributeComparison::NotEquals =>
                $actual !== $this->expected,
            AuthorizationAttributeComparison::GreaterThan =>
                $this->compareNumbers($actual, static fn (float $a, float $b): bool => $a > $b),
            AuthorizationAttributeComparison::GreaterThanOrEqual =>
                $this->compareNumbers($actual, static fn (float $a, float $b): bool => $a >= $b),
            AuthorizationAttributeComparison::LessThan =>
                $this->compareNumbers($actual, static fn (float $a, float $b): bool => $a < $b),
            AuthorizationAttributeComparison::LessThanOrEqual =>
                $this->compareNumbers($actual, static fn (float $a, float $b): bool => $a <= $b),
        };
    }

    /**
     * @param callable(float, float): bool $comparison
     */
    private function compareNumbers(
        string|int|float|bool|null $actual,
        callable $comparison
    ): bool {
        if (
            !is_int($actual)
            && !is_float($actual)
        ) {
            return false;
        }

        if (
            !is_int($this->expected)
            && !is_float($this->expected)
        ) {
            return false;
        }

        return $comparison((float) $actual, (float) $this->expected);
    }
}
