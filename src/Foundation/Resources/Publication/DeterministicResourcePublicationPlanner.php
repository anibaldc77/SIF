<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Publication;

use Sif\Foundation\Resources\Contracts\ResourcePublicationPlannerInterface;
use Sif\Foundation\Resources\Exceptions\DuplicateResourcePublicationException;
use Sif\Foundation\Resources\Exceptions\ResourcePublicationTargetCollisionException;

final class DeterministicResourcePublicationPlanner implements ResourcePublicationPlannerInterface
{
    public function compile(array $requests): CompiledResourcePublicationPlan
    {
        $resourceIdentities = [];
        $targetPaths = [];
        $publications = [];

        foreach (array_values($requests) as $order => $request) {
            $qualifiedIdentifier = $request->qualifiedIdentifier();
            if (isset($resourceIdentities[$qualifiedIdentifier])) {
                throw new DuplicateResourcePublicationException(sprintf('Resource "%s" was requested for publication more than once.', $qualifiedIdentifier));
            }

            $portableTarget = strtolower($request->targetPath()->value());
            if (isset($targetPaths[$portableTarget])) {
                throw new ResourcePublicationTargetCollisionException(sprintf(
                    'Publication target "%s" collides with resource "%s".',
                    $request->targetPath()->value(),
                    $targetPaths[$portableTarget],
                ));
            }

            $resourceIdentities[$qualifiedIdentifier] = true;
            $targetPaths[$portableTarget] = $qualifiedIdentifier;
            $publications[] = new PlannedResourcePublication($request, $order);
        }

        usort($publications, static function (PlannedResourcePublication $left, PlannedResourcePublication $right): int {
            $targetComparison = strcmp(
                $left->request()->targetPath()->value(),
                $right->request()->targetPath()->value(),
            );

            if ($targetComparison !== 0) {
                return $targetComparison;
            }

            return $left->publicationOrder() <=> $right->publicationOrder();
        });

        $manifest = new ImmutableResourceManifest(
            array_map(static fn (PlannedResourcePublication $publication): ResourceManifestEntry => new ResourceManifestEntry($publication), $publications),
        );

        return new CompiledResourcePublicationPlan($publications, $manifest);
    }
}
