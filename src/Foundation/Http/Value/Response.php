<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Value;

use Sif\Foundation\Contracts\ResponseInterface;

final readonly class Response implements ResponseInterface
{
    public function __construct(
        private HttpStatus $status = new HttpStatus(200),
        private HttpProtocolVersion $protocolVersion = HttpProtocolVersion::Http11,
        private HeaderBag $headers = new HeaderBag(),
        private ResponseBody $body = new ResponseBody(),
    ) {
    }

    public static function text(string $contents, int $status = 200): self
    {
        return new self(
            new HttpStatus($status),
            body: new ResponseBody($contents, 'text/plain', 'utf-8'),
        );
    }

    /** @param array<string, mixed> $data */
    public static function json(array $data, int $status = 200): self
    {
        $json = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return new self(
            new HttpStatus($status),
            body: new ResponseBody($json, 'application/json', 'utf-8'),
        );
    }

    public function status(): HttpStatus { return $this->status; }
    public function protocolVersion(): HttpProtocolVersion { return $this->protocolVersion; }
    public function headers(): HeaderBag { return $this->headers; }
    public function body(): ResponseBody { return $this->body; }

    public function withStatus(HttpStatus $status): self
    {
        return new self($status, $this->protocolVersion, $this->headers, $this->body);
    }

    public function withProtocolVersion(HttpProtocolVersion $protocolVersion): self
    {
        return new self($this->status, $protocolVersion, $this->headers, $this->body);
    }

    /** @param string|list<string> $values */
    public function withHeader(string $name, string|array $values): self
    {
        return new self($this->status, $this->protocolVersion, $this->headers->with($name, $values), $this->body);
    }

    /** @param string|list<string> $values */
    public function withAddedHeader(string $name, string|array $values): self
    {
        return new self($this->status, $this->protocolVersion, $this->headers->withAdded($name, $values), $this->body);
    }

    public function withoutHeader(string $name): self
    {
        return new self($this->status, $this->protocolVersion, $this->headers->without($name), $this->body);
    }

    public function withBody(ResponseBody $body): self
    {
        return new self($this->status, $this->protocolVersion, $this->headers, $body);
    }

    public function normalizedHeaders(): HeaderBag
    {
        $headers = $this->headers;
        $contentType = $this->body->contentType();
        if ($contentType !== null && !$headers->has('Content-Type')) {
            $headers = $headers->with('Content-Type', $contentType);
        }
        if ($this->status->permitsBody() && !$headers->has('Content-Length')) {
            $headers = $headers->with('Content-Length', (string) $this->body->length());
        }

        return $headers;
    }
}
