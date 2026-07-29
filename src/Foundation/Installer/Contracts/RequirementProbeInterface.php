<?php

declare(strict_types=1);

namespace Sif\Foundation\Installer\Contracts;

use Sif\Foundation\Installer\InstallationRequest;
use Sif\Foundation\Installer\RequirementIdentifier;
use Sif\Foundation\Installer\RequirementProbeResult;
use Sif\Foundation\Installer\RequirementSeverity;

interface RequirementProbeInterface
{
    public function identifier(): RequirementIdentifier;

    public function severity(): RequirementSeverity;

    public function priority(): int;

    public function probe(InstallationRequest $request): RequirementProbeResult;
}
