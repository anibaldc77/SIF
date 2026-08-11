<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp;

use InvalidArgumentException;

final readonly class OpenId4VpPresentationContext
{
    /**
     * @param list<string> $expectedOrigins
     */
    public function __construct(
        private string $verifierId,
        private string $nonce,
        private array $expectedOrigins = [],
        private ?string $walletNonce = null,
        private ?string $transactionId = null
    ) {
        if (
            trim($this->verifierId) === ''
            || trim($this->nonce) === ''
        ) {
            throw new InvalidArgumentException(
                'OpenID4VP presentation context is invalid.'
            );
        }
    }

    public function verifierId(): string
    {
        return $this->verifierId;
    }

    public function nonce(): string
    {
        return $this->nonce;
    }

    /**
     * @return list<string>
     */
    public function expectedOrigins(): array
    {
        return $this->expectedOrigins;
    }

    public function walletNonce(): ?string
    {
        return $this->walletNonce;
    }

    public function transactionId(): ?string
    {
        return $this->transactionId;
    }
}
