---
id: EG-313
title: PDO Migration Adapters Architecture
summary: Defines the governed architecture, portability boundaries, capability model, persistent history, advisory locking, transactional coordination and incremental roadmap for PDO-backed migration adapters.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-30
updated: 2026-07-30
work_package: WP-218
tags:
  - foundation
  - database
  - migrations
  - pdo
  - adapters
  - architecture
  - security
depends_on:
  - EG-264
  - EG-305
  - EG-310
  - EG-311
  - EG-312
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-313 — PDO Migration Adapters Architecture

## 1. Purpose

WP-218 establishes governed PDO-backed adapters for the provider-neutral migration engine delivered by WP-217.

The package SHALL connect the existing migration contracts to supported relational databases without moving vendor-specific behavior into the Foundation migration domain. It SHALL provide persistent migration history, exclusive execution locking, transaction coordination and explicit SQL operation handling through capability-aware adapters.

WP-218 SHALL preserve the planning, integrity, authorization and execution guarantees already established by WP-217. An adapter may implement infrastructure behavior, but it may not reinterpret or bypass migration policy.

## 2. Architectural objectives

WP-218 SHALL provide:

1. a narrow PDO connection boundary suitable for injected and externally managed connections;
2. deterministic database-platform identification;
3. explicit adapter capabilities rather than inferred portability;
4. persistent migration history storage with canonical value mapping;
5. exclusive migration locks with bounded acquisition and safe release;
6. transaction management aligned with real driver and platform behavior;
7. SQL migration operation handlers with explicit direction support;
8. safe identifier handling and no interpolation of untrusted values;
9. typed failure translation without leaking credentials or raw connection state;
10. reference support for PostgreSQL, MySQL and SQL Server;
11. compatibility with older SQL Server environments where PDO capabilities permit it;
12. conformance tests reusable by every database adapter;
13. optional runtime composition without implicit network access during boot;
14. reproducible validation in environments where live databases are unavailable.

## 3. Non-goals

WP-218 SHALL NOT implement:

- a schema-definition language;
- automatic SQL generation from PHP models;
- an ORM, active record or repository abstraction;
- database or user creation;
- credential discovery or secret storage;
- connection pooling;
- distributed locks across unrelated databases;
- distributed transactions;
- automatic backup, restore or point-in-time recovery;
- migration discovery from arbitrary filesystem paths;
- implicit migration execution during application boot;
- automatic correction of divergent history;
- conversion of non-transactional DDL into an atomic operation;
- support claims for a platform not covered by a conformance profile.

## 4. Governing principles

### 4.1 Core policy remains in WP-217

Registry validation, history integrity assessment, selection, planning, dry-run, authorization and execution ordering remain owned by WP-217.

PDO adapters SHALL implement only the contracts delegated to infrastructure. They SHALL not modify a plan, suppress an integrity violation or execute an unauthorized migration.

### 4.2 Capabilities before execution

Every adapter composition SHALL expose an immutable capability profile before mutation begins.

The profile SHALL describe at least:

- supported platform and driver;
- transaction support;
- transactional DDL expectations;
- savepoint support;
- history/schema transaction coupling;
- lock mechanism;
- lock scope;
- lock timeout behavior;
- supported SQL operation directions.

Unknown or contradictory capabilities SHALL fail closed.

### 4.3 No false portability

PDO provides a common API, not identical database semantics.

WP-218 SHALL model differences explicitly. PostgreSQL advisory locks, MySQL named locks and SQL Server application locks are separate strategies behind a common contract. Transaction and DDL behavior SHALL be declared per platform profile rather than generalized from PDO alone.

### 4.4 Externally owned connections

An injected PDO connection MAY be owned by the application or by the adapter composition.

Ownership SHALL be explicit. WP-218 SHALL not close or replace an externally owned connection. Connection creation from credentials MAY be supplied by a separate factory, but credentials SHALL not become part of migration descriptors, reports, checksums or diagnostics.

### 4.5 Safe failure translation

PDO exceptions and vendor error details SHALL be translated into typed infrastructure failures with safe diagnostics.

Public reports MAY include:

- platform;
- operation kind;
- migration identity;
- safe SQL state where policy permits;
- whether mutation may have occurred;
- whether transaction rollback succeeded;
- whether lock release succeeded.

They SHALL NOT include passwords, DSNs containing secrets, arbitrary parameter values or unrestricted raw SQL.

