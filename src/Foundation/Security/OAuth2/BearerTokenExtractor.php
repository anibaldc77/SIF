<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth2;

use Sif\Foundation\Security\Contracts\BearerTokenExtractorInterface;
use Sif\Foundation\Security\Exceptions\InvalidBearerTokenException;

final readonly class BearerTokenExtractor implements BearerTokenExtractorInterface
{
    public function extract(string $authorizationHeader): ?AccessToken
    {
        $header = trim($authorizationHeader);

        if ($header === '') {
            return null;
        }

        if (preg_match('/^Bearer\s+(.+)$/i', $header, $matches) !== 1) {
            throw new InvalidBearerTokenException(
                'Authorization header does not contain a valid Bearer token.'
            );
        }

        $token = trim($matches[1]);

        if ($token === '' || preg_match('/\s/', $token) === 1) {
            throw new InvalidBearerTokenException(
                'Bearer token must be a single non-empty token value.'
            );
        }

        return new AccessToken($token);
    }
}
