<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Formats\IsoMdoc\IsoMdocDeviceResponse;

interface IsoMdocDeviceResponseParserInterface
{
    public function parse(string $serialized): IsoMdocDeviceResponse;
}
