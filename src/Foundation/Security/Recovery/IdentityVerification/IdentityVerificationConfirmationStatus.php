<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Recovery\IdentityVerification;

enum IdentityVerificationConfirmationStatus: string
{
    case Succeeded = 'succeeded';
    case Rejected = 'rejected';
}
