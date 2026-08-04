<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Csrf;

use Sif\Foundation\Http\Value\HttpMethod;
use Sif\Foundation\Security\Csrf\Exceptions\CsrfException;

final readonly class CsrfConfiguration
{
    /** @var list<HttpMethod> */
    private array $protectedMethods;

    /** @param list<HttpMethod> $protectedMethods */
    public function __construct(
        private string $sessionKey = '_csrf.token',
        private string $headerName = 'X-CSRF-Token',
        private string $bodyField = '_csrf',
        array $protectedMethods = [HttpMethod::Post, HttpMethod::Put, HttpMethod::Patch, HttpMethod::Delete],
    ) {
        if ($sessionKey === '' || preg_match('/[\r\n\x00]/', $sessionKey) === 1) {
            throw new CsrfException('CSRF session key must be non-empty and safe.');
        }
        if ($headerName === '' || preg_match("/^[!#$%&'*+.^_`|~0-9A-Za-z-]+$/", $headerName) !== 1) {
            throw new CsrfException('CSRF header name is invalid.');
        }
        if ($bodyField === '' || preg_match('/[\r\n\x00]/', $bodyField) === 1) {
            throw new CsrfException('CSRF body field must be non-empty and safe.');
        }
        $this->protectedMethods = array_values(array_unique($protectedMethods, SORT_REGULAR));
    }

    public function sessionKey(): string { return $this->sessionKey; }
    public function headerName(): string { return $this->headerName; }
    public function bodyField(): string { return $this->bodyField; }
    /** @return list<HttpMethod> */ public function protectedMethods(): array { return $this->protectedMethods; }
    public function protects(HttpMethod $method): bool { return in_array($method, $this->protectedMethods, true); }
}
