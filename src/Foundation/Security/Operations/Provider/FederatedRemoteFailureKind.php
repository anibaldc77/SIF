<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Operations\Provider;

enum FederatedRemoteFailureKind: string
{
    case Transient = 'transient';
    case Permanent = 'permanent';
    case Unsupported = 'unsupported';
}
