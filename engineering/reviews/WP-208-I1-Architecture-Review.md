---
id: WP-208-I1-REVIEW
title: WP-208-I1 Audit Architecture Review
summary: Reviews the proposed storage-neutral, event-driven, context-aware Audit architecture and its readiness for incremental implementation.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Architecture Board
created: 2026-07-27
updated: 2026-07-27
work_package: WP-208
tags:
  - foundation
  - audit
  - architecture
  - context
  - events
  - review
depends_on:
  - EG-233
  - EG-232
  - EG-217
  - EG-213
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-208-I1 — Audit Architecture Review

## Scope

WP-208-I1 defines the architectural boundary of the future Audit subsystem. It does not add production code, tests, database artifacts, persistence adapters, or automatic Runtime integration.

## Reviewed decisions

The architecture establishes that:

- Audit Core is storage-neutral and has no database knowledge;
- records are emitted through immutable events;
- every record carries an explicit Execution Context;
- canonical representation is JSON-compatible data;
- levels are typed values rather than free-form strings;
- application models customize audit information through explicit contracts;
- reflection-based automatic model inspection is excluded;
- state snapshots and diffs are optional and provider-driven;
- confidentiality, redaction, and data minimization are mandatory concerns;
- persistence, retention, encryption, and access control belong to adapters;
- an optional static facade may be considered only after instance contracts exist.

## Dependency review

The proposed dependency direction is valid:

```text
Application or module
        -> Audit contracts and values
        -> Audit event
        -> Event Dispatcher
        -> Optional adapters
```

Audit may use Execution Context contracts but must not make Context aware of Audit.

Audit must not depend on a persistence adapter, ORM, BaseModel, HTTP request, CLI request, session, authentication implementation, or database connection.

## Compatibility review

The proposal is additive and compatible with the current PHP 8.2 baseline.

No changes are required to:

- Application;
- Bootstrap;
- Kernel;
- Lifecycle;
- Runtime;
- Event Dispatcher;
- Observation;
- Execution Context;
- Configuration;
- Environment;
- Capability Registry.

## Risk review

Primary risks are:

1. auditing excessive or confidential data;
2. coupling record construction to ORM internals;
3. conflating auditing with logging;
4. conflating audit level with retention or authorization;
5. introducing hidden global state through a static facade;
6. allowing adapter failures to alter the original record;
7. defining an unstable JSON schema.

The architecture mitigates these risks through explicit contracts, immutable values, mandatory Context, deterministic serialization, policy boundaries, and delayed adapter work.

## Recommendation

Approve WP-208-I1 and continue with WP-208-I2, limited to typed identifiers, audit levels, action names, and subject descriptors.

WP-208-I2 should not yet implement records, serialization, events, persistence, model hooks, or a static facade.
