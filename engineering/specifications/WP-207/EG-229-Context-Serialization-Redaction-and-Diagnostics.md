---
id: EG-229
title: Context Serialization, Redaction and Diagnostic Snapshot
summary: Defines canonical deterministic serialization, explicit attribute-key redaction, and diagnostic-safe execution context snapshots.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-27
updated: 2026-07-27
work_package: WP-207
tags:
  - foundation
  - context
  - serialization
  - redaction
  - diagnostics
depends_on:
  - EG-228
  - EG-227
  - EG-226
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-229 — Context Serialization, Redaction and Diagnostic Snapshot

## 1. Purpose

This specification defines the canonical safe array representation of an execution context, explicit redaction of sensitive attribute keys, and an immutable snapshot suitable for diagnostics.

## 2. Public contracts

The increment SHALL provide:

- `ContextSerializerInterface`;
- `ContextRedactionPolicyInterface`;
- `ExecutionContextSerializer`;
- `ContextRedactionPolicy`;
- `ContextDiagnosticSnapshot`;
- `InvalidContextRedactionPolicyException`.

## 3. Canonical representation

The serializer SHALL emit stable snake-case keys for every standard context field. Required and optional fields SHALL always be represented; absent optional values SHALL be `null`.

Timestamps SHALL use an ISO-8601 representation with microseconds and explicit offset.

## 4. Deterministic attributes

Associative attribute keys SHALL be sorted recursively using ordinal string comparison. List order SHALL be preserved. The serializer SHALL NOT mutate the source context or its attributes.

## 5. Redaction policy

Redaction SHALL be explicit and policy-driven. The initial policy SHALL use an exact, case-sensitive deny-list of attribute keys.

Redaction SHALL apply at every associative depth. A matched value SHALL be replaced by a stable marker. The policy SHALL reject empty keys and empty markers.

The policy SHALL apply only to extension attributes in this increment. Standard typed fields remain governed by the context construction boundary.

## 6. Diagnostic snapshot

`ContextDiagnosticSnapshot` SHALL capture only the canonical serialized representation produced by a supplied serializer and policy. It SHALL expose stable context and correlation identifiers and a complete safe array payload.

The snapshot SHALL NOT inspect arbitrary objects, include stack traces, reveal host paths, or introduce a dependency on logging, observation, audit, storage, HTTP, or CLI infrastructure.

## 7. Exclusions

This increment SHALL NOT include:

- JSON text encoding;
- automatic secret detection;
- wildcard or regular-expression policies;
- encryption or hashing;
- ambient context access;
- Runtime, event, observation, or audit wiring;
- persistence or export adapters.

## 8. Acceptance criteria

The increment is accepted when canonical field serialization, timestamp stability, recursive associative sorting, list preservation, recursive key redaction, policy validation, source immutability, diagnostic snapshot generation, PHPStan level 8, Builder validation, and the full repository quality gate pass.
