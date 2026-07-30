---
id: EG-305
title: Database Migration Engine Architecture
summary: Defines the governed architecture, deterministic planning model, history integrity, transactional boundaries and incremental roadmap for the SIF database migration engine.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-30
updated: 2026-07-30
work_package: WP-217
tags:
  - foundation
  - database
  - migrations
  - architecture
  - integrity
  - security
depends_on:
  - EG-264
  - EG-297
  - EG-304
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-305 — Database Migration Engine Architecture

## 1. Purpose

WP-217 establishes the provider-neutral Database Migration Engine for SIF.

The subsystem SHALL transform an explicit migration request and a governed migration registry into an immutable, deterministic and auditable execution plan. It SHALL apply or compensate database changes only through injected migration and history adapters whose capabilities are declared explicitly.

The engine SHALL remain independent of a CLI framework, web controller, ORM, query builder, schema DSL, database vendor and deployment platform.

## 2. Architectural objectives

The migration engine SHALL provide:

1. immutable migration identities, descriptors, requests and plans;
2. deterministic discovery, validation and dependency ordering;
3. checksum-based integrity for registered and applied migrations;
4. provider-neutral history and lock contracts;
5. explicit transaction capability negotiation;
6. dry-run and review-safe plan representations;
7. forward execution and explicit down migration behavior;
8. ordered journals and typed execution reports;
9. drift, divergence and tampering detection;
10. optional integration with the WP-216 Installer through a narrow adapter;
11. additive runtime integration;
12. reproducible validation across local and CI environments.

## 3. Non-goals

WP-217 SHALL NOT implement:

- an ORM or active-record layer;
- automatic schema inference from models;
- vendor-specific SQL generation in the Foundation;
- arbitrary shell command execution;
- remote database provisioning;
- database creation or credential management;
- implicit execution during application boot;
- destructive rollback when no explicit down operation exists;
- editing an applied migration in place;
- hidden discovery from unrestricted filesystem roots;
- concurrent execution without an acquired migration lock;
- distributed transactions across multiple databases;
- automatic backup or restore;
- a graphical migration interface.

## 4. Governing principles

### 4.1 Planning before mutation

Discovery, normalization, validation, dependency resolution and planning SHALL be read-only. No database mutation may occur before an immutable plan exists and execution is explicitly authorized by the caller.

### 4.2 Determinism

Given the same registry, history snapshot, request and adapter capabilities, the engine SHALL produce the same plan, diagnostics and fingerprint.

Registration order MAY act only as a documented final tie-breaker after explicit dependency and ordering rules.

### 4.3 Provider neutrality

The Foundation SHALL depend on contracts and immutable values. SQL dialects, schema APIs, connection objects and vendor exceptions SHALL remain in adapters.

### 4.4 Explicit irreversibility

A migration SHALL declare whether down execution is supported. The engine SHALL NOT infer reversibility from SQL text or operation names.

### 4.5 History as integrity evidence

Applied migration history SHALL be treated as governed evidence. Existing records SHALL not be silently rewritten, reordered or deleted by normal execution.

## 5. Core domain model

The minimum model SHALL include:

- `MigrationId`: stable canonical identity;
- `MigrationVersion`: optional sortable semantic or timestamp value;
- `MigrationDescriptor`: immutable metadata and dependencies;
- `MigrationChecksum`: canonical content fingerprint;
- `MigrationDirection`: `up` or `down`;
- `MigrationRequest`: target, direction, limits and execution mode;
- `MigrationOperation`: provider-neutral operation descriptor or adapter-owned payload reference;
- `MigrationPlanEntry`: one ordered migration action;
- `MigrationPlan`: immutable ordered aggregate and fingerprint;
- `AppliedMigration`: immutable history record;
- `MigrationHistorySnapshot`: ordered applied state;
- `MigrationExecutionReport`: final outcome and journal;
- `MigrationDiagnostic`: typed, safe and deterministic finding.

Public values SHALL reject empty identifiers, malformed dependencies, duplicate identities, unsupported directions and inconsistent state through typed exceptions rooted at a migration exception base.

## 6. Migration definition boundary

A migration definition SHALL expose governed metadata and executable behavior through explicit contracts.

Definitions SHALL NOT be evaluated from YAML, JSON, database rows or arbitrary strings. Metadata files MAY describe migrations but SHALL NOT contain executable PHP, SQL interpolation expressions or shell commands.

A migration implementation MAY provide:

- an `up` operation;
- an optional `down` operation;
- declared dependencies;
- optional tags or module ownership;
- a checksum source that is canonical and stable;
- capability requirements.

The engine SHALL not require migrations to extend a concrete base class when a contract is sufficient.

## 7. Registry and discovery

Migration discovery SHALL occur only through explicitly configured sources or registries.

