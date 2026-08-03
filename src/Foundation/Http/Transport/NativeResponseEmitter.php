<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Transport;

use Closure;
use Sif\Foundation\Contracts\ResponseEmitterInterface;
use Sif\Foundation\Contracts\ResponseInterface;
use Sif\Foundation\Http\Exceptions\ResponseAlreadyEmittedException;

final class NativeResponseEmitter implements ResponseEmitterInterface
{
    /** @var Closure(int, string, string): void */
    private Closure $statusWriter;

    /** @var Closure(string, string, bool): void */
    private Closure $headerWriter;

    /** @var Closure(string): void */
    private Closure $bodyWriter;

    private bool $emitted = false;

    /**
     * @param null|callable(int, string, string): void $statusWriter
     * @param null|callable(string, string, bool): void $headerWriter
     * @param null|callable(string): void $bodyWriter
     */
    public function __construct(
        ?callable $statusWriter = null,
        ?callable $headerWriter = null,
        ?callable $bodyWriter = null,
    ) {
        $this->statusWriter = Closure::fromCallable($statusWriter ?? static function (int $code, string $reason, string $protocol): void {
            header(sprintf('HTTP/%s %d%s', $protocol, $code, $reason !== '' ? ' ' . $reason : ''), true, $code);
        });
        $this->headerWriter = Closure::fromCallable($headerWriter ?? static function (string $name, string $value, bool $replace): void {
            header($name . ': ' . $value, $replace);
        });
        $this->bodyWriter = Closure::fromCallable($bodyWriter ?? static function (string $contents): void {
            echo $contents;
        });
    }

    public function emit(ResponseInterface $response): void
    {
        if ($this->emitted) {
            throw new ResponseAlreadyEmittedException('An HTTP response may only be emitted once.');
        }
        $this->emitted = true;

        ($this->statusWriter)(
            $response->status()->code(),
            $response->status()->reasonPhrase(),
            $response->protocolVersion()->value,
        );

        $headers = $response->headers();
        $contentType = $response->body()->contentType();
        if ($contentType !== null && !$headers->has('Content-Type')) {
            $headers = $headers->with('Content-Type', $contentType);
        }
        if ($response->status()->permitsBody() && !$headers->has('Content-Length')) {
            $headers = $headers->with('Content-Length', (string) $response->body()->length());
        }

        foreach ($headers->all() as $name => $values) {
            foreach ($values as $index => $value) {
                ($this->headerWriter)($name, $value, $index === 0);
            }
        }

        if ($response->status()->permitsBody() && !$response->body()->isEmpty()) {
            ($this->bodyWriter)($response->body()->contents());
        }
    }

    public function hasEmitted(): bool
    {
        return $this->emitted;
    }
}
