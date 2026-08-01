<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Sql;

use Sif\Foundation\Persistence\Pdo\Exception\InvalidPdoSqlIdentifierException;
use Sif\Foundation\Persistence\Pdo\Platform\PdoPersistencePlatform;

final readonly class PdoSqlIdentifier
{
    /** @var list<string> */
    private array $segments;

    public function __construct(string $identifier)
    {
        $parts = explode('.', trim($identifier));
        foreach ($parts as $part) {
            if (preg_match('/^[A-Za-z_][A-Za-z0-9_$]*$/D', $part) !== 1) {
                throw new InvalidPdoSqlIdentifierException('SQL identifier contains an invalid segment.');
            }
        }
        $this->segments = array_values($parts);
    }

    /** @return list<string> */ public function segments(): array { return $this->segments; }
    public function value(): string { return implode('.', $this->segments); }

    public function quoted(PdoPersistencePlatform $platform): string
    {
        $quote = $platform->identifierQuote();
        return implode('.', array_map(static fn (string $segment): string => $quote . $segment . $quote, $this->segments));
    }
}
