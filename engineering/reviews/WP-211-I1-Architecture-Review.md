---
id: WP-211-I1-REVIEW
title: WP-211-I1 Configuration 2.0 Architecture Review
summary: Reviews the compatible evolution from WP-203 toward source-neutral providers, deterministic composition, provenance, schemas, secret safety, immutable snapshots, diagnostics, caching, and controlled Runtime and Container integration.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Architecture Board
created: 2026-07-27
updated: 2026-07-27
work_package: WP-211
tags:
  - foundation
  - configuration
  - architecture
  - compatibility
  - review
depends_on:
  - EG-257
  - EG-256
  - EG-209
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-211-I1 — Configuration 2.0 Architecture Review

## Scope

WP-211-I1 defines the architecture of Configuration 2.0.

It adds no production PHP code and does not alter current Runtime behavior.

## Baseline review

WP-203 is correctly treated as the compatibility baseline.

The existing configuration subsystem already provides a useful and validated foundation:

- stable read and mutation contracts;
- deterministic dot-notation behavior;
- PHP and JSON loaders;
- ordered merge semantics;
- application ownership;
- successful-boot freezing;
- capability integration.

Reimplementing these features from scratch would create unnecessary compatibility risk.

## Architectural decisions reviewed

The architecture establishes:

- source providers separate from repository mutation;
- canonical normalized values;
- deterministic layered composition;
- explicit merge strategies;
- value provenance;
- typed access;
- schema validation;
- mandatory secret redaction;
- structured diagnostics;
- immutable snapshots;
- reproducible fingerprints;
- optional cache adapters;
- controlled Runtime integration;
- explicit Container 2.0 integration;
- deterministic module contributions;
- staged migration from WP-203.

## Source boundary review

The provider model is preferable to embedding source access in the repository.

It permits arrays, files, environment projections, command-line overrides, test sources, and future adapters without coupling read consumers to infrastructure.

Remote stores and secret managers remain adapters rather than Core dependencies.

## Composition review

Preserving WP-203 merge semantics as the default compatibility policy is appropriate.

Explicit precedence plus stable registration order eliminates dependency on traversal or map-order side effects.

Alternative policies should remain opt-in strategies.

## Provenance review

Provenance is necessary for operational diagnosis and legal or institutional traceability.

It must remain structurally separate from raw secret values.

The architecture appropriately records source identity, precedence, override status, and classification without requiring exposure of the resolved value.

## Schema review

A schema layer is justified because typed reads alone do not validate the complete application configuration.

Schema validation should happen before freeze and snapshot publication.

Defaults must be explicit and observable because invisible defaults can obscure deployment errors.

## Secret-safety review

Secret redaction is correctly defined as mandatory across:

- diagnostics;
- exceptions;
- logs;
- snapshots intended for display;
- audit records;
- generated artifacts.

The configuration subsystem should consume secret providers but should not become a secret-management product.

## Snapshot and cache review

Immutable snapshots align with the current irreversible freeze semantics.

A canonical fingerprint improves deployment diagnosis and reproducibility.

Caching is correctly optional and must never silently accept stale or corrupt data.

## Runtime review

The current successful-boot freeze boundary remains valid.

WP-211 should evolve the data published at that boundary rather than changing lifecycle order without need.

Failed boot must continue to leave configuration unfrozen for diagnosis and recovery.

## Container review

Container 2.0 integration must remain explicit.

The container must not infer configuration keys from constructor parameter names.

Preferred patterns are typed bindings, configuration objects, and explicit factories.

## Module review

Module contributions require namespace ownership and deterministic registration.

Unrestricted filesystem discovery or cross-module mutation would undermine determinism and should remain excluded.

## Compatibility review

The architecture does not supersede EG-207, EG-208, or EG-209 immediately.

This is the correct migration posture.

Each later increment must map existing classes and contracts before introducing deprecation.

## Risk review

Primary risks are:

1. duplicating WP-203 rather than evolving it;
2. secret leakage through provenance or diagnostics;
3. hidden source precedence;
4. locale-dependent conversion;
5. ambiguous schema defaults;
6. stale cache use;
7. implicit network access;
8. hidden configuration injection through the container;
9. module namespace conflicts;
10. mutable global configuration;
11. non-deterministic fingerprints;
12. premature Runtime replacement.

The proposed architecture mitigates these risks through compatibility-first evolution, explicit providers, deterministic ordering, immutable snapshots, safe diagnostics, and staged integration.

## Recommendation

Approve WP-211-I1.

Continue with WP-211-I2, limited to:

- `ConfigurationKey`;
- normalized configuration value rules;
- typed read contracts;
- immutable source descriptors;
- core exception taxonomy;
- adapters or compatibility tests for existing WP-203 read behavior.

WP-211-I2 should not yet implement file access, schemas, provenance composition, secret redaction, snapshots, caching, Runtime integration, or Container integration.
