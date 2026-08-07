<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\MultiFactor;

enum MultiFactorChallengePurpose: string
{
    case AuthenticationContinuation = 'authentication_continuation';
    case StepUp = 'step_up';
}
