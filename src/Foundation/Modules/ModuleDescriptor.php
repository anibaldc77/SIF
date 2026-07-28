<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules;

use Sif\Foundation\Modules\Exceptions\InvalidModuleDescriptorException;

final readonly class ModuleDescriptor
{
    /**
     * @param list<ModuleDependency> $requiredDependencies
     * @param list<ModuleDependency> $optionalDependencies
     * @param list<ModuleConflict> $conflicts
     * @param list<non-empty-string> $requiredCapabilities
     * @param list<non-empty-string> $providedCapabilities
     * @param list<class-string> $serviceProviders
     * @param array<string, scalar|null> $diagnosticMetadata
     */
    public function __construct(
        private ModuleId $id,
        private ModuleVersion $version,
        private string $name,
        private ?string $description = null,
        private array $requiredDependencies = [],
        private array $optionalDependencies = [],
        private array $conflicts = [],
        private array $requiredCapabilities = [],
        private array $providedCapabilities = [],
        private ?string $configurationNamespace = null,
        private array $serviceProviders = [],
        private array $diagnosticMetadata = [],
    ) {
        if (trim($name) === '') { throw InvalidModuleDescriptorException::emptyName(); }
        $this->validateRelations();
        $this->assertUniqueStrings($requiredCapabilities, 'required capability');
        $this->assertUniqueStrings($providedCapabilities, 'provided capability');
        $this->assertUniqueStrings($serviceProviders, 'service provider');
    }

    public function id(): ModuleId { return $this->id; }
    public function version(): ModuleVersion { return $this->version; }
    public function name(): string { return $this->name; }
    public function description(): ?string { return $this->description; }
    /** @return list<ModuleDependency> */ public function requiredDependencies(): array { return $this->requiredDependencies; }
    /** @return list<ModuleDependency> */ public function optionalDependencies(): array { return $this->optionalDependencies; }
    /** @return list<ModuleConflict> */ public function conflicts(): array { return $this->conflicts; }
    /** @return list<non-empty-string> */ public function requiredCapabilities(): array { return $this->requiredCapabilities; }
    /** @return list<non-empty-string> */ public function providedCapabilities(): array { return $this->providedCapabilities; }
    public function configurationNamespace(): ?string { return $this->configurationNamespace; }
    /** @return list<class-string> */ public function serviceProviders(): array { return $this->serviceProviders; }
    /** @return array<string, scalar|null> */ public function diagnosticMetadata(): array { return $this->diagnosticMetadata; }

    private function validateRelations(): void
    {
        $dependencies = [];
        foreach (array_merge($this->requiredDependencies, $this->optionalDependencies) as $dependency) {
            $id = $dependency->moduleId()->value();
            if ($id === $this->id->value()) { throw InvalidModuleDescriptorException::selfReference($id); }
            if (isset($dependencies[$id])) { throw InvalidModuleDescriptorException::duplicateRelation($id); }
            $dependencies[$id] = true;
        }
        $conflicts = [];
        foreach ($this->conflicts as $conflict) {
            $id = $conflict->moduleId()->value();
            if ($id === $this->id->value()) { throw InvalidModuleDescriptorException::selfReference($id); }
            if (isset($conflicts[$id])) { throw InvalidModuleDescriptorException::duplicateRelation($id); }
            if (isset($dependencies[$id])) { throw InvalidModuleDescriptorException::contradictoryRelation($id); }
            $conflicts[$id] = true;
        }
    }

    /** @param list<string> $values */
    private function assertUniqueStrings(array $values, string $label): void
    {
        $seen = [];
        foreach ($values as $value) {
            if ($value === '' || isset($seen[$value])) { throw new \InvalidArgumentException(sprintf('Invalid or duplicate %s "%s".', $label, $value)); }
            $seen[$value] = true;
        }
    }
}
