<?php

declare(strict_types=1);

namespace Sif\Foundation\Session\Middleware;

use Sif\Foundation\Contracts\HttpMiddlewareInterface;
use Sif\Foundation\Contracts\RequestHandlerInterface;
use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Contracts\ResponseInterface;
use Sif\Foundation\Http\Cookie\CookieSerializer;
use Sif\Foundation\Session\SessionRequestAttributes;
use Sif\Foundation\Session\SessionRuntime;
use Sif\Foundation\Session\Transport\SessionCookieTransport;

final readonly class SessionMiddleware implements HttpMiddlewareInterface
{
    public function __construct(
        private SessionRuntime $runtime,
        private SessionCookieTransport $transport = new SessionCookieTransport(),
        private CookieSerializer $serializer = new CookieSerializer(),
    ) {
    }

    public function process(RequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        $opened = $this->runtime->open($this->transport->candidateIdentifier($request));
        $session = $this->transport->fromOpenResult($opened);
        $state = $session->state();
        $previousId = $state->id()->value();

        $response = $next->handle($request->withAttribute(SessionRequestAttributes::STATE, $state));

        $this->runtime->commit($state);

        if ($state->destroyed()) {
            return $this->appendSetCookie($response, $this->serializer->serialize($this->transport->removalCookie()));
        }

        if ($session->shouldIssueCookie() || $state->id()->value() !== $previousId) {
            return $this->appendSetCookie($response, $this->serializer->serialize($this->transport->sessionCookie($state)));
        }

        return $response;
    }

    private function appendSetCookie(ResponseInterface $response, string $value): ResponseInterface
    {
        $values = $response->headers()->values('Set-Cookie');
        $values[] = $value;
        return $response->withHeader('Set-Cookie', $values);
    }
}
