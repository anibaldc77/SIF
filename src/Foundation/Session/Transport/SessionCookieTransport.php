<?php

declare(strict_types=1);

namespace Sif\Foundation\Session\Transport;

use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Http\Cookie\Cookie;
use Sif\Foundation\Http\Cookie\CookieExpiration;
use Sif\Foundation\Http\Cookie\CookieName;
use Sif\Foundation\Http\Cookie\CookieValue;
use Sif\Foundation\Session\SessionOpenResult;
use Sif\Foundation\Session\SessionState;

final readonly class SessionCookieTransport
{
    public function __construct(private SessionCookieConfiguration $configuration = new SessionCookieConfiguration())
    {
    }

    public function candidateIdentifier(RequestInterface $request): ?string
    {
        $value = $request->cookies()->get($this->configuration->name());
        return is_string($value) && $value !== '' ? $value : null;
    }

    public function fromOpenResult(SessionOpenResult $result): SessionTransportResult
    {
        return new SessionTransportResult(
            $result->state(),
            $result->identifierAccepted(),
            $result->expiredRecordDiscarded(),
        );
    }

    public function sessionCookie(SessionState $state): Cookie
    {
        return new Cookie(
            new CookieName($this->configuration->name()),
            new CookieValue($state->id()->value()),
            $this->configuration->path(),
            $this->configuration->domain(),
            $this->configuration->secure(),
            $this->configuration->httpOnly(),
            $this->configuration->sameSite(),
            new CookieExpiration(maxAge: $this->configuration->maxAge()),
        );
    }

    public function removalCookie(): Cookie
    {
        return Cookie::removal(
            $this->configuration->name(),
            $this->configuration->path(),
            $this->configuration->domain(),
            $this->configuration->secure(),
            $this->configuration->httpOnly(),
            $this->configuration->sameSite(),
        );
    }
}
