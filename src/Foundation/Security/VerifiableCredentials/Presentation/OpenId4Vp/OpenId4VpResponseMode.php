<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp;

enum OpenId4VpResponseMode: string
{
    case Query = 'query';
    case Fragment = 'fragment';
    case DirectPost = 'direct_post';
    case DirectPostJwt = 'direct_post.jwt';
}
