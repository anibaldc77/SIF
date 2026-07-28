<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Bootstrap;

use InvalidArgumentException;
use Sif\Foundation\Configuration\Cache\Contracts\ConfigurationSnapshotCacheInterface;
use Sif\Foundation\Configuration\Composition\ConfigurationComposer;
use Sif\Foundation\Configuration\Diagnostics\ConfigurationDiagnostic;
use Sif\Foundation\Configuration\Diagnostics\ConfigurationDiagnosticSeverity;
use Sif\Foundation\Configuration\Snapshot\ConfigurationSnapshotFactory;
use Sif\Foundation\Configuration\Source\Contracts\ConfigurationSourceInterface;

final readonly class ConfigurationBootstrapper
{
    /** @var list<ConfigurationSourceInterface> */
    private array $sources;

    /**
     * @param iterable<ConfigurationSourceInterface> $sources
     */
    public function __construct(
        iterable $sources,
        private ?ConfigurationSnapshotCacheInterface $cache = null,
        private ?string $cacheKey = null,
        private ConfigurationComposer $composer = new ConfigurationComposer(),
        private ConfigurationSnapshotFactory $snapshotFactory = new ConfigurationSnapshotFactory(),
    ) {
        $normalizedSources = [];

        foreach ($sources as $source) {
            $normalizedSources[] = $source;
        }

        $this->sources = $normalizedSources;

        if ($this->cache !== null && trim((string) $this->cacheKey) === '') {
            throw new InvalidArgumentException(
                'A non-empty configuration cache key is required when a cache is configured.',
            );
        }
    }

    public function load(): ConfigurationBootstrapResult
    {
        $cacheKey = $this->cacheKey;

        if ($this->cache !== null && $cacheKey !== null) {
            $cached = $this->cache->get($cacheKey);

            if ($cached !== null && $this->snapshotFactory->verify($cached)) {
                return new ConfigurationBootstrapResult(
                    $cached,
                    true,
                    diagnostics: [$this->cacheDiagnostic(
                        'CFG_BOOTSTRAP_CACHE_HIT',
                        ConfigurationDiagnosticSeverity::Info,
                        'A verified configuration snapshot was loaded from cache.',
                    )],
                );
            }

            if ($cached !== null) {
                $this->cache->forget($cacheKey);
            }
        }

        $composed = $this->composer->compose($this->sources);
        $snapshot = $this->snapshotFactory->create($composed->repository());
        $diagnostics = $composed->diagnostics();

        if ($this->cache !== null && $cacheKey !== null) {
            $this->cache->put($cacheKey, $snapshot);
            $diagnostics[] = $this->cacheDiagnostic(
                'CFG_BOOTSTRAP_CACHE_MISS',
                ConfigurationDiagnosticSeverity::Info,
                'Configuration sources were composed and the resulting snapshot was cached.',
            );
        }

        return new ConfigurationBootstrapResult(
            $snapshot,
            false,
            $composed->allProvenance(),
            $diagnostics,
        );
    }

    private function cacheDiagnostic(
        string $code,
        ConfigurationDiagnosticSeverity $severity,
        string $message,
    ): ConfigurationDiagnostic {
        return new ConfigurationDiagnostic(
            $code,
            $severity,
            $message,
            'configuration-cache',
            ['cache_key_configured' => true],
        );
    }
}
