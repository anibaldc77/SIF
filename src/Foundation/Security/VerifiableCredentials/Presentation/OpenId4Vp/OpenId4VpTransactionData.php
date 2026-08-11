<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp;

use InvalidArgumentException;

final readonly class OpenId4VpTransactionData
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        private string $transactionId,
        private string $type,
        private array $attributes = []
    ) {
        if (
            trim($this->transactionId) === ''
            || trim($this->type) === ''
        ) {
            throw new InvalidArgumentException(
                'OpenID4VP transaction data is invalid.'
            );
        }
    }

    public function transactionId(): string
    {
        return $this->transactionId;
    }

    public function type(): string
    {
        return $this->type;
    }

    /**
     * @return array<string, mixed>
     */
    public function attributes(): array
    {
        return $this->attributes;
    }
}
