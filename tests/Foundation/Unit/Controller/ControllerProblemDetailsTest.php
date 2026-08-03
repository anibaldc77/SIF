<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Foundation\Controller\Argument\ActionArgumentIssue;
use Sif\Foundation\Controller\Argument\ActionArgumentSource;
use Sif\Foundation\Controller\Exceptions\ControllerArgumentResolutionException;
use Sif\Foundation\Controller\Exceptions\ControllerValidationException;
use Sif\Foundation\Controller\Problem\ControllerExceptionHandler;
use Sif\Foundation\Controller\Problem\ExceptionMapperRegistry;
use Sif\Foundation\Controller\Problem\ExceptionMapping;
use Sif\Foundation\Controller\Validation\ValidationIssue;
use Sif\Foundation\Controller\Validation\ValidationResult;
use Sif\Foundation\Http\Value\HttpMethod;
use Sif\Foundation\Http\Value\Request;
use Sif\Foundation\Http\Value\Uri;

final class ControllerProblemDetailsTest extends TestCase
{
    public function testArgumentIssuesBecomeSafeProblemDetails(): void
    {
        $exception = new ControllerArgumentResolutionException([
            new ActionArgumentIssue('invalid_integer', 'id', ActionArgumentSource::Route, 'The value must be an integer.'),
        ]);
        $response = (new ControllerExceptionHandler())->handle($exception, $this->request());
        $payload = json_decode($response->body()->contents(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(400, $response->status()->code());
        self::assertSame('application/problem+json; charset=utf-8', $response->headers()->line('Content-Type'));
        self::assertSame('route.id', $payload['errors'][0]['path']);
    }

    public function testValidationIssuesBecomeUnprocessableEntity(): void
    {
        $exception = new ControllerValidationException(new ValidationResult([
            new ValidationIssue('required', 'body.email', 'The value is required.'),
        ]));
        $response = (new ControllerExceptionHandler())->handle($exception, $this->request());

        self::assertSame(422, $response->status()->code());
        self::assertStringContainsString('body.email', $response->body()->contents());
    }

    public function testExplicitExceptionMappingDoesNotExposeThrowableMessage(): void
    {
        $registry = new ExceptionMapperRegistry();
        $registry->register(new ExceptionMapping(
            RuntimeException::class,
            409,
            'https://example.test/problems/conflict',
            'Conflict',
            'The operation conflicts with the current state.',
        ));
        $response = (new ControllerExceptionHandler($registry))->handle(
            new RuntimeException('database password was secret'),
            $this->request(),
        );

        self::assertSame(409, $response->status()->code());
        self::assertStringNotContainsString('password', $response->body()->contents());
    }

    public function testUnexpectedThrowableUsesSafeFallback(): void
    {
        $response = (new ControllerExceptionHandler())->handle(
            new RuntimeException('internal path C:\\secret\\file.php'),
            $this->request(),
        );

        self::assertSame(500, $response->status()->code());
        self::assertSame(
            '{"detail":"An unexpected error occurred.","instance":"/widgets/7","status":500,"title":"Internal server error","type":"https://sif.dev/problems/internal-error"}',
            $response->body()->contents(),
        );
    }

    private function request(): Request
    {
        return new Request(HttpMethod::Get, new Uri(path: '/widgets/7'));
    }
}