## 5. Package boundary

WP-218 SHOULD introduce an additive namespace rooted at:

```text
Sif\Foundation\Migration\Pdo
```

The package MAY contain the following conceptual areas:

```text
Pdo/
  Connection/
  Platform/
  History/
  Lock/
  Transaction/
  Operation/
  Exception/
  Runtime/
```

No WP-217 domain type SHALL depend on these PDO classes.

## 6. Connection boundary

The PDO integration SHALL use a narrow connection contract or immutable connection reference rather than exposing connection construction throughout the subsystem.

The boundary SHALL provide only the operations required by adapters, including:

- access to the PDO instance;
- platform identity;
- ownership metadata;
- transaction state inspection where reliable;
- safe execution through prepared statements.

The default PDO configuration SHOULD require exception error mode and SHOULD disable emulated prepares where the driver supports native prepared statements reliably.

Configuration validation SHALL occur before migration mutation.

## 7. Platform identification

Platform resolution SHALL be deterministic and based on an explicit configured platform or a normalized PDO driver name.

Supported canonical platform identifiers for the initial product SHALL be:

- `postgresql`;
- `mysql`;
- `sqlserver`.

Driver aliases such as `pgsql` and `sqlsrv` MAY normalize to canonical identifiers. Ambiguous or unsupported drivers SHALL fail before adapter composition.

Platform resolution SHALL not execute schema-changing SQL.

## 8. Capability profile

An immutable PDO migration capability profile SHALL represent the effective behavior of the adapter composition.

Capabilities SHALL be derived from a governed platform profile plus explicitly validated runtime facts. Runtime probing SHALL be read-only and bounded.

The profile SHALL not claim universal transactional DDL. In particular:

- PostgreSQL MAY support transaction-per-migration and broader transactional DDL, subject to operation restrictions;
- MySQL SHALL account for statements that cause implicit commit;
- SQL Server SHALL model transaction support separately from individual DDL and lock behavior;
- SQL Server legacy compatibility SHALL be a declared profile, not an implicit fallback.

## 9. Persistent migration history

The PDO history adapter SHALL implement `MigrationHistoryStoreInterface` without weakening the immutable history model.

### 9.1 Governed table

The default history table SHOULD be named `sif_migration_history`, with platform-safe qualification controlled by configuration.

The physical model SHALL preserve at least:

- migration identifier;
- version where present;
- checksum algorithm;
- checksum value;
- direction;
- terminal status;
- execution sequence;
- execution timestamp;
- execution batch or plan fingerprint where governed by the WP-217 record model.

Column mappings SHALL be explicit and versioned.

### 9.2 Bootstrap behavior

History table creation SHALL never occur silently during history reads.

Provisioning SHALL be represented as an explicit, reviewable installation or migration operation. A missing history table SHALL produce a typed state that callers can assess before execution.

### 9.3 Append and ordering

History records SHALL be written using prepared statements and deterministic value mapping.

Ordering SHALL not rely solely on wall-clock timestamps. A stable sequence or equivalent deterministic ordering field SHALL be present.

### 9.4 Integrity

Reads SHALL preserve all values required by WP-217 integrity checking. Unknown status, malformed checksum, duplicate sequence or lossy conversion SHALL fail closed.

## 10. Lock adapters

Execution SHALL acquire a database-scoped exclusive lock through `MigrationLockInterface` before mutation.

Initial strategies SHOULD be:

- PostgreSQL advisory lock;
- MySQL named lock;
- SQL Server `sp_getapplock` / `sp_releaseapplock`.

### 10.1 Lock identity

Lock identity SHALL be derived deterministically from:

- application or repository namespace;
- connection name;
- database identity;
- governed migration scope.

Secrets SHALL not participate in lock identity.

### 10.2 Acquisition

Acquisition SHALL support a bounded timeout. Zero or finite timeout behavior SHALL be explicit.

A timeout, denial or unsupported mechanism SHALL produce `MigrationLockUnavailableException` or a narrower adapter exception translated to that public contract.

### 10.3 Release

Release SHALL be idempotent where the platform permits it. Release failure SHALL be reported without replacing the primary migration failure.

Connection loss while holding a session-scoped lock SHALL be represented explicitly in diagnostics.

## 11. Transaction adapter

The PDO transaction manager SHALL implement `MigrationTransactionManagerInterface` and expose behavior consistent with the declared capability profile.

