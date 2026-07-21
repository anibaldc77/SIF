<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Output;

use RuntimeException;
use Sif\Builder\Cli\Contract\OutputInterface;

final readonly class ConsoleOutput implements OutputInterface
{
    /** @param resource|null $standardOutput @param resource|null $standardError */
    public function __construct(
        private mixed $standardOutput = null,
        private mixed $standardError = null,
    ) {
        if ($this->standardOutput !== null && !is_resource($this->standardOutput)) {
            throw new RuntimeException('Standard output must be a writable stream resource.');
        }
        if ($this->standardError !== null && !is_resource($this->standardError)) {
            throw new RuntimeException('Standard error must be a writable stream resource.');
        }
    }

    public function write(string $content): void
    {
        $this->writeTo($this->standardOutput ?? STDOUT, $content, 'standard output');
    }

    public function writeError(string $content): void
    {
        $this->writeTo($this->standardError ?? STDERR, $content, 'standard error');
    }

    /** @param resource $stream */
    private function writeTo(mixed $stream, string $content, string $label): void
    {
        if ($content === '') {
            return;
        }

        $remaining = $content;
        while ($remaining !== '') {
            $written = fwrite($stream, $remaining);
            if ($written === false || $written === 0) {
                throw new RuntimeException(sprintf('Unable to write to %s.', $label));
            }

            $remaining = substr($remaining, $written);
        }
    }
}
