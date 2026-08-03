<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Http\Value\AttributeBag;
use Sif\Foundation\Http\Value\CookieBag;
use Sif\Foundation\Http\Value\HeaderBag;
use Sif\Foundation\Http\Value\HttpMethod;
use Sif\Foundation\Http\Value\HttpProtocolVersion;
use Sif\Foundation\Http\Value\QueryParameterBag;
use Sif\Foundation\Http\Value\RequestBody;
use Sif\Foundation\Http\Value\ServerParameterBag;

interface RequestInterface
{
    public function method(): HttpMethod;
    public function uri(): UriInterface;
    public function protocolVersion(): HttpProtocolVersion;
    public function headers(): HeaderBag;
    public function cookies(): CookieBag;
    public function query(): QueryParameterBag;
    public function attributes(): AttributeBag;
    public function server(): ServerParameterBag;
    public function body(): RequestBody;
    /** @return array<string, UploadedFileInterface|list<UploadedFileInterface>> */
    public function uploadedFiles(): array;
    public function withAttribute(string $name, mixed $value): self;
    public function withoutAttribute(string $name): self;
}
