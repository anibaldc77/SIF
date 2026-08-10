<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Fapi;

final readonly class FapiSenderConstraintRequirements
{
    /**
     * @param list<FapiSenderConstraintMethod> $allowedMethods
     */
    public function __construct(
        private bool $required = true,
        private array $allowedMethods = []
    ) {
    }

    public function required(): bool
    {
        return $this->required;
    }

    /**
     * @return list<FapiSenderConstraintMethod>
     */
    public function allowedMethods(): array
    {
        return $this->allowedMethods;
    }

    public function allows(FapiSenderConstraintMethod $method): bool
    {
        foreach ($this->allowedMethods as $allowed) {
            if ($allowed->value() === $method->value()) {
                return true;
            }
        }

        return false;
    }
}
