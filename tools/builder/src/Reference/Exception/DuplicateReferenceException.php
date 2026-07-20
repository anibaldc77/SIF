<?php

declare(strict_types=1);

namespace Sif\Builder\Reference\Exception;

use DomainException;

final class DuplicateReferenceException extends DomainException
{
    public static function forIdentity(string $identity): self
    {
        return new self(sprintf('A reference with identity "%s" already exists.', $identity));
    }
}
