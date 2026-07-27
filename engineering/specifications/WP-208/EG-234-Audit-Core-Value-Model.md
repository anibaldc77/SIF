---
id: EG-234
title: Audit Core Value Model
summary: Defines immutable audit identifiers, typed levels, semantic action names, subject descriptors, and initial extension contracts.
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
  - value-objects
  - contracts
depends_on:
  - EG-233
  - EG-232
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-234 — Audit Core Value Model

## Purpose

This specification defines the first production increment of the Audit subsystem.

The increment is limited to immutable value objects and extension contracts. It does not create audit records, payloads, serializers, events, persistence adapters, policies, model hooks, or a static facade.

## Public model

### AuditId

`AuditId` is an immutable opaque identifier.

It:

- preserves the supplied non-empty string;
- does not impose UUID, ULID, integer, or database constraints;
- supports explicit equality;
- exposes a stable string representation.

### AuditLevel

`AuditLevel` is a backed enum with these initial values:

- `diagnostic`;
- `informational`;
- `notice`;
- `warning`;
- `critical`.

Each level has a stable priority used only for semantic comparison. Priority does not define retention, authorization, or log verbosity.

### AuditAction

`AuditAction` is an immutable semantic identifier.

Valid names:

- begin with a lower-case letter;
- contain lower-case letters and digits;
- may use `.`, `_`, or `-` as separators;
- do not contain spaces;
- do not contain empty segments.

Examples:

```text
user.created
document.signed
case-status.changed
permission_denied
```

Action names are case-sensitive.

### AuditSubject

`AuditSubject` identifies the conceptual target of an audited action.

It contains:

- a mandatory non-empty subject type;
- an optional non-empty subject identifier.

The Audit Core does not infer subject identity from models.

## Contracts

### AuditIdGeneratorInterface

Produces an `AuditId` without coupling the Core to a specific generation algorithm.

### AuditableSubjectInterface

Allows an application object or adapter to provide an explicit `AuditSubject`.

Implementing this contract is optional. Reflection-based discovery is not introduced.

## Compatibility

The increment:

- requires PHP 8.2;
- is storage-neutral;
- does not modify Runtime, Context, Event Dispatcher, Observation, Configuration, or Environment;
- does not add database dependencies;
- is additive.

## Acceptance criteria

- immutable identifiers, levels, actions, and subjects exist;
- invalid construction fails with typed exceptions;
- action naming rules are deterministic;
- subject identity remains explicit;
- PHPStan level 8 passes;
- PHPUnit passes;
- Builder diagnostics remain zero;
- governed generation remains deterministic.
