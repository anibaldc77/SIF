---
id: EG-263
title: Immutable Configuration Snapshots Canonical Serialization and Fingerprints
summary: Defines immutable configuration snapshots, deterministic type-preserving serialization, and reproducible SHA-256 fingerprints for Configuration 2.0.
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
  - snapshots
  - serialization
  - fingerprints
work_package: WP-211
depends_on:
  - EG-257
  - EG-258
  - EG-259
  - EG-260
  - EG-261
  - EG-262
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-263 — Immutable Snapshots, Canonical Serialization, and Fingerprints

## Purpose

This specification defines deterministic configuration snapshots suitable for equality checks, cache keys, bootstrap validation, diagnostics correlation, and future persisted snapshot loading.

A snapshot captures the effective configuration as an immutable repository and associates it with a reproducible fingerprint. It does not capture source adapters, process environment, timestamps, diagnostics, provenance, or mutable runtime state.

## Canonical serialization

`CanonicalConfigurationSerializer` SHALL produce the same payload for semantically equivalent configuration maps regardless of associative insertion order.

The representation SHALL preserve distinctions among:

- `null`;
- boolean values;
- integers;
- floating-point values;
- strings;
- ordered lists;
- associative maps;
- integer and string map keys.

Associative map entries SHALL be sorted deterministically by key type and key value. List order SHALL remain significant.

The serializer SHALL validate values through the Configuration 2.0 value model before serialization. Unsupported values SHALL fail explicitly.

Canonical payloads may contain raw secrets. They SHALL be treated as internal material and SHALL NOT be logged, displayed, attached to diagnostics, or exposed as generated artifacts. Safe export remains the required boundary for observable configuration representations.

## Fingerprints

`ConfigurationFingerprint` SHALL use SHA-256 in this increment. The external value SHALL use the form:

```text
sha256:<lowercase-hex-digest>
```

A fingerprint SHALL change when any configuration value, scalar type, list order, map key type, or map structure changes.

A fingerprint is an integrity and equality identifier. It is not encryption, authentication, or authorization. It SHALL NOT be used as a substitute for secret management.

## Snapshots

`ConfigurationSnapshot` SHALL contain:

- an `ImmutableConfigurationRepository`;
- a `ConfigurationFingerprint`;
- a snapshot format version.

Snapshot creation SHALL copy the source values into a new immutable repository. Subsequent changes to caller-owned arrays SHALL NOT affect the snapshot.

Snapshot equality SHALL be based on format version and constant-time fingerprint comparison. Creation time is intentionally excluded because timestamps would make identical configurations non-reproducible.

## Factory and verification

`ConfigurationSnapshotFactory` SHALL:

- create snapshots from `ConfigurationInterface`;
- compute fingerprints from canonical serialization;
- verify that a snapshot repository still corresponds to its fingerprint.

The factory SHALL remain storage-neutral. Persistence and cache loading are deferred to the next increment.

## Compatibility

This increment is additive. It SHALL NOT modify:

- existing mutable or immutable repository behavior;
- typed reads;
- source adapters;
- precedence and composition;
- provenance;
- diagnostics;
- schema validation and normalization;
- secret classification and safe export;
- Runtime or Container integration.

## Exclusions

This increment does not implement:

- snapshot persistence;
- filesystem or distributed cache adapters;
- signing or encryption;
- timestamps or expiry;
- provenance serialization;
- Runtime bootstrap integration;
- automatic safe export of canonical payloads.

## Acceptance criteria

- equivalent associative maps serialize identically;
- list order remains significant;
- scalar types remain distinct;
- snapshots own immutable repository copies;
- equivalent snapshots have equal fingerprints;
- changed values produce different fingerprints;
- fingerprints use validated SHA-256 hexadecimal digests;
- snapshot verification succeeds for valid snapshots;
- PHPUnit passes;
- PHPStan level 8 passes;
- Builder diagnostics remain zero;
- governed generation remains deterministic.
