<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpPresentationContext;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpTransactionData;

interface OpenId4VpTransactionBindingPolicyInterface
{
    public function validate(
        OpenId4VpTransactionData $transactionData,
        OpenId4VpPresentationContext $context
    ): void;
}
