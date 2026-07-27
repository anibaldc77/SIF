---
id: EG-235
title: Immutable Audit Record and Payload
summary: Defines the immutable Audit record, JSON-compatible payload validation, optional state snapshots, change sets, tags, and schema versioning.
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
  - payload
  - immutable-record
depends_on:
  - EG-234
  - EG-233
  - EG-232
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-235 — Immutable Audit Record and Payload

## Purpose

This specification defines the immutable Audit record and its JSON-compatible payload model.

The increment does not define factories, serializers, redaction policies, events, persistence adapters, model hooks, or a static facade.

## AuditPayload

`AuditPayload` is an immutable wrapper for application-defined, JSON-compatible audit data.

It accepts:

- `null`;
- booleans;
- integers;
- finite floating-point values;
- strings;
- nested arrays using integer or non-empty string keys.

It rejects:

- objects;
- closures;
- resources;
- non-finite floats;
- empty string keys;
- unsupported runtime values.

The payload supports a first-level immutable merge. It does not perform deep merge semantics.

## AuditRecord

`AuditRecord` is the authoritative immutable statement that an auditable action occurred.

It contains:

- `AuditId`;
- `AuditAction`;
- `AuditLevel`;
- occurrence timestamp;
- mandatory `ExecutionContextInterface`;
- `AuditSubject`;
- canonical payload;
- optional before snapshot;
- optional after snapshot;
- optional change set;
- ordered unique tags;
- schema version.

## Optional snapshots and changes

Before, after, and change-set values are optional `AuditPayload` instances.

The Core does not calculate diffs and does not inspect models. Producers or future providers supply these values explicitly.

## Tags

Tags are ordered, non-empty strings. Duplicate tags are removed while preserving first occurrence order.

Tags are descriptive metadata only. They do not define authorization, retention, or persistence routing.

## Schema version

Every record has a non-empty schema version. The default is `1.0`.

Schema version describes the canonical record document contract and is independent from framework package versioning.

## Exclusions

This increment does not implement:

- record factories;
- ID generators;
- canonical serializers;
- redaction policies;
- event dispatching;
- persistence;
- retention;
- automatic model inspection;
- automatic diffs;
- static Audit facade.

## Acceptance criteria

- all record components are immutable;
- Execution Context is mandatory;
- payload validation is deterministic and typed;
- snapshots and changes remain optional;
- duplicate tags are normalized deterministically;
- schema version is explicit;
- PHPUnit passes;
- PHPStan level 8 passes;
- Builder diagnostics remain zero;
- governed generation is deterministic.
