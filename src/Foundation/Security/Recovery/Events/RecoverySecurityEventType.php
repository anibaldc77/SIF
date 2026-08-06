<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Recovery\Events;

enum RecoverySecurityEventType: string
{
    case RequestAccepted = 'request_accepted';
    case RequestBlocked = 'request_blocked';
    case ChallengeIssued = 'challenge_issued';
    case ChallengeConsumed = 'challenge_consumed';
    case ChallengeRejected = 'challenge_rejected';
}
