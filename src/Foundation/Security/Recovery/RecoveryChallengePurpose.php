<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Recovery;

enum RecoveryChallengePurpose: string
{
    case PasswordReset = 'password_reset';
    case IdentityVerification = 'identity_verification';
}
