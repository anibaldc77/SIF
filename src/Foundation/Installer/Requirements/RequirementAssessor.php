<?php

declare(strict_types=1);

namespace Sif\Foundation\Installer\Requirements;

use Sif\Foundation\Installer\Contracts\RequirementProbeInterface;
use Sif\Foundation\Installer\Exceptions\DuplicateRequirementProbeException;
use Sif\Foundation\Installer\Exceptions\InvalidRequirementProbeException;
use Sif\Foundation\Installer\Exceptions\RequirementProbeExecutionException;
use Sif\Foundation\Installer\InstallationRequest;
use Sif\Foundation\Installer\RequirementAssessmentReport;

final class RequirementAssessor
{
    /**
     * @param iterable<RequirementProbeInterface> $probes
     */
    public function assess(
        InstallationRequest $request,
        iterable $probes,
    ): RequirementAssessmentReport {
        $registered = [];
        $seen = [];
        $registrationOrder = 0;

        foreach ($probes as $probe) {
            if (!$probe instanceof RequirementProbeInterface) {
                throw new InvalidRequirementProbeException(
                    'Requirement assessor members must implement RequirementProbeInterface.',
                );
            }

            $identifier = $probe->identifier()->value();
            if (isset($seen[$identifier])) {
                throw new DuplicateRequirementProbeException(
                    sprintf('Requirement probe "%s" is registered more than once.', $identifier),
                );
            }

            $seen[$identifier] = true;
            $registered[] = [
                'probe' => $probe,
                'order' => $registrationOrder++,
            ];
        }

        usort(
            $registered,
            static function (array $left, array $right): int {
                /** @var RequirementProbeInterface $leftProbe */
                $leftProbe = $left['probe'];
                /** @var RequirementProbeInterface $rightProbe */
                $rightProbe = $right['probe'];

                $priority = $leftProbe->priority() <=> $rightProbe->priority();

                return $priority !== 0
                    ? $priority
                    : $left['order'] <=> $right['order'];
            },
        );

        $results = [];
        foreach ($registered as $entry) {
            /** @var RequirementProbeInterface $probe */
            $probe = $entry['probe'];

            try {
                $result = $probe->probe($request);
            } catch (\Throwable $throwable) {
                throw new RequirementProbeExecutionException(
                    sprintf('Requirement probe "%s" failed during read-only assessment.', $probe->identifier()->value()),
                    0,
                    $throwable,
                );
            }

            if (!$result->identifier()->equals($probe->identifier())) {
                throw new InvalidRequirementProbeException(
                    sprintf('Requirement probe "%s" returned a result for a different identifier.', $probe->identifier()->value()),
                );
            }

            if ($result->severity() !== $probe->severity()) {
                throw new InvalidRequirementProbeException(
                    sprintf('Requirement probe "%s" returned a result with a different severity.', $probe->identifier()->value()),
                );
            }

            $results[] = $result;
        }

        return new RequirementAssessmentReport($results);
    }
}
