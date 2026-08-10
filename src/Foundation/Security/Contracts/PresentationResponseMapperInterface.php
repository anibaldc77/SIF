<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\PresentationResponse;

interface PresentationResponseMapperInterface
{
    public function map(
        string $serializedResponse
    ): PresentationResponse;
}
