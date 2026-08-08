<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Scim\Versioning;

use Sif\Foundation\Security\Contracts\ScimPreconditionEvaluatorInterface;

final readonly class DefaultScimPreconditionEvaluator implements ScimPreconditionEvaluatorInterface
{
    public function evaluate(
        ScimPrecondition $precondition,
        ?ScimResourceVersion $currentVersion
    ): ScimPreconditionResult {
        if ($precondition->type() === ScimPrecondition::IF_MATCH) {
            if ($precondition->wildcard()) {
                return new ScimPreconditionResult(
                    $currentVersion !== null,
                    $currentVersion !== null
                        ? null
                        : 'resource_missing'
                );
            }

            if ($currentVersion === null) {
                return new ScimPreconditionResult(
                    false,
                    'resource_missing'
                );
            }

            foreach ($precondition->entityTags() as $tag) {
                if ($tag->matches($currentVersion)) {
                    return new ScimPreconditionResult(true);
                }
            }

            return new ScimPreconditionResult(
                false,
                'version_mismatch'
            );
        }

        if ($precondition->wildcard()) {
            return new ScimPreconditionResult(
                $currentVersion === null,
                $currentVersion === null
                    ? null
                    : 'resource_exists'
            );
        }

        if ($currentVersion === null) {
            return new ScimPreconditionResult(true);
        }

        foreach ($precondition->entityTags() as $tag) {
            if ($tag->matches($currentVersion)) {
                return new ScimPreconditionResult(
                    false,
                    'version_matches_disallowed'
                );
            }
        }

        return new ScimPreconditionResult(true);
    }
}
