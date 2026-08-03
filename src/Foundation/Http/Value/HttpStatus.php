<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Value;

use Sif\Foundation\Http\Exceptions\InvalidHttpResponseException;

final readonly class HttpStatus
{
    /** @var array<int, string> */
    private const REASONS = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        204 => 'No Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        409 => 'Conflict',
        410 => 'Gone',
        415 => 'Unsupported Media Type',
        422 => 'Unprocessable Content',
        429 => 'Too Many Requests',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
    ];

    public function __construct(
        private int $code,
        private ?string $reasonPhrase = null,
    ) {
        if ($code < 100 || $code > 599) {
            throw new InvalidHttpResponseException(sprintf('HTTP status code "%d" must be between 100 and 599.', $code));
        }
        if ($reasonPhrase !== null && preg_match('/[\r\n\x00]/', $reasonPhrase) === 1) {
            throw new InvalidHttpResponseException('HTTP reason phrase must not contain CR, LF or NUL.');
        }
    }

    public function code(): int
    {
        return $this->code;
    }

    public function reasonPhrase(): string
    {
        return $this->reasonPhrase ?? self::REASONS[$this->code] ?? '';
    }

    public function isInformational(): bool
    {
        return $this->code >= 100 && $this->code < 200;
    }

    public function isSuccessful(): bool
    {
        return $this->code >= 200 && $this->code < 300;
    }

    public function permitsBody(): bool
    {
        return !$this->isInformational() && $this->code !== 204 && $this->code !== 304;
    }
}
