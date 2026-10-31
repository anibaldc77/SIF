<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Haip;

use InvalidArgumentException;

final readonly class OpenId4VcHaipProfile
{
    /**
     * @param list<OpenId4VcHaipFlow> $flows
     * @param list<OpenId4VcHaipCredentialFormat> $credentialFormats
     */
    public function __construct(
        private string $name,
        private array $flows,
        private array $credentialFormats
    ) {
        if (
            trim($this->name) === ''
            || $this->flows === []
            || $this->credentialFormats === []
        ) {
            throw new InvalidArgumentException(
                'OpenID4VC HAIP profile is invalid.'
            );
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    /** @return list<OpenId4VcHaipFlow> */
    public function flows(): array
    {
        return $this->flows;
    }

    /** @return list<OpenId4VcHaipCredentialFormat> */
    public function credentialFormats(): array
    {
        return $this->credentialFormats;
    }

    public function supportsFlow(OpenId4VcHaipFlow $flow): bool
    {
        return in_array($flow, $this->flows, true);
    }

    public function supportsCredentialFormat(
        OpenId4VcHaipCredentialFormat $format
    ): bool {
        return in_array($format, $this->credentialFormats, true);
    }
}
