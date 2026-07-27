---
id: EG-236
title: Audit Record Factory, Canonical Serialization and Redaction
summary: Defines deterministic audit record construction, canonical document serialization, context snapshot composition, and explicit recursive redaction.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-27
updated: 2026-07-27
work_package: WP-208
tags:
  - foundation
  - audit
  - factory
  - serialization
  - redaction
depends_on:
  - EG-235
  - EG-229
  - EG-234
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-236 — Audit Record Factory, Canonical Serialization and Redaction

## Purpose

This increment defines deterministic audit record construction and canonical, persistence-neutral serialization.

It remains independent of event dispatching, persistence, retention, static facades, ORM hooks, and automatic model inspection.

## Factory

`AuditRecordFactory` depends on:

- `AuditIdGeneratorInterface`;
- `ClockInterface`.

The factory creates complete immutable `AuditRecord` instances and does not store them.

All application-specific values remain explicit inputs.

## Canonical serialization

`AuditRecordSerializer` emits a stable JSON-compatible document with this field order:

1. `schema_version`;
2. `audit_id`;
3. `action`;
4. `level`;
5. `occurred_at`;
6. `context`;
7. `subject`;
8. `payload`;
9. `before`;
10. `after`;
11. `changes`;
12. `tags`.

The occurrence time uses ISO-8601 with microseconds and timezone.

Associative maps are recursively sorted by key. Lists preserve their insertion order.

Optional snapshots are represented as `null`.

## Context composition

The context document is produced exclusively through `ContextSerializerInterface` and `ContextRedactionPolicyInterface`.

Audit serialization does not inspect or reconstruct context internals independently.

## Audit redaction

`AuditRedactionPolicyInterface` defines explicit payload redaction.

The initial policy:

- uses an explicit case-sensitive list of keys;
- applies recursively to associative arrays and maps inside lists;
- replaces matching values with a stable marker;
- rejects empty keys and empty markers;
- does not attempt automatic secret detection.

The policy is applied independently to payload, before, after, and changes.

## Storage neutrality

The serializer returns an array and does not:

- call `json_encode`;
- write files;
- open database connections;
- dispatch events;
- call a logger;
- perform network I/O.

Adapters decide how and where to encode or persist the canonical document.

## Acceptance criteria

- factory uses injected ID generation and clock;
- canonical field order is stable;
- associative maps are recursively sorted;
- list order is preserved;
- context and audit redaction are applied explicitly;
- optional snapshots remain `null`;
- serialization is deterministic;
- PHPUnit passes;
- PHPStan level 8 passes;
- Builder diagnostics remain zero;
- governed generation remains deterministic.
