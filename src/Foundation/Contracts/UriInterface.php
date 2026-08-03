<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

interface UriInterface
{
    public function scheme(): string;
    public function userInfo(): string;
    public function host(): string;
    public function port(): ?int;
    public function path(): string;
    public function query(): string;
    public function fragment(): string;
    public function authority(): string;
    public function withScheme(string $scheme): self;
    public function withUserInfo(string $user, ?string $password = null): self;
    public function withHost(string $host): self;
    public function withPort(?int $port): self;
    public function withPath(string $path): self;
    public function withQuery(string $query): self;
    public function withFragment(string $fragment): self;
    public function toString(): string;
}
