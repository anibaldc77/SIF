<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Transport;

use Sif\Foundation\Http\Exceptions\HttpTransportException;
use Sif\Foundation\Http\Value\AttributeBag;
use Sif\Foundation\Http\Value\CookieBag;
use Sif\Foundation\Http\Value\HeaderBag;
use Sif\Foundation\Http\Value\HttpMethod;
use Sif\Foundation\Http\Value\HttpProtocolVersion;
use Sif\Foundation\Http\Value\QueryParameterBag;
use Sif\Foundation\Http\Value\Request;
use Sif\Foundation\Http\Value\RequestBody;
use Sif\Foundation\Http\Value\ServerParameterBag;
use Sif\Foundation\Http\Value\UploadedFile;
use Sif\Foundation\Http\Value\Uri;

final class NativeRequestFactory
{
    /**
     * @param array<string, mixed> $server
     * @param array<string, mixed> $query
     * @param array<string, mixed> $cookies
     * @param array<string, mixed> $files
     */
    public function create(
        array $server,
        array $query = [],
        array $cookies = [],
        array $files = [],
        string $body = '',
    ): Request {
        $method = HttpMethod::fromString((string) ($server['REQUEST_METHOD'] ?? 'GET'));
        $protocol = HttpProtocolVersion::fromString((string) ($server['SERVER_PROTOCOL'] ?? 'HTTP/1.1'));
        $headers = $this->headers($server);
        $uri = $this->uri($server);
        [$mediaType, $charset] = $this->bodyMetadata($headers);

        return new Request(
            $method,
            $uri,
            $protocol,
            $headers,
            new CookieBag($cookies),
            new QueryParameterBag($query),
            new AttributeBag(),
            new ServerParameterBag($server),
            new RequestBody($body, $mediaType, $charset),
            $this->uploadedFiles($files),
        );
    }

    public function fromGlobals(): Request
    {
        $body = file_get_contents('php://input');
        if ($body === false) {
            throw new HttpTransportException('Unable to read the native HTTP request body.');
        }

        return $this->create($_SERVER, $_GET, $_COOKIE, $_FILES, $body);
    }

    /** @param array<string, mixed> $server */
    private function headers(array $server): HeaderBag
    {
        $headers = [];
        foreach ($server as $name => $value) {
            if (!is_scalar($value)) {
                continue;
            }

            if (str_starts_with($name, 'HTTP_')) {
                $header = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$header] = (string) $value;
                continue;
            }

            if ($name === 'CONTENT_TYPE') {
                $headers['Content-Type'] = (string) $value;
            } elseif ($name === 'CONTENT_LENGTH') {
                $headers['Content-Length'] = (string) $value;
            }
        }

        return new HeaderBag($headers);
    }

    /** @param array<string, mixed> $server */
    private function uri(array $server): Uri
    {
        $secure = isset($server['HTTPS']) && !in_array(strtolower((string) $server['HTTPS']), ['', 'off', '0'], true);
        $scheme = $secure ? 'https' : 'http';
        $hostValue = (string) ($server['HTTP_HOST'] ?? $server['SERVER_NAME'] ?? 'localhost');
        $requestUri = (string) ($server['REQUEST_URI'] ?? '/');

        $hostParts = parse_url($scheme . '://' . $hostValue);
        if ($hostParts === false || !isset($hostParts['host'])) {
            throw new HttpTransportException(sprintf('Unable to derive a valid request host from "%s".', $hostValue));
        }

        $requestParts = parse_url($requestUri);
        if ($requestParts === false) {
            throw new HttpTransportException(sprintf('Unable to parse native request URI "%s".', $requestUri));
        }

        $port = isset($hostParts['port'])
            ? (int) $hostParts['port']
            : (isset($server['SERVER_PORT']) ? (int) $server['SERVER_PORT'] : null);

        if (($scheme === 'http' && $port === 80) || ($scheme === 'https' && $port === 443)) {
            $port = null;
        }

        return new Uri(
            $scheme,
            '',
            strtolower((string) $hostParts['host']),
            $port,
            isset($requestParts['path']) ? (string) $requestParts['path'] : '/',
            isset($requestParts['query']) ? (string) $requestParts['query'] : '',
        );
    }

    /** @return array{0: ?string, 1: ?string} */
    private function bodyMetadata(HeaderBag $headers): array
    {
        if (!$headers->has('Content-Type')) {
            return [null, null];
        }

        $parts = array_map('trim', explode(';', $headers->line('Content-Type')));
        $mediaType = array_shift($parts);
        $charset = null;
        foreach ($parts as $part) {
            if (str_starts_with(strtolower($part), 'charset=')) {
                $charset = trim(substr($part, 8), " \t\n\r\0\x0B\"");
            }
        }

        return [$mediaType !== '' ? $mediaType : null, $charset !== '' ? $charset : null];
    }

    /**
     * @param array<string, mixed> $files
     *
     * @return array<string, UploadedFile|list<UploadedFile>>
     */
    private function uploadedFiles(array $files): array
    {
        $result = [];
        foreach ($files as $field => $specification) {
            if (!is_array($specification)) {
                throw new HttpTransportException(sprintf('Uploaded file field "%s" has an invalid native structure.', $field));
            }
            $result[$field] = $this->normalizeUploadedFile($specification);
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $specification
     *
     * @return UploadedFile|list<UploadedFile>
     */
    private function normalizeUploadedFile(array $specification): UploadedFile|array
    {
        foreach (['name', 'type', 'tmp_name', 'size', 'error'] as $key) {
            if (!array_key_exists($key, $specification)) {
                throw new HttpTransportException(sprintf('Uploaded file specification is missing "%s".', $key));
            }
        }

        if (is_array($specification['name'])) {
            $names = array_values($specification['name']);
            $types = is_array($specification['type']) ? array_values($specification['type']) : [];
            $paths = is_array($specification['tmp_name']) ? array_values($specification['tmp_name']) : [];
            $sizes = is_array($specification['size']) ? array_values($specification['size']) : [];
            $errors = is_array($specification['error']) ? array_values($specification['error']) : [];
            $files = [];
            foreach ($names as $index => $name) {
                $files[] = new UploadedFile(
                    is_string($name) ? $name : null,
                    isset($types[$index]) && is_string($types[$index]) && $types[$index] !== '' ? $types[$index] : null,
                    isset($paths[$index]) && is_string($paths[$index]) && $paths[$index] !== '' ? $paths[$index] : null,
                    isset($sizes[$index]) ? (int) $sizes[$index] : null,
                    isset($errors[$index]) ? (int) $errors[$index] : UPLOAD_ERR_NO_FILE,
                );
            }

            return $files;
        }

        return new UploadedFile(
            is_string($specification['name']) && $specification['name'] !== '' ? $specification['name'] : null,
            is_string($specification['type']) && $specification['type'] !== '' ? $specification['type'] : null,
            is_string($specification['tmp_name']) && $specification['tmp_name'] !== '' ? $specification['tmp_name'] : null,
            is_numeric($specification['size']) ? (int) $specification['size'] : null,
            is_numeric($specification['error']) ? (int) $specification['error'] : UPLOAD_ERR_NO_FILE,
        );
    }
}
