<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Http\MultiFactor;

use DateTimeImmutable;
use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Contracts\ResponseInterface;
use Sif\Foundation\Http\Value\Response;
use Sif\Foundation\Security\Exceptions\InvalidMultiFactorRequestException;
use Sif\Foundation\Session\SessionState;

final readonly class TotpChallengeResponseEndpoint
{
    public function __construct(private MultiFactorSessionElevationService $service)
    {
    }

    public function handle(
        RequestInterface $request,
        SessionState $session,
        DateTimeImmutable $now
    ): ResponseInterface {
        try {
            $payload = TotpChallengeResponsePayload::fromJson(
                $request->body()->contents()
            );
        } catch (InvalidMultiFactorRequestException) {
            return self::json(['satisfied' => false], 422);
        }

        $result = $this->service->satisfyTotp($payload, $session, $now);

        return self::json(
            ['satisfied' => $result->isSatisfied()],
            $result->isSatisfied() ? 200 : 401
        );
    }

    /** @param array<string,mixed> $payload */
    private static function json(array $payload, int $status): Response
    {
        return Response::json($payload, $status)
            ->withHeader('Cache-Control', 'no-store')
            ->withHeader('Pragma', 'no-cache');
    }
}
