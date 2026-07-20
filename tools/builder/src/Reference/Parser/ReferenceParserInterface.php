<?php

declare(strict_types=1);

namespace Sif\Builder\Reference\Parser;

use Sif\Builder\Metadata\MetadataDocument;
use Sif\Builder\Reference\ReferenceCollection;

interface ReferenceParserInterface
{
    public function parse(MetadataDocument $document): ReferenceCollection;
}
