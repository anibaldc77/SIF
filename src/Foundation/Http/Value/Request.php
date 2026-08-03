<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Value;

use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Contracts\UploadedFileInterface;
use Sif\Foundation\Contracts\UriInterface;
use Sif\Foundation\Http\Exceptions\InvalidHttpRequestException;

final readonly class Request implements RequestInterface
{
    /** @var array<string, UploadedFileInterface|list<UploadedFileInterface>> */
    private array $uploadedFiles;

    /** @param array<string, UploadedFileInterface|list<UploadedFileInterface>> $uploadedFiles */
    public function __construct(
        private HttpMethod $method,
        private UriInterface $uri,
        private HttpProtocolVersion $protocolVersion = HttpProtocolVersion::Http11,
        private HeaderBag $headers = new HeaderBag(),
        private CookieBag $cookies = new CookieBag(),
        private QueryParameterBag $query = new QueryParameterBag(),
        private AttributeBag $attributes = new AttributeBag(),
        private ServerParameterBag $server = new ServerParameterBag(),
        private RequestBody $body = new RequestBody(),
        array $uploadedFiles = [],
    ) {
        foreach ($uploadedFiles as $name => $file) {
            if ($name === '') {
                throw new InvalidHttpRequestException('Uploaded file field names must be non-empty.');
            }
            if (is_array($file)) {
                foreach ($file as $item) {
                    if (!$item instanceof UploadedFileInterface) {
                        throw new InvalidHttpRequestException('Uploaded file collections must contain uploaded-file descriptors.');
                    }
                }
                continue;
            }
            if (!$file instanceof UploadedFileInterface) {
                throw new InvalidHttpRequestException('Uploaded files must implement UploadedFileInterface.');
            }
        }
        $this->uploadedFiles = $uploadedFiles;
    }

    public function method(): HttpMethod { return $this->method; }
    public function uri(): UriInterface { return $this->uri; }
    public function protocolVersion(): HttpProtocolVersion { return $this->protocolVersion; }
    public function headers(): HeaderBag { return $this->headers; }
    public function cookies(): CookieBag { return $this->cookies; }
    public function query(): QueryParameterBag { return $this->query; }
    public function attributes(): AttributeBag { return $this->attributes; }
    public function server(): ServerParameterBag { return $this->server; }
    public function body(): RequestBody { return $this->body; }
    public function uploadedFiles(): array { return $this->uploadedFiles; }

    public function withMethod(HttpMethod $method): self
    {
        return $this->copy(method: $method);
    }

    public function withUri(UriInterface $uri): self
    {
        return $this->copy(uri: $uri);
    }

    public function withProtocolVersion(HttpProtocolVersion $version): self
    {
        return $this->copy(protocolVersion: $version);
    }

    /** @param string|list<string> $values */
    public function withHeader(string $name, string|array $values): self
    {
        return $this->copy(headers: $this->headers->with($name, $values));
    }

    public function withoutHeader(string $name): self
    {
        return $this->copy(headers: $this->headers->without($name));
    }

    public function withBody(RequestBody $body): self
    {
        return $this->copy(body: $body);
    }

    public function withAttribute(string $name, mixed $value): self
    {
        return $this->copy(attributes: $this->attributes->with($name, $value));
    }

    public function withoutAttribute(string $name): self
    {
        return $this->copy(attributes: $this->attributes->without($name));
    }

    private function copy(
        ?HttpMethod $method = null,
        ?UriInterface $uri = null,
        ?HttpProtocolVersion $protocolVersion = null,
        ?HeaderBag $headers = null,
        ?CookieBag $cookies = null,
        ?QueryParameterBag $query = null,
        ?AttributeBag $attributes = null,
        ?ServerParameterBag $server = null,
        ?RequestBody $body = null,
    ): self {
        return new self(
            $method ?? $this->method,
            $uri ?? $this->uri,
            $protocolVersion ?? $this->protocolVersion,
            $headers ?? $this->headers,
            $cookies ?? $this->cookies,
            $query ?? $this->query,
            $attributes ?? $this->attributes,
            $server ?? $this->server,
            $body ?? $this->body,
            $this->uploadedFiles,
        );
    }
}
