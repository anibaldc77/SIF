<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Redaction;

use Sif\Foundation\Logging\Exceptions\NormalizationException;

final readonly class SecretRedactionPolicy
{
    /** @var list<string> */
    private array $sensitiveKeys;

    /**
     * @param list<string> $sensitiveKeys
     */
    public function __construct(
        array $sensitiveKeys = [
            'password', 'passwd', 'secret', 'token', 'access_token', 'refresh_token',
            'authorization', 'api_key', 'apikey', 'private_key', 'client_secret',
            'cookie', 'set_cookie', 'session_id',
        ],
        private string $redactionMarker = '[redacted]',
    ) {
        if ($redactionMarker === '') {
            throw NormalizationException::because('redaction marker must not be empty');
        }
        $normalized = [];
        foreach ($sensitiveKeys as $key) {
            $canonical = self::canonicalKey($key);
            if ($canonical === '') {
                throw NormalizationException::because('sensitive keys must not be empty');
            }
            $normalized[$canonical] = true;
        }
        $this->sensitiveKeys = array_keys($normalized);
    }

    public function isSensitive(string $key): bool
    {
        return in_array(self::canonicalKey($key), $this->sensitiveKeys, true);
    }

    public function redactionMarker(): string
    {
        return $this->redactionMarker;
    }

    private static function canonicalKey(string $key): string
    {
        $key = strtolower(trim($key));
        $key = preg_replace('/[^a-z0-9]+/', '_', $key) ?? '';
        return trim($key, '_');
    }
}
