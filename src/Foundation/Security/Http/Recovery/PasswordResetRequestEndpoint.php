<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Http\Recovery;

use DateTimeImmutable;
use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Contracts\ResponseInterface;
use Sif\Foundation\Http\Value\Response;
use Sif\Foundation\Security\Exceptions\SecurityException;
use Sif\Foundation\Security\Recovery\PasswordReset\PasswordResetService;

final readonly class PasswordResetRequestEndpoint
{
    public function __construct(private PasswordResetService $service) {}

    public function handle(RequestInterface $request, DateTimeImmutable $now): ResponseInterface
    {
        try { $payload = RecoveryRequestPayload::fromJson($request->body()->contents()); }
        catch (SecurityException) { return self::json(['accepted' => true], 202); }
        $this->service->request($payload->lookupKey(), $now);
        return self::json(['accepted' => true], 202);
    }

    /** @param array<string,mixed> $payload */
    private static function json(array $payload, int $status): Response
    {
        return Response::json($payload, $status)->withHeader('Cache-Control','no-store')->withHeader('Pragma','no-cache');
    }
}
