<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Identity;

use Sif\Foundation\Security\Contracts\IdentityInterface;

final readonly class Identity implements IdentityInterface
{
    public function __construct(private IdentityId $id)
    {
    }

    public function id(): IdentityId
    {
        return $this->id;
    }
}
