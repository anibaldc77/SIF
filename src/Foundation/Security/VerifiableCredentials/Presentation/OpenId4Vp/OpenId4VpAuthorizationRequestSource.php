<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp;

enum OpenId4VpAuthorizationRequestSource: string
{
    case InlineParameters = 'inline_parameters';
    case RequestObject = 'request_object';
    case RequestUri = 'request_uri';
}
