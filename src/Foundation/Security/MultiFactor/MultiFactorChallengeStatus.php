<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\MultiFactor;

enum MultiFactorChallengeStatus: string
{
    case Pending = 'pending';
    case Satisfied = 'satisfied';
    case Revoked = 'revoked';
}
