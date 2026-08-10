<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Issuance;

use InvalidArgumentException;

final readonly class CredentialIssuanceGrantType
{
    public const AUTHORIZATION_CODE = 'authorization_code';
    public const PRE_AUTHORIZED_CODE = 'pre-authorized_code';

    public function __construct(private string $value)
    {
        if (!in_array($this->value, [
            self::AUTHORIZATION_CODE,
            self::PRE_AUTHORIZED_CODE,
        ], true)) {
            throw new InvalidArgumentException(
                'Credential issuance grant type is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
