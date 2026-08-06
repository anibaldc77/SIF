<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Recovery\PasswordReset;

enum PasswordResetConfirmationStatus: string
{
    case Succeeded = 'succeeded';
    case Rejected = 'rejected';
}
