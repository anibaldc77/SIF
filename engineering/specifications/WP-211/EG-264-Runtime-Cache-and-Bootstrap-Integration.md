---
id: EG-264
title: Configuration Runtime Cache and Bootstrap Integration
summary: Defines cache-neutral configuration snapshot reuse and backward-compatible integration of Configuration 2.0 with the SIF bootstrap lifecycle.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-28
updated: 2026-07-28
tags:
  - foundation
  - configuration
  - runtime
  - cache
  - bootstrap
work_package: WP-211
depends_on:
  - EG-257
  - EG-258
  - EG-259
  - EG-260
  - EG-261
  - EG-262
  - EG-263
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-264 — Runtime, Cache, and Bootstrap Integration

## Purpose

This specification defines a storage-neutral cache boundary for immutable configuration snapshots and a backward-compatible path for Configuration 2.0 to participate in application bootstrap.

The increment SHALL preserve the existing provider lifecycle: configuration remains mutable during provider `register()` and `boot()` and becomes frozen only after successful boot.

## Cache contract

`ConfigurationSnapshotCacheInterface` SHALL expose explicit operations to retrieve, store, forget, and clear snapshots.

Cache keys SHALL be supplied by the composition root. Configuration 2.0 SHALL NOT derive keys from source paths, process-wide environment state, secret values, or volatile timestamps.

A cache implementation SHALL return complete `ConfigurationSnapshot` instances. A retrieved snapshot SHALL be verified through `ConfigurationSnapshotFactory` before use. Invalid snapshots SHALL be rejected and removed.

The reference implementation SHALL be in-memory and process-local. Persistent storage, distributed invalidation, encryption, expiry, and locking are outside this increment.

## Bootstrap pipeline

`ConfigurationBootstrapper` SHALL:

1. attempt to load a verified snapshot when a cache and cache key are configured;
2. skip source loading on a verified cache hit;
3. compose registered sources deterministically on a cache miss;
4. create an immutable snapshot from the effective repository;
5. store the snapshot when caching is enabled;
6. return structured bootstrap metadata.

The result SHALL identify whether the snapshot came from cache and SHALL expose source diagnostics and provenance when sources were composed.

Provenance is intentionally empty on a cache hit because this increment does not persist provenance metadata. Consumers SHALL NOT infer source ownership from a cached snapshot alone.

## Diagnostics

The bootstrap pipeline SHALL emit stable, value-free diagnostics:

- `CFG_BOOTSTRAP_CACHE_HIT`;
- `CFG_BOOTSTRAP_CACHE_MISS`.

Diagnostics SHALL NOT contain configuration values, secret fragments, canonical payloads, fingerprints, environment contents, or raw cache keys.

## Runtime integration

`Bootstrap` MAY receive a `ConfigurationBootstrapper` as an optional final constructor argument.

When supplied, Bootstrap SHALL initialize the existing mutable `ConfigurationRepository` from the snapshot values. This preserves compatibility with service providers that mutate configuration during `register()` and `boot()`.

When no Configuration 2.0 bootstrapper is supplied, Bootstrap SHALL retain the existing WP-203 file-loader path without behavioral change.

## Compatibility

This increment SHALL be additive except for the backward-compatible optional constructor parameter added to `Bootstrap`.

It SHALL NOT change:

- `ConfigurationInterface`;
- `MutableConfigurationInterface`;
- provider lifecycle ordering;
- configuration freeze timing;
- existing PHP and JSON source loading;
- Runtime state transitions;
- Container 2.0 behavior.

## Security

Caches may contain configuration secrets because snapshots contain effective values. Cache implementations are therefore trusted infrastructure boundaries.

The in-memory reference cache does not persist values outside the current process. Future persistent adapters SHALL define encryption, permissions, atomic writes, invalidation, and secret-handling requirements before implementation.
