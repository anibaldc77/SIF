<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Planning;

use Sif\Foundation\Resources\Contribution\CompiledResourceContributionPlan;
use Sif\Foundation\Resources\Filesystem\AuthorizedResourceRoot;
use Sif\Foundation\Resources\Filesystem\AuthorizedResourceRootCollection;
use Sif\Foundation\Resources\Filesystem\SafeFilesystemResourceResolver;
use Sif\Foundation\Resources\Localization\ImmutableTranslationPlan;
use Sif\Foundation\Resources\Publication\CompiledResourcePublicationPlan;
use Sif\Foundation\Resources\Registry\CompiledResourceRegistry;

final readonly class ResourceManagementPlan
{
    /** @var list<AuthorizedResourceRoot> */
    private array $authorizedRoots;

    /** @var array<string, ImmutableTranslationPlan> */
    private array $translationPlans;

    /**
     * @param list<AuthorizedResourceRoot> $authorizedRoots
     * @param array<string, ImmutableTranslationPlan> $translationPlans
     */
    public function __construct(
        private CompiledResourceRegistry $registry,
        private CompiledResourceContributionPlan $contributions,
        array $authorizedRoots = [],
        array $translationPlans = [],
        private ?CompiledResourcePublicationPlan $publication = null,
    ) {
        $rootsByIdentifier = [];
        foreach ($authorizedRoots as $root) {
            $identifier = $root->identifier()->value();
            if (isset($rootsByIdentifier[$identifier])) {
                throw new \InvalidArgumentException(sprintf(
                    'Authorized resource root "%s" occurs more than once in the management plan.',
                    $identifier,
                ));
            }
            $rootsByIdentifier[$identifier] = $root;
        }

        foreach ($translationPlans as $key => $plan) {
            if ($key === '') {
                throw new \InvalidArgumentException('Translation plan keys must not be empty.');
            }
        }

        $this->authorizedRoots = array_values($authorizedRoots);
        $this->translationPlans = $translationPlans;
    }

    public function registry(): CompiledResourceRegistry { return $this->registry; }
    public function contributions(): CompiledResourceContributionPlan { return $this->contributions; }

    /** @return list<AuthorizedResourceRoot> */
    public function authorizedRoots(): array { return $this->authorizedRoots; }

    /** @return array<string, ImmutableTranslationPlan> */
    public function translationPlans(): array { return $this->translationPlans; }

    public function translationPlan(string $key): ?ImmutableTranslationPlan
    {
        return $this->translationPlans[$key] ?? null;
    }

    public function publication(): ?CompiledResourcePublicationPlan { return $this->publication; }

    public function createPathResolver(): SafeFilesystemResourceResolver
    {
        $collection = new AuthorizedResourceRootCollection();
        foreach ($this->authorizedRoots as $root) {
            $collection->add($root);
        }

        return new SafeFilesystemResourceResolver($collection);
    }
}
