---
id: EG-238
title: Customizable Audit Models and Change Sets
summary: Defines explicit model-facing audit contracts, external adapters, immutable descriptions, snapshots, metadata, and change-set boundaries without reflection.
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
  - models
  - change-set
  - adapters
depends_on:
  - EG-237
  - EG-235
  - EG-234
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-238 — Customizable Audit Models and Change Sets

## Purpose

This specification defines how application models and external objects provide audit subject identity, metadata, snapshots, and change sets without coupling the Audit Core to an ORM, BaseModel, reflection, or database state.

## Explicit model contracts

A model may opt in through these contracts:

- `AuditableSubjectInterface`;
- `AuditMetadataProviderInterface`;
- `AuditSnapshotProviderInterface`;
- `AuditChangeSetProviderInterface`.

Only the subject contract is mandatory for direct model description.

Metadata, snapshot, and change-set providers are optional and default to empty payloads.

## External model adapters

`AuditModelAdapterInterface` allows an application to describe objects that cannot or should not implement Core contracts.

An adapter supplies:

- explicit `AuditSubject`;
- metadata payload;
- snapshot payload.

If the model itself implements `AuditChangeSetProviderInterface`, the describer may still use that explicit change set.

Adapters take precedence over model contracts for subject, metadata, and snapshot.

## AuditModelDescription

`AuditModelDescription` is an immutable value containing:

- subject;
- metadata;
- snapshot;
- changes.

It preserves supplied value identity and performs no serialization, persistence, dispatching, or model mutation.

## AuditModelDescriber

`AuditModelDescriber` creates a description from either:

1. an explicit adapter; or
2. model contracts.

If neither an adapter nor `AuditableSubjectInterface` is available, the describer throws `UnsupportedAuditableModelException`.

There is no reflection fallback.

## Change-set policy

The Core does not calculate differences automatically.

Change sets must be provided explicitly through `AuditChangeSetProviderInterface` or future application-level factories.

This prevents hidden ORM coupling and avoids ambiguous comparison rules.

## Exclusions

This increment does not implement:

- BaseModel hooks;
- ORM listeners;
- reflection-based property discovery;
- automatic dirty tracking;
- database queries;
- record creation;
- event emission;
- persistence;
- static Audit facade.

## Acceptance criteria

- direct model contracts are explicit;
- external adapters are supported;
- optional metadata, snapshots, and changes default safely;
- adapters take precedence deterministically;
- unsupported models fail with a typed exception;
- no reflection or ORM dependency is introduced;
- PHPUnit passes;
- PHPStan level 8 passes;
- Builder diagnostics remain zero;
- governed generation is deterministic.
