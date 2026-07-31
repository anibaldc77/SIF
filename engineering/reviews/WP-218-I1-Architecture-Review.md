---
id: WP-218-I1-ARCHITECTURE-REVIEW
title: WP-218 I1 Architecture Review
summary: Reviews the proposed PDO migration adapter boundaries, platform capability model, persistent history, locking, transaction semantics, SQL operation safety and eight-increment implementation sequence.
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
  - pdo
  - adapters
  - architecture
  - security
  - review
work_package: WP-218
depends_on:
  - EG-313
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-218 I1 Architecture Review

## Scope reviewed

- dependency direction between WP-217 and PDO infrastructure;
- PDO connection ownership and configuration;
- deterministic platform identification;
- immutable capability profiles;
- persistent migration history;
- explicit history-table provisioning;
- PostgreSQL, MySQL and SQL Server lock strategies;
- transaction and transactional-DDL differences;
- SQL operation handling;
- identifier and value safety;
- failure translation and secret redaction;
- conformance testing and support evidence;
- Installer and Runtime integration;
- eight-increment delivery roadmap.

## Architectural decision

WP-218 SHALL be implemented as an additive infrastructure package that adapts WP-217 contracts to PDO-backed relational databases.

WP-217 remains the sole owner of migration policy, integrity, planning, authorization and execution ordering. WP-218 owns physical persistence and database interaction only.

This dependency direction is mandatory:

```text
WP-218 PDO adapters -> WP-217 migration contracts
```

The reverse dependency is prohibited.

## Platform review

The architecture correctly rejects the assumption that PDO makes database semantics portable.

PostgreSQL, MySQL and SQL Server receive explicit platform profiles. Locking, transactional DDL, savepoints, implicit commits and history coupling are declared capabilities rather than hidden driver behavior.

Unsupported or contradictory capability states fail before mutation.

## History review

Persistent history is append-oriented and preserves the complete immutable WP-217 history model.

The architecture correctly prohibits silent history-table creation during reads or migration execution. Provisioning must be an explicit Installer or migration operation with its own plan, dry-run, authorization and journal.

Stable sequence ordering prevents dependence on timestamp resolution.

## Lock review

The selected mechanisms are appropriate reference strategies:

- PostgreSQL advisory locks;
- MySQL named locks;
- SQL Server application locks.

Lock identity is deterministic and excludes secrets. Acquisition is bounded, release failure remains secondary to the primary execution failure, and connection-loss semantics are reportable.

## Transaction review

The architecture distinguishes PDO transaction API availability from real schema atomicity.

It explicitly addresses:

- MySQL implicit commits;
- PostgreSQL operations that cannot run inside a transaction;
- SQL Server operation-specific behavior;
- externally active transactions;
- savepoint negotiation;
- rollback failure preservation;
- history/schema transaction coupling.

This avoids false atomicity guarantees.

## SQL safety review

Values and identifiers are handled separately.

Prepared statements are mandatory for values. Configurable identifiers require strict platform-specific validation before quoting. Raw SQL, DSNs and parameter values are excluded from default diagnostics.

The operation model remains declarative and directional. Arbitrary PHP callbacks, shell commands and unrestricted file execution remain out of scope.

## Runtime and Installer review

Runtime composition is optional and additive. Registration and boot do not execute migrations and should avoid eager network access.

Installer integration may provision the history table but cannot grant migration authorization or bypass WP-217 controls.

These boundaries preserve the safety properties established by WP-216 and WP-217.

## Testing review

The architecture appropriately separates:

- repository tests that require no live database;
- reusable adapter conformance suites;
- conditional vendor integration suites;
- evidence required before claiming platform support.

SQLite or test doubles may validate generic mapping behavior, but they cannot prove PostgreSQL, MySQL or SQL Server conformance.

## Compatibility review

WP-218 is additive. Existing in-memory migration adapters remain valid and applications without PDO adapter registration observe no behavior change.

No incompatible change to WP-217 public contracts is authorized by this architecture.

## Risks and mitigations

| Risk | Impact | Mitigation |
|---|---:|---|
| PDO is treated as semantically portable | Critical | Explicit platform profiles and capability checks. |
| History table is created implicitly | High | Separate governed provisioning operation. |
| Concurrent migration runs corrupt state | Critical | Mandatory bounded platform lock. |
| Transaction rollback is overstated | Critical | Model transactional DDL and implicit commits explicitly. |
| External transactions are committed accidentally | Critical | Explicit ownership and external-transaction policy. |
| SQL identifiers are interpolated unsafely | Critical | Strict validation followed by platform quoting. |
| Vendor exceptions expose credentials or SQL | High | Typed failure translation and redacted diagnostics. |
| Generic tests are presented as vendor support | High | Conditional conformance evidence per platform. |
| Runtime boot performs network or mutation work | High | Optional lazy composition and prohibition of boot migration. |
| Adapter changes migration policy | Critical | WP-217 remains authoritative; dependency direction enforced. |
| SQL Server legacy support weakens modern safety | High | Separate declared compatibility profile and tests. |
| Lock release hides the primary error | High | Preserve primary cause and report cleanup failure secondarily. |

## Increment decision

I2 may begin after approval of EG-313.

I2 is limited to immutable connection, platform, ownership and capability values plus validation tests. It SHALL perform no database I/O.
