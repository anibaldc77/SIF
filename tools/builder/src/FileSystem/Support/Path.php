<?php
declare(strict_types=1);
namespace Sif\Builder\FileSystem\Support;
final readonly class Path
{
    public function __construct(private PathNormalizer $normalizer, public string $value) {}
    public function normalized(): string { return $this->normalizer->normalize($this->value); }
    public function join(string $child): self { return new self($this->normalizer, $this->normalized().'/'.$child); }
}
