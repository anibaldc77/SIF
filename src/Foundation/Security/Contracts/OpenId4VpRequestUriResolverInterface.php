<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpRequestObject;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpRequestUri;

interface OpenId4VpRequestUriResolverInterface
{
    public function resolve(OpenId4VpRequestUri $requestUri): OpenId4VpRequestObject;
}
