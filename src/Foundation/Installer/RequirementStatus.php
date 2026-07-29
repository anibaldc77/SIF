<?php

declare(strict_types=1);

namespace Sif\Foundation\Installer;

enum RequirementStatus: string
{
    case Passed = 'passed';
    case Failed = 'failed';
}
