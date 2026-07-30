---
id: WP-217-I1-ARCHITECTURE-REVIEW
title: WP-217 I1 Architecture Review
summary: Reviews the proposed Database Migration Engine boundaries, integrity controls, concurrency model, transaction semantics and implementation sequence.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-07-30
updated: 2026-07-30
tags:
  - migrations
  - database
  - architecture
  - integrity
  - review
work_package: WP-217
depends_on:
  - EG-305
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-217 I1 Architecture Review

## Scope reviewed

- migration engine boundaries;
- immutable migration and request model;
- explicit registry and discovery controls;
- dependency graph and deterministic ordering;
- applied-history integrity;
- canonical checksums;
- drift and divergence detection;
- locking and concurrency;
- transaction capability negotiation;
- dry-run and execution authorization;
- forward and down migration behavior;
- Installer, Modules and Persistence integration;
- optional runtime integration;
- eight-increment delivery roadmap.

## Architectural decision

WP-217 SHALL be implemented as a provider-neutral, planning-first migration subsystem.

The engine owns normalization, graph validation, history assessment, planning and execution orchestration. Database-specific SQL, schema operations, connection behavior, locks and history persistence remain adapter responsibilities.

Application construction and boot SHALL never trigger migration execution.

## Integrity review

The architecture establishes the following mandatory integrity controls:

- immutable migration identities;
- deterministic canonical checksums;
- duplicate and cycle rejection;
- applied checksum verification;
- orphan and divergence detection;
- append-oriented history evidence;
- plan fingerprint verification immediately before execution;
- explicit repair workflows outside normal execution.

Checksum mismatch is correctly classified as a blocking condition rather than an informational warning.

## Concurrency review

Execution requires an acquired migration lock or an adapter-declared equivalent exclusivity guarantee. This prevents concurrent planners from applying the same history snapshot independently.

Lock acquisition, release and failure reporting are explicit. Lock-release failure remains secondary to the original execution failure.

## Transaction review

The architecture does not assume that every database supports transactional DDL or that schema changes and history records share one transaction.

Capability negotiation distinguishes:

- no transaction support;
- transaction per migration;
- transaction for the complete plan;
- savepoints;
- transactional history coupling.

Unsupported atomicity requests fail before mutation. This is suitable for SQL Server, PostgreSQL, MySQL and older database environments without creating false portability guarantees.

## Rollback review

Database transaction rollback and explicit down migration are treated as different mechanisms. This is correct.

Down execution requires declared support, dependency-safe ordering and explicit authorization. The engine does not claim automatic compensation for partially applied non-transactional operations.

## Security review

The architecture establishes:

- no executable metadata;
- no unrestricted migration discovery;
- no secret inclusion in checksums or diagnostics;
- no raw vendor exception leakage;
- no implicit destructive rollback;
- no silent history repair;
- no migration execution without deterministic planning and authorization.

These controls are normative for all later increments.

## Compatibility review

WP-217 is additive. Existing Runtime, Persistence, Installer, Module, Configuration, Logging and Error Handling behavior remains unchanged when migrations are not configured.

The engine may consume narrow persistence contracts but SHALL not introduce reverse dependencies from WP-209 or require repository and ORM semantics.

## Integration review

The WP-216 bridge is intentionally deferred until the migration engine has its own planning and execution controls. The bridge must translate requests only; it may not bypass history verification, locking, authorization or transaction capability checks.

Module migration contributions retain ownership provenance. Module removal does not erase applied history.

## Risks and mitigations

| Risk | Impact | Mitigation |
|---|---:|---|
| Vendor behavior is generalized incorrectly | Critical | Require explicit adapter capabilities and reject unsupported atomicity. |
| Applied migrations are edited in place | Critical | Canonical checksums and blocking drift detection. |
| Concurrent execution corrupts history | Critical | Mandatory lock or equivalent exclusivity guarantee. |
| Rollback is treated as universally safe | High | Separate transaction rollback from explicit down migration. |
| History and schema changes diverge | High | Declare transactional history coupling and report partial state. |
| Filesystem discovery executes untrusted code | Critical | Explicit governed registries and no executable metadata evaluation. |
| Installer bypasses migration controls | High | Narrow bridge that delegates to the complete migration workflow. |
| Vendor exceptions expose infrastructure | High | Typed public failures and safe diagnostics. |
| Module removal hides applied migrations | Medium | Preserve history and report orphaned ownership. |
| Migration engine becomes an ORM | High | Keep schema DSL, mapping and model inference out of scope. |

## Increment decision

I2 may begin after approval of EG-305.

I2 SHALL implement only immutable migration identities, versions, directions, descriptors, checksums, requests, targets and typed validation exceptions.

I2 SHALL NOT implement registries, dependency planning, history stores, database connections, locks, transactions, execution, Installer bridges, service providers or concrete database adapters.

## Review outcome

The architecture is suitable to begin incremental implementation subject to repository validation of the governed metadata.
