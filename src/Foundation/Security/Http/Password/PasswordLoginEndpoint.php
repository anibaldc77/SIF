<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Http\Password;

use DateTimeImmutable;
use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Contracts\ResponseInterface;
use Sif\Foundation\Http\Value\Response;
use Sif\Foundation\Security\Authentication\AuthenticationRequestId;
use Sif\Foundation\Security\Exceptions\InvalidPasswordLoginRequestException;
use Sif\Foundation\Session\SessionState;

final readonly class PasswordLoginEndpoint
{
    public function __construct(private PasswordSessionLoginService $loginService)
    {
    }

    public function handle(RequestInterface $request, SessionState $session, DateTimeImmutable $now): ResponseInterface
    {
        try {
            $login = PasswordLoginRequest::fromJson($request->body()->contents());
        } catch (InvalidPasswordLoginRequestException) {
            return self::json(['error' => 'invalid_request'], 422);
        }

        $requestId = trim($request->headers()->line('X-Request-ID'));
        if ($requestId === '') {
            $requestId = hash('sha256', $now->format('U.u') . '|' . $login->lookupKey()->value());
        }

        $result = $this->loginService->login($login, new AuthenticationRequestId($requestId), $now, $session);

        if (!$result->isSuccessful()) {
            return self::json(['error' => 'invalid_credentials'], 401)
                ->withHeader('WWW-Authenticate', 'Password realm="application"');
        }

        return self::json(['authenticated' => true], 200);
    }

    /** @param array<string, mixed> $payload */
    private static function json(array $payload, int $status): Response
    {
        return Response::json($payload, $status)
            ->withHeader('Cache-Control', 'no-store')
            ->withHeader('Pragma', 'no-cache');
    }
}
