<?php

declare(strict_types=1);

namespace Sif\Foundation\Container;

enum ContainerDiagnosticCode: string
{
    case AliasTargetMissing = 'CONTAINER-001';
    case AliasCycle = 'CONTAINER-002';
    case AutowiredClassMissing = 'CONTAINER-003';
    case ContextualConsumerMissing = 'CONTAINER-004';
    case ContextualServiceMissing = 'CONTAINER-005';
}
