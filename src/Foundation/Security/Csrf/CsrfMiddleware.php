<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Csrf;

use JsonException;
use Sif\Foundation\Contracts\HttpMiddlewareInterface;
use Sif\Foundation\Contracts\RequestHandlerInterface;
use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Contracts\ResponseInterface;
use Sif\Foundation\Http\Value\HttpStatus;
use Sif\Foundation\Http\Value\Response;
use Sif\Foundation\Http\Value\ResponseBody;
use Sif\Foundation\Session\SessionRequestAttributes;
use Sif\Foundation\Session\SessionState;

final readonly class CsrfMiddleware implements HttpMiddlewareInterface
{
    public function __construct(
        private CsrfTokenManager $manager = new CsrfTokenManager(),
        private CsrfRequestTokenExtractor $extractor = new CsrfRequestTokenExtractor(),
        private CsrfConfiguration $configuration = new CsrfConfiguration(),
    ) {
    }

    public function process(RequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        if (!$this->configuration->protects($request->method())) {
            return $next->handle($request);
        }

        $session = $request->attributes()->get(SessionRequestAttributes::STATE);
        if (!$session instanceof SessionState) {
            return $this->forbidden();
        }

        $result = $this->manager->validate($session, $this->extractor->extract($request));
        if (!$result->isValid()) {
            return $this->forbidden();
        }

        return $next->handle($request);
    }

    private function forbidden(): ResponseInterface
    {
        try {
            $json = json_encode([
                'type' => 'about:blank',
                'title' => 'Forbidden',
                'status' => 403,
                'detail' => 'The request could not be validated.',
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (JsonException) {
            $json = '{"type":"about:blank","title":"Forbidden","status":403,"detail":"The request could not be validated."}';
        }

        return new Response(
            new HttpStatus(403),
            body: new ResponseBody($json, 'application/problem+json', 'utf-8'),
        );
    }
}
