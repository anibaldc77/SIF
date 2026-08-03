<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Error;

use Sif\Foundation\ErrorHandling\Orchestration\ErrorHandlingResult;
use Sif\Foundation\Http\Value\Response;

final class HttpErrorResponseFactory
{
    public function notFound(): Response
    {
        return Response::json([
            'error' => [
                'code' => 'route_not_found',
                'message' => 'The requested resource was not found.',
            ],
        ], 404);
    }

    /** @param list<string> $allowedMethods */
    public function methodNotAllowed(array $allowedMethods): Response
    {
        $response = Response::json([
            'error' => [
                'code' => 'method_not_allowed',
                'message' => 'The request method is not allowed for this resource.',
            ],
        ], 405);

        if ($allowedMethods === []) {
            return $response;
        }

        return $response->withHeader('Allow', implode(', ', $allowedMethods));
    }

    public function internalFailure(ErrorHandlingResult $result): Response
    {
        return Response::json([
            'error' => [
                'code' => 'internal_error',
                'message' => 'The request could not be completed.',
                'failure_id' => $result->envelope()->id()->value(),
            ],
        ], 500);
    }
}
