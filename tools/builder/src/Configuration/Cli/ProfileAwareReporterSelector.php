<?php

declare(strict_types=1);

namespace Sif\Builder\Configuration\Cli;

use Sif\Builder\Cli\Contract\ReporterSelectorInterface;
use Sif\Builder\Cli\Exception\ReporterSelectionException;
use Sif\Builder\Cli\Reporting\ReporterSelector;
use Sif\Builder\Engine\Contract\ReporterInterface;

final readonly class ProfileAwareReporterSelector implements ReporterSelectorInterface
{
    public function __construct(
        private ResolvedCliConfigurationStore $store,
        private ReporterSelector $delegate = new ReporterSelector(),
    ) {
    }

    public function select(?string $format): ReporterInterface
    {
        $allowed = $this->store->current()->profile->reporters;
        if ($allowed === []) {
            throw new ReporterSelectionException('The selected build profile does not enable a reporter.');
        }

        $reporter = $this->delegate->select($format ?? $allowed[0]);
        if (!in_array($reporter->id(), $allowed, true)) {
            throw new ReporterSelectionException(sprintf(
                'Reporter "%s" is not enabled by build profile "%s".',
                $reporter->id(),
                $this->store->current()->profile->identifier,
            ));
        }

        return $reporter;
    }
}
