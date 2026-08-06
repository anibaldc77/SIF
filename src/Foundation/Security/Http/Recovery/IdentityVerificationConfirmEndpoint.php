<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Http\Recovery;

use DateTimeImmutable;
use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Contracts\ResponseInterface;
use Sif\Foundation\Http\Value\Response;
use Sif\Foundation\Security\Exceptions\SecurityException;
use Sif\Foundation\Security\Recovery\IdentityVerification\IdentityVerificationService;

final readonly class IdentityVerificationConfirmEndpoint
{
    public function __construct(private IdentityVerificationService $service) {}

    public function handle(RequestInterface $request, DateTimeImmutable $now): ResponseInterface
    {
        try { $payload = RecoveryConfirmationPayload::fromJson($request->body()->contents()); }
        catch (SecurityException) { return self::json(['verified' => false], 400); }
        $result = $this->service->confirm($payload->challengeId(), $payload->token(), $now);
        return self::json(['verified' => $result->isSucceeded()], $result->isSucceeded() ? 200 : 400);
    }

    /** @param array<string,mixed> $payload */
    private static function json(array $payload, int $status): Response
    {
        return Response::json($payload, $status)->withHeader('Cache-Control','no-store')->withHeader('Pragma','no-cache');
    }
}
