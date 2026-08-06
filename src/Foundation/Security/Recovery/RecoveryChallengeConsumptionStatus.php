<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Recovery;

enum RecoveryChallengeConsumptionStatus: string
{
    case Consumed = 'consumed';
    case NotFound = 'not_found';
    case PurposeMismatch = 'purpose_mismatch';
    case TokenMismatch = 'token_mismatch';
    case Expired = 'expired';
    case AlreadyConsumed = 'already_consumed';
    case Revoked = 'revoked';
}
