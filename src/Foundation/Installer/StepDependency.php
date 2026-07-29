<?php

declare(strict_types=1);

namespace Sif\Foundation\Installer;

use Sif\Foundation\Installer\Exceptions\InvalidStepDependencyException;

final readonly class StepDependency
{
    private InstallationStepIdentifier $step;

    private bool $required;

    public function __construct(
        InstallationStepIdentifier $step,
        bool $required = true,
    ) {
        $this->step = $step;
        $this->required = $required;
    }

    public static function required(InstallationStepIdentifier $step): self
    {
        return new self($step, true);
    }

    public static function optional(InstallationStepIdentifier $step): self
    {
        return new self($step, false);
    }

    public function step(): InstallationStepIdentifier
    {
        return $this->step;
    }

    public function requiredDependency(): bool
    {
        return $this->required;
    }

    /**
     * @param InstallationStepIdentifier $owner
     */
    public function assertNotSelfDependency(InstallationStepIdentifier $owner): void
    {
        if ($this->step->equals($owner)) {
            throw new InvalidStepDependencyException(
                sprintf('Installation step "%s" cannot depend on itself.', $owner->value()),
            );
        }
    }

    /** @return array{step: string, required: bool} */
    public function summary(): array
    {
        return [
            'step' => $this->step->value(),
            'required' => $this->required,
        ];
    }
}
