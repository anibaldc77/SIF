<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Status;

enum CredentialStatusMechanism: string
{
    case BitstringStatusList = 'bitstring_status_list';
    case TokenStatusList = 'token_status_list';
}
