<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpResolvedPresentation;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpVpTokenEnvelope;

interface OpenId4VpVpTokenProcessorInterface
{
    public function process(
        OpenId4VpVpTokenEnvelope $envelope
    ): OpenId4VpResolvedPresentation;
}