It SHALL:

- reject nested transaction assumptions unless savepoints are explicitly supported;
- distinguish no active transaction from an externally active transaction;
- avoid committing or rolling back an externally owned transaction without explicit policy;
- preserve the primary exception when rollback also fails;
- detect transaction-state divergence where PDO and platform state disagree;
- declare whether history writes share the schema transaction.

The adapter SHALL not claim that rollback reversed statements known to cause implicit commit.

## 12. SQL operation handlers

WP-218 SHALL provide explicit handlers for governed SQL migration operations.

A SQL operation SHALL carry separate forward and optional down statements through immutable operation metadata already accepted by the WP-217 execution boundary or through an additive typed operation contract.

Handlers SHALL:

- execute statements in declared order;
- use prepared parameters for values;
- prohibit parameters from substituting SQL identifiers;
- reject empty statement sets;
- report the failing statement index safely;
- avoid logging unrestricted SQL text by default;
- return `MigrationOperationResult` values consistent with actual execution;
- declare whether down direction is supported.

Arbitrary shell, PHP callback and unrestricted file execution SHALL remain prohibited.

## 13. Identifier policy

Database identifiers cannot be safely bound as prepared-statement values.

Any configurable schema, table or lock namespace used as an identifier SHALL pass a strict platform-specific identifier policy before quoting. The policy SHALL reject control characters, comment delimiters, statement separators and malformed multipart names.

Quoting SHALL be performed by the platform adapter and SHALL not be treated as validation by itself.

## 14. Platform profiles

### 14.1 PostgreSQL

The PostgreSQL profile SHOULD use advisory locks and native prepared statements. It MAY support transactional history coupling when schema operations and history writes share the same connection and transaction.

Operations forbidden inside a transaction SHALL require an explicit non-transactional execution profile.

### 14.2 MySQL

The MySQL profile SHOULD use named locks and SHALL account for implicit commits caused by DDL and administrative statements.

The default profile SHALL not advertise complete-plan atomicity. History coupling SHALL reflect actual statement behavior.

### 14.3 SQL Server

The SQL Server profile SHOULD use application locks and PDO SQLSRV prepared execution.

It SHALL normalize lock return codes and distinguish timeout, cancellation, deadlock victim and parameter errors.

A legacy compatibility profile MAY support older SQL Server versions by avoiding unsupported syntax. Compatibility SHALL be tested independently and SHALL not reduce safety validation.

## 15. Conformance testing

WP-218 SHALL provide reusable adapter conformance tests.

Every history adapter SHALL prove:

- empty history behavior;
- deterministic ordering;
- round-trip preservation;
- append semantics;
- malformed-record rejection;
- checksum preservation;
- isolation between configured history scopes.

Every lock adapter SHALL prove:

- exclusive acquisition;
- timeout behavior;
- release;
- repeated release safety;
- contention reporting;
- connection-loss semantics where testable.

Every transaction adapter SHALL prove:

- begin, commit and rollback;
- unsupported mode rejection;
- externally active transaction policy;
- rollback-failure reporting;
- capability consistency.

Every operation handler SHALL prove:

- forward ordering;
- down ordering;
- first-failure stop behavior;
- safe diagnostics;
- no execution during dry-run;
- authorization remains enforced by WP-217 orchestration.

## 16. Test environments

Repository validation SHALL not require a live database.

The default suite SHALL use deterministic test doubles or SQLite only where semantics are not presented as conformance for PostgreSQL, MySQL or SQL Server.

Vendor integration suites MAY run conditionally through environment configuration and containers. Skipped vendor tests SHALL be explicit and SHALL not be counted as proof of platform conformance.

A platform SHALL be declared supported only after its conformance suite passes in CI or equivalent governed evidence.

## 17. Runtime integration

Runtime integration SHALL remain optional and additive.

A PDO migration service provider MAY compose:

- connection reference;
- platform profile;
- history store;
- lock adapter;
- transaction manager;
- SQL operation handlers;
- existing `MigrationRuntime`.

Registration and boot SHALL not open a remote connection or execute migrations implicitly unless an explicit lazy connection policy is unavailable and the application has opted into eager validation. Migration mutation during boot remains prohibited.

## 18. Installer integration

WP-216 integration SHALL continue through the bridge delivered by WP-217.

The PDO adapter package MAY provide an explicit installation step for provisioning the migration history table. That step SHALL be independently planned, dry-run capable, authorized and journaled.

