<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Scim\Versioning;

use Sif\Foundation\Security\Contracts\ScimPreconditionEvaluatorInterface;
use Sif\Foundation\Security\Exceptions\ScimPreconditionFailedException;

final readonly class ScimVersionGuard
{
    public function __construct(
        private ScimPreconditionEvaluatorInterface $evaluator
    ) {
    }

    public function assertSatisfied(
        ScimPrecondition $precondition,
        ?ScimResourceVersion $currentVersion
    ): void {
        $result = $this->evaluator->evaluate(
            $precondition,
            $currentVersion
        );

        if ($result->satisfied()) {
            return;
        }

        throw new ScimPreconditionFailedException(
            'SCIM precondition failed'
            . (
                $result->reason() !== null
                    ? ': ' . $result->reason()
                    : '.'
            )
        );
    }
}
