<?php

declare(strict_types=1);

namespace Sif\Foundation\Installer;

enum RequirementSeverity: string
{
    case Required = 'required';
    case Optional = 'optional';
}
