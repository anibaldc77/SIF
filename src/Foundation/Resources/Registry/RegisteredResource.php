<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Registry;

use Sif\Foundation\Resources\Exceptions\InvalidRegistrationOrderException;
use Sif\Foundation\Resources\ResourceDescriptor;

final readonly class RegisteredResource
{
    public function __construct(
        private ResourceDescriptor $descriptor,
        private int $registrationOrder,
    ) {
        if ($registrationOrder < 0) {
            throw new InvalidRegistrationOrderException('Resource registration order must be zero or greater.');
        }
    }

    public function descriptor(): ResourceDescriptor
    {
        return $this->descriptor;
    }

    public function registrationOrder(): int
    {
        return $this->registrationOrder;
    }

    public function key(): string
    {
        return $this->descriptor->qualifiedIdentifier();
    }

    /** @return array{registration_order:int,resource:array{identifier:string,namespace:string,type:string,source:string,priority:int,logical_version:?string,owner:?string,metadata:array<string, null|bool|int|float|string>}} */
    public function summary(): array
    {
        return [
            'registration_order' => $this->registrationOrder,
            'resource' => $this->descriptor->summary(),
        ];
    }
}
