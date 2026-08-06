<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Http\Password;

use Sif\Foundation\Contracts\ResponseInterface;
use Sif\Foundation\Http\Value\Response;
use Sif\Foundation\Session\SessionState;

final readonly class PasswordLogoutEndpoint
{
    public function __construct(private PasswordSessionLoginService $loginService)
    {
    }

    public function handle(SessionState $session): ResponseInterface
    {
        $this->loginService->logout($session);

        return Response::json(['authenticated' => false], 200)
            ->withHeader('Cache-Control', 'no-store')
            ->withHeader('Pragma', 'no-cache');
    }
}
