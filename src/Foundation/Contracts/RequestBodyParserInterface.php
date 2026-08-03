<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Http\Value\RequestBody;

interface RequestBodyParserInterface
{
    public function supports(RequestBody $body): bool;

    /** @return array<string, mixed> */
    public function parse(RequestBody $body): array;
}
