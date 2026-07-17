<?php

declare(strict_types=1);

namespace Sif\Builder\Metadata;

enum DocumentClass: string
{
    case Normative = 'NormativeDocument';
    case Governance = 'GovernanceDocument';
    case Review = 'ReviewDocument';
    case Informative = 'InformativeDocument';
    case Template = 'TemplateDocument';
}
