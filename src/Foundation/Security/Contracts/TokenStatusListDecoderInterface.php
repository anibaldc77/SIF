<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Status\Token\TokenStatusList;
use Sif\Foundation\Security\VerifiableCredentials\Status\Token\TokenStatusListDecodedData;

interface TokenStatusListDecoderInterface
{
    public function decode(
        TokenStatusList $statusList
    ): TokenStatusListDecodedData;
}