The registry SHALL:

- preserve stable identities;
- reject duplicates;
- normalize descriptors before planning;
- expose deterministic iteration;
- retain ownership provenance;
- prevent replacement of a registered migration without an explicit override policy.

Filesystem scanning, Composer integration and module contribution adapters MAY be implemented later, but they SHALL produce the same normalized registry model.

## 8. Dependency graph and ordering

The planner SHALL validate:

- missing dependencies;
- self-dependencies;
- dependency cycles;
- duplicate versions when a version policy requires uniqueness;
- invalid direction-specific ordering;
- dependencies that conflict with the requested target;
- applied history that violates the current graph.

Forward plans SHALL order dependencies before dependants.

Rollback plans SHALL reverse the valid applied dependency order and SHALL reject removal of a migration still required by an applied dependant, unless that dependant is included earlier in the same rollback plan.

## 9. Checksums, drift and divergence

Each migration SHALL have a canonical checksum calculated from stable governed content. Runtime-only values, secrets, absolute paths and non-deterministic serialization SHALL be excluded.

The engine SHALL detect at least:

- an applied migration missing from the current registry;
- a registered migration whose checksum differs from applied history;
- a migration recorded as applied before one of its dependencies;
- duplicate history records;
- a failed or incomplete history record;
- a local branch whose applied chain diverges from the requested target.

Checksum mismatch SHALL be a blocking integrity failure by default. Any repair operation belongs to a separately authorized administrative workflow and SHALL not be disguised as normal migration execution.

## 10. History contract

A provider-neutral history store SHALL support:

- reading an immutable ordered snapshot;
- recording migration start when supported;
- recording successful application;
- recording successful down execution;
- recording failure without exposing secret database details;
- correlating records with an execution identifier;
- retaining checksum, batch and deterministic sequence information.

The engine SHALL not assume that the history store shares the same transaction as schema or data changes. Adapter capabilities SHALL state whether atomic coupling is available.

Normal execution SHALL append or transition governed records; it SHALL not silently rewrite prior successful evidence.

## 11. Locking and concurrency

Execution SHALL require an acquired migration lock unless the adapter explicitly proves an equivalent exclusive execution guarantee.

The lock contract SHALL expose:

- lock identity;
- acquisition result;
- owner-safe diagnostic token;
- lease or timeout semantics when supported;
- release outcome.

The engine SHALL reject concurrent execution when exclusivity cannot be established. Lock owner details SHALL be safe for diagnostics and SHALL not expose credentials, connection strings or process secrets.

## 12. Transaction boundaries

Transaction behavior SHALL be capability-driven.

An adapter MAY declare:

- no transaction support;
- transaction per migration;
- transaction for the complete plan;
- savepoint support;
- transactional history coupling;
- DDL transactional limitations.

The planner SHALL reject a requested atomicity level that the adapter cannot provide. The execution report SHALL state the actual boundary used.

The engine SHALL NOT claim universal rollback. Database rollback and explicit down migration are distinct mechanisms.

## 13. Execution model

Execution SHALL:

1. acquire the migration lock;
2. re-read and validate the history snapshot;
3. verify the plan fingerprint and authorization context;
4. begin the supported transaction boundary;
5. execute entries in plan order;
6. record ordered journal results;
7. update history according to the adapter contract;
8. commit or roll back the active database transaction when available;
9. release the lock;
10. return an immutable report.

A failure SHALL preserve the primary typed cause. Cleanup, history or lock-release failures SHALL be recorded as secondary diagnostics without replacing the original cause.

## 14. Down migrations and compensation

Down execution SHALL occur only when:

- the migration declares down support;
- dependency safety is satisfied;
- the request explicitly authorizes rollback direction;
- the target is deterministic;
- the current applied checksum remains valid.

An explicit down migration is not equivalent to automatic compensation. If an `up` operation partially succeeds outside a transaction, the engine SHALL report partial state and SHALL not execute `down` automatically unless a future governed policy explicitly authorizes that behavior.

## 15. Dry-run and authorization

Dry-run SHALL produce the same normalized plan that execution would consume, including:

- direction;
- ordered migration identities;
- dependencies;
- checksums;
- expected history transitions;
- transaction capability decision;
- lock requirement;
- irreversible steps;
- safe diagnostics;
- plan fingerprint.

Dry-run SHALL not acquire an execution lock, open a transaction, mutate history or execute migration operations.

Execution authorization SHALL be explicit and SHALL bind at least to the plan fingerprint, target database identity token and direction. Secrets SHALL never be embedded in the authorization value.

## 16. Installer integration

WP-216 MAY invoke migrations only through a narrow adapter contract implemented outside the Installer core.

