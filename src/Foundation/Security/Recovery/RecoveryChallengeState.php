<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Recovery;

enum RecoveryChallengeState: string
{
    case Pending = 'pending';
    case Consumed = 'consumed';
    case Revoked = 'revoked';
}
