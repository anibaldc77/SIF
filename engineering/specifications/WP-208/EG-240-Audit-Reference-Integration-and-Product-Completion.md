---
id: EG-240
title: Audit Reference Integration and Product Completion
summary: Defines the end-to-end reference integration, acceptance baseline, guarantees, exclusions, and formal completion criteria for WP-208.
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
  - integration
  - completion
depends_on:
  - EG-239
  - EG-238
  - EG-237
  - EG-236
  - EG-235
  - EG-234
  - EG-233
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-240 — Audit Reference Integration and Product Completion

## Purpose

This specification completes WP-208 by defining and validating a vertical reference flow across the Audit subsystem without adding persistence or database coupling.

## Reference flow

The accepted vertical composition is:

```text
ExecutionContext
        |
        v
AuditRecordFactory
        |
        v
AuditService
        |
        v
EventDispatcherAuditEmitter
        |
        v
AuditRecordCreated
        |
        v
Application listener
        |
        v
AuditRecordSerializer
        |
        v
Canonical redacted document
```

Every step is explicit and replaceable.

## Acceptance scenarios

The reference integration must demonstrate:

1. complete record creation with deterministic ID and time;
2. mandatory Execution Context;
3. event-driven emission;
4. listener receipt of the same record instance;
5. canonical serialization;
6. context redaction;
7. audit payload redaction;
8. optional before, after, and change-set payloads;
9. explicit model description feeding record construction;
10. operation without persistence or database access.

## Product guarantees

WP-208 guarantees:

- storage neutrality;
- event-driven emission;
- mandatory explicit context;
- immutable records and values;
- typed levels;
- semantic actions and subjects;
- JSON-compatible payloads;
- deterministic serialization;
- explicit redaction;
- model customization through contracts or adapters;
- no reflection fallback;
- optional static facade only as a delegating convenience;
- no hidden ambient context;
- PHP 8.2 compatibility;
- no external runtime dependency.

## Product exclusions

WP-208 does not include:

- database schemas;
- migrations;
- repositories;
- retention policies;
- encryption at rest;
- cryptographic signatures;
- hash chains;
- async transport;
- automatic BaseModel integration;
- automatic dirty tracking;
- automatic Runtime registration;
- authorization;
- reporting dashboards.

These may be implemented by later adapters or work packages.

## Completion baseline

WP-208 is complete when:

- all increments I1 through I8 are integrated;
- the complete PHPUnit suite passes;
- PHPStan level 8 reports zero errors;
- Builder reports zero diagnostics;
- governed generation is deterministic;
- reference examples execute successfully;
- repository diff checks pass.

## Future extension points

Later work packages may add:

- persistence adapter contracts;
- SQL Server, PostgreSQL, or MySQL adapters;
- BaseModel integration;
- asynchronous emission;
- retention and confidentiality policies;
- integrity chains;
- reporting and search APIs.

Those extensions must depend on the Audit Core, never the reverse.