The adapter SHALL translate an approved installation mutation into a migration request and SHALL return a safe execution result. It SHALL not bypass migration planning, history integrity, locking, authorization or transaction capability validation.

WP-217 SHALL not depend on Installer orchestration types in its core domain model. Integration SHALL remain one-way and optional.

## 17. Module integration

Modules MAY contribute migration definitions through an explicit contribution contract.

Module ownership SHALL be retained in migration provenance. Removing or disabling a module SHALL not erase its applied history. The planner SHALL report orphaned applied migrations and require an explicit administrative decision.

Contribution ordering SHALL not override declared migration dependencies.

## 18. Persistence integration

WP-217 MAY reuse stable connection and transaction contracts from WP-209 where they are sufficiently narrow. It SHALL not couple the migration model to repositories, mappers, criteria, pagination or unit-of-work semantics.

A migration adapter MAY wrap an existing persistence connection, but migration history and lock capabilities SHALL remain explicit.

## 19. Logging, context and error handling

Execution MAY consume optional Context, Logging and Error Handling contracts.

Diagnostics SHALL include safe correlation and migration identifiers but SHALL exclude:

- SQL parameters marked secret;
- credentials;
- connection strings;
- raw vendor exception messages when they may expose infrastructure;
- unrestricted SQL bodies;
- absolute paths unless explicitly classified safe.

Logging failure SHALL not alter migration execution state. The execution report remains authoritative.

## 20. Runtime integration

Runtime integration SHALL be optional and additive.

Application boot SHALL NOT execute migrations automatically. A future migration service provider MAY publish planning and execution services through the container and capability registry.

Existing applications SHALL remain valid without migration services.

## 21. Security invariants

All increments SHALL preserve:

1. no implicit migration execution;
2. no executable metadata evaluation;
3. no unrestricted filesystem discovery;
4. no secret data in checksums, fingerprints, journals or diagnostics;
5. no execution without deterministic planning;
6. no execution without exclusivity or equivalent guarantee;
7. no silent checksum repair;
8. no destructive down execution without explicit support and authorization;
9. no unsupported atomicity claims;
10. no replacement of the primary failure by cleanup failures;
11. no vendor exception leakage through public reports;
12. no history rewriting during ordinary execution.

## 22. Public API compatibility

WP-217 SHALL be additive.

It SHALL NOT:

- change existing runtime lifecycle signatures;
- require migration services during bootstrap;
- alter WP-209 persistence contracts incompatibly;
- trigger migration execution from Installer or Module registration;
- require a specific database vendor or SQL dialect.

## 23. Increment roadmap

### I1 — Architecture

Governed boundaries, domain model, integrity rules, transaction semantics, integrations and delivery roadmap.

### I2 — Immutable migration value model

Migration identities, versions, directions, descriptors, checksums, requests, targets and typed validation exceptions.

### I3 — Registry and deterministic dependency planning

Explicit registry, duplicate detection, graph validation, cycle detection, forward ordering and rollback ordering.

### I4 — History snapshots and integrity assessment

Applied records, immutable snapshots, checksum verification, drift, divergence, orphan and incomplete-state diagnostics.

### I5 — Migration plans, dry-run and authorization

Plan entries, target resolution, transaction decisions, safe plan views, fingerprints and explicit execution authorization.

### I6 — Execution, locking and transaction orchestration

Adapter contracts, lock lifecycle, capability negotiation, execution journal, primary-cause preservation and immutable reports.

### I7 — Reference in-memory adapter and Installer bridge

Deterministic reference adapter, history and lock implementation, narrow WP-216 bridge and integration tests.

### I8 — Runtime integration and product completion

Optional service provider, application exposure, examples, compatibility tests, completion review and final validation evidence.

## 24. Acceptance criteria

WP-217 is complete when:

1. all eight increments are implemented;
2. migration planning is deterministic and immutable;
3. duplicate identities, missing dependencies and cycles fail deterministically;
4. applied checksum drift and history divergence are detected;
5. execution requires an exclusive lock or declared equivalent;
6. transaction behavior matches declared adapter capabilities;
7. dry-run performs no mutation;
8. down execution is explicit and dependency-safe;
9. journals and reports preserve the primary cause and safe diagnostics;
10. Installer integration cannot bypass migration controls;
11. runtime integration remains optional;
12. Composer validation succeeds;
13. PHPUnit and PHPStan succeed;
14. Builder generation and validation produce zero diagnostics;
15. the complete Work Package is tagged in Git.

## 25. Open implementation constraints

I2 SHALL not introduce database access, registries, planning, history stores, locks, execution, service providers or concrete adapters.

Canonical checksum serialization SHALL be specified before any applied-history comparison is implemented.

Vendor-specific SQL and schema builders SHALL remain outside the Foundation unless separately governed.
