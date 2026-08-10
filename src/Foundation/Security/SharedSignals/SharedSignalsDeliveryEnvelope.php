<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\SharedSignals;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class SharedSignalsDeliveryEnvelope
{
    public function __construct(
        private string $streamId,
        private string $deliveryId,
        private SecurityEventToken $token,
        private DateTimeImmutable $preparedAt
    ) {
        if (
            trim($this->streamId) === ''
            || trim($this->deliveryId) === ''
        ) {
            throw new InvalidArgumentException(
                'Shared signals delivery envelope is invalid.'
            );
        }
    }

    public function streamId(): string
    {
        return $this->streamId;
    }

    public function deliveryId(): string
    {
        return $this->deliveryId;
    }

    public function token(): SecurityEventToken
    {
        return $this->token;
    }

    public function preparedAt(): DateTimeImmutable
    {
        return $this->preparedAt;
    }
}