Installer execution SHALL not grant migration authorization automatically.

## 19. Observability

Adapter operations SHOULD integrate with the existing execution context, structured logging and error-handling subsystems without making them mandatory dependencies of core contracts.

Observable events SHOULD include safe fields for:

- platform;
- connection name;
- migration identity;
- operation direction;
- lock acquisition duration;
- transaction mode;
- history append outcome;
- result classification.

SQL text, DSNs and parameter values SHALL be redacted or omitted by default.

## 20. Compatibility and dependency rules

WP-218 SHALL be additive.

It SHALL depend on WP-217 contracts. WP-217 SHALL not depend on WP-218.

Existing in-memory adapters SHALL remain valid reference implementations. Applications not registering PDO migration adapters SHALL observe no behavior change.

No public WP-217 contract may be changed incompatibly to simplify PDO implementation. Any necessary additive contract SHALL be separately specified and covered by compatibility tests.

## 21. Security requirements

WP-218 SHALL enforce:

1. no credentials in diagnostics, logs, checksums or history;
2. prepared statements for values;
3. strict identifier validation before quoting;
4. bounded lock acquisition;
5. explicit transaction capability checks;
6. no implicit history-table creation;
7. no unrestricted SQL logging;
8. no arbitrary executable migration callbacks;
9. no migration execution during application boot;
10. no silent downgrade to an unsafe platform profile;
11. no automatic history repair;
12. preservation of the primary failure when cleanup also fails.

## 22. Proposed public surface

The final names remain subject to increment-level design, but WP-218 is expected to introduce concepts equivalent to:

- `PdoMigrationConnection`;
- `PdoMigrationPlatform`;
- `PdoMigrationCapabilities`;
- `PdoMigrationHistoryStore`;
- platform lock adapters;
- `PdoMigrationTransactionManager`;
- `SqlMigrationOperation`;
- `PdoSqlMigrationOperationHandler`;
- adapter composition or factory;
- typed PDO migration exceptions;
- reusable conformance test contracts.

## 23. Increment roadmap

### I1 — Architecture

Governed architecture, boundaries, platform profiles, risks and implementation sequence.

### I2 — Connection and platform value model

Immutable connection reference, platform identity, ownership policy, capability profile and validation failures.

### I3 — Persistent history adapter

Governed history schema model, explicit provisioning plan, prepared read/write mapping and history conformance tests.

### I4 — Lock adapters

Deterministic lock identity plus PostgreSQL, MySQL and SQL Server lock strategies with conformance tests.

### I5 — Transaction coordination

PDO transaction manager, external transaction policy, savepoint capability and failure preservation.

### I6 — SQL operation model and handler

Immutable SQL operations, directional execution, safe statement diagnostics and operation-handler tests.

### I7 — Adapter composition and Installer bridge

Platform-aware composition, explicit history provisioning integration and cross-component tests.

### I8 — Runtime integration and product completion

Optional service provider, platform examples, conditional vendor suites, completion review and final validation evidence.

## 24. Acceptance criteria

WP-218 is complete when:

1. all eight increments are implemented;
2. WP-217 policy remains authoritative and unchanged;
3. platform identification and capability resolution are deterministic;
4. history persistence preserves the complete governed record model;
5. missing history infrastructure is reported without implicit creation;
6. lock acquisition is exclusive, bounded and safely released;
7. transaction behavior matches declared platform capabilities;
8. SQL operations execute only after WP-217 authorization;
9. identifiers and values follow separate safe handling rules;
10. PostgreSQL, MySQL and SQL Server profiles have reusable conformance coverage;
11. vendor support claims are backed by governed integration evidence;
12. runtime registration performs no implicit migration;
13. Composer validation succeeds;
14. PHPUnit and PHPStan succeed;
15. Builder generation and validation produce zero diagnostics;
16. the complete Work Package is tagged in Git.

## 25. Open implementation constraints

I2 SHALL not introduce history persistence, lock acquisition, transactions, SQL execution, runtime providers or Installer mutation.

The first implementation increment SHALL establish immutable connection and capability values before any adapter performs I/O.

Physical history schema details SHALL be specified before the persistent history adapter is implemented.

Platform support SHALL be evidence-based. A generic PDO test suite SHALL not be presented as proof of PostgreSQL, MySQL or SQL Server conformance.
