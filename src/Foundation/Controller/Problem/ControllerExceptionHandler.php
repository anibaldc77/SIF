<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Problem;

use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Contracts\ResponseInterface;
use Sif\Foundation\Controller\Api\ApiJsonEncoder;
use Sif\Foundation\ErrorHandling\Contracts\ErrorHandlerInterface;
use Sif\Foundation\ErrorHandling\FailureOrigin;
use Sif\Foundation\Http\Value\HeaderBag;
use Sif\Foundation\Http\Value\HttpStatus;
use Sif\Foundation\Http\Value\Response;
use Sif\Foundation\Http\Value\ResponseBody;
use Throwable;

final readonly class ControllerExceptionHandler
{
    public function __construct(
        private ExceptionMapperRegistry $mappings = new ExceptionMapperRegistry(),
        private ProblemDetailsFactory $problems = new ProblemDetailsFactory(),
        private ApiJsonEncoder $encoder = new ApiJsonEncoder(),
        private ?ErrorHandlerInterface $errorHandler = null,
    ) {
    }

    public function handle(Throwable $throwable, RequestInterface $request): ResponseInterface
    {
        $mapping = $this->mappings->resolve($throwable);
        $failureId = null;

        if ($mapping === null && $this->errorHandler !== null) {
            $result = $this->errorHandler->handle(
                $throwable,
                new FailureOrigin('controller.http'),
                [
                    'http.method' => $request->method()->value,
                    'http.path' => $request->uri()->path(),
                ],
            );
            $failureId = $result->envelope()->id()->value();
        }

        $problem = $this->problems->fromThrowable($throwable, $request, $mapping, $failureId);

        return new Response(
            new HttpStatus($problem->status()),
            headers: new HeaderBag(['Content-Type' => 'application/problem+json; charset=utf-8']),
            body: new ResponseBody(
                $this->encoder->encode($problem->toArray()),
                'application/problem+json',
                'utf-8',
            ),
        );
    }
}
