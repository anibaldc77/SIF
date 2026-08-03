<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Http\Value\HeaderBag;
use Sif\Foundation\Http\Value\HttpProtocolVersion;
use Sif\Foundation\Http\Value\HttpStatus;
use Sif\Foundation\Http\Value\ResponseBody;

interface ResponseInterface
{
    public function status(): HttpStatus;
    public function protocolVersion(): HttpProtocolVersion;
    public function headers(): HeaderBag;
    public function body(): ResponseBody;
    public function withStatus(HttpStatus $status): self;
    /** @param string|list<string> $values */
    public function withHeader(string $name, string|array $values): self;
    public function withoutHeader(string $name): self;
    public function withBody(ResponseBody $body): self;
}
