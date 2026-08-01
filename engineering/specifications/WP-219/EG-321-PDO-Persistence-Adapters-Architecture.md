---
id: EG-321
title: PDO Persistence Adapters Architecture
summary: Defines the governed architecture, SQL compilation boundaries, platform capability model, execution safety and incremental roadmap for PDO-backed persistence adapters over WP-209 contracts.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-31
updated: 2026-07-31
work_package: WP-219
tags:
  - foundation
  - persistence
  - database
  - pdo
  - sql
  - architecture
  - security
depends_on:
  - EG-241
  - EG-248
  - EG-313
  - EG-320
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-321 — PDO Persistence Adapters Architecture

## 1. Purpose

WP-219 establishes production-oriented PDO-backed persistence adapters for the provider-neutral contracts completed in WP-209.

The subsystem SHALL translate immutable persistence requests into deterministic, parameterized SQL execution plans and SHALL map PDO results and failures back into WP-209 values without moving SQL, connection ownership, vendor behavior or driver exceptions into the Foundation persistence core.

WP-219 SHALL support PostgreSQL, MySQL and SQL Server through explicit platform profiles and SHALL preserve compatibility with the in-memory reference adapter.

## 2. Architectural objectives

WP-219 SHALL provide:

1. immutable PDO connection and platform values suitable for persistence operations;
2. provider-neutral SQL abstract syntax and compilation contracts;
3. deterministic SQL compilation for selection, insertion, update and deletion;
4. strict separation of identifiers from bound values;
5. prepared-statement execution and typed parameter binding;
6. transaction adaptation to WP-209 connection and transaction contracts;
7. result-set and row-mapping adapters;
8. repository and unit-of-work composition over explicit metadata;
9. structured, redacted failure translation;
10. optional runtime registration without eager database access;
11. conformance tests and vendor-specific integration evidence;
12. an additive path toward BaseModel 2.0 without coupling WP-219 to an active-record API.

## 3. Dependency direction

The mandatory dependency direction is:

```text
Application / future BaseModel 2.0
              ↓
WP-219 PDO Persistence Adapters
              ↓
WP-209 Persistence Contracts
              ↓
Foundation contracts and values
```

WP-209 SHALL NOT depend on PDO, SQL dialects or WP-219 classes.

WP-219 MAY reuse compatible immutable platform or connection concepts from WP-218 only through explicit composition. Migration execution policy and persistence query policy SHALL remain separate.

## 4. Non-goals

WP-219 SHALL NOT implement:

- BaseModel 2.0 or an active-record API;
- automatic entity discovery;
- schema generation from PHP classes;
- implicit migrations;
- lazy loading or proxy generation;
- relationship orchestration;
- an identity map shared across requests;
- automatic dirty tracking;
- second-level cache;
- distributed transactions;
- connection pooling;
- SQL parsing of arbitrary user-provided statements;
- stored-procedure generation;
- a web or CLI interface;
- database credential storage;
- automatic retry of non-idempotent mutations;
- support claims for a vendor without integration evidence.

## 5. Governing principles

### 5.1 Compile before execute

Every query SHALL be normalized and compiled into an immutable execution plan before PDO interaction. Compilation is read-only and deterministic.

### 5.2 Values are never identifiers

Values SHALL be represented by bound parameters. Identifiers SHALL be validated against platform rules and quoted by the selected platform compiler. A value placeholder SHALL never be used as a substitute for a table, column, alias or ordering direction.

### 5.3 Explicit metadata

Table names, columns, keys and writable fields SHALL come from explicit repository or mapper metadata. WP-219 SHALL not infer persistence mappings from unrestricted object reflection during execution.

### 5.4 Provider-specific behavior is declared

Pagination, identifier quoting, returning clauses, affected-row semantics, boolean representation, savepoints and transaction behavior SHALL be selected through explicit platform capabilities rather than PDO driver-name branching scattered through the codebase.

### 5.5 No eager connectivity

Composition and runtime registration SHALL be side-effect free. Database connectivity begins only when an explicitly requested persistence operation requires it.

## 6. Core value model

The minimum WP-219 model SHALL include:

- `PdoPersistenceConnectionProfile`;
- `PdoPersistencePlatform`;
- `PdoPersistenceCapabilities`;
- `SqlIdentifier` and qualified identifier values;
- `SqlParameter` and parameter-type values;
- `SqlPredicate` and predicate groups;
- `SqlOrdering`;
- `SqlProjection`;
- `SqlMutationAssignment`;
- `SqlQueryPlan`;
- `CompiledSqlStatement`;
- `PdoExecutionResult`;
- `PdoResultSet`;
- `PdoPersistenceDiagnostic`.

Values SHALL reject empty names, unsupported identifier forms, duplicate parameter names, invalid pagination values, unsafe ordering directions and contradictory capability states through typed exceptions.

## 7. SQL abstract model

The SQL model SHALL represent only governed persistence operations required by WP-209:

- select with projection, predicates, sorting and pagination;
- count and existence checks;
- insert of explicit columns and values;
- update with explicit assignments and predicates;
- delete with explicit predicates;
- optional returning or generated-identity retrieval when supported.

The model SHALL NOT accept unrestricted SQL fragments as ordinary predicates, projections or ordering expressions.

Any future raw-SQL escape hatch requires a separate specification, explicit authorization and diagnostics that never expose secret values.

## 8. SQL compilation contracts

A platform compiler SHALL transform a normalized query plan into a `CompiledSqlStatement` containing:

- SQL text with placeholders;
- ordered parameter descriptors;
- expected statement kind;
- result expectations;
- platform capability evidence;
- a deterministic plan fingerprint safe for diagnostics.

Compilation SHALL be deterministic for equivalent normalized inputs.

Vendor compilers SHALL own:

- identifier quoting;
- placeholder strategy where PDO driver constraints require it;
- pagination syntax;
- generated-key or returning syntax;
- boolean and temporal literals only when literals are unavoidable;
- platform-specific mutation constraints.

## 9. Criteria translation

WP-209 criteria, sorting and pagination SHALL be translated through explicit visitors or translators.

The initial supported predicate set SHALL include:

- equality and inequality;
- greater-than and less-than comparisons;
- `LIKE` with bound patterns;
- `IN` with a validated non-empty bound value list;
- null checks;
- nested `AND` and `OR` groups.

Unsupported operators SHALL fail during planning, before statement preparation.

Empty `IN` lists SHALL have a defined deterministic policy and SHALL never generate syntactically invalid SQL.

## 10. Parameter binding

PDO execution SHALL use prepared statements and explicit binding.

The binder SHALL:

- preserve the compiled parameter order;
- map supported PHP scalar and null values to declared PDO parameter types;
- reject resources, closures and unsupported objects;
- avoid logging values by default;
- distinguish integer, boolean, string, null and large-object policies;
- avoid driver emulation assumptions unless explicitly configured and tested.

Named or positional placeholders MAY be used per platform profile, but the compiled representation SHALL be unambiguous.

## 11. Connection and transaction adaptation

WP-219 SHALL adapt PDO connectivity to WP-209 connection and transaction contracts.

Connection ownership SHALL be explicit:

- adapter-owned PDO;
- caller-provided PDO;
- factory-created PDO.

The adapter SHALL NOT close or commit a caller-owned connection without explicit contract authority.

Transaction operations SHALL preserve:

- begin, commit and rollback ownership;
- externally active transaction policy;
- savepoint capability where supported;
- primary failure when rollback also fails;
- typed reporting of connection loss and invalid transaction state.

WP-218 migration transaction classes MAY inform shared low-level primitives, but migration-specific authorization and history semantics SHALL not leak into persistence operations.

## 12. Result-set and mapping boundary

PDO rows SHALL be converted to provider-neutral result-set values before repository mapping.

The result adapter SHALL define:

- associative fetch mode;
- stable column-name handling;
- empty result behavior;
- single-row cardinality checks;
- affected-row reporting;
- generated identity retrieval;
- safe disposal of cursor resources.

Entity or DTO construction belongs to injected WP-209 mapper contracts. WP-219 SHALL not require persisted objects to extend a framework base class.

## 13. Repository composition

A PDO repository adapter SHALL compose:

- a WP-209 repository contract;
- explicit persistence metadata;
- a query planner/compiler;
- a PDO statement executor;
- a mapper;
- optional transaction coordination.

Repository metadata SHALL declare at least:

- table identifier;
- selectable columns;
- writable columns;
- primary key columns;
- generated-key policy;
- mapper identity.

Composite keys SHALL be first-class and SHALL not be encoded as concatenated strings.

## 14. Unit of Work

Initial WP-219 Unit of Work support SHALL be explicit and bounded.

It MAY coordinate registered repository operations within one compatible PDO transaction. It SHALL NOT introduce automatic dirty tracking, hidden flush-on-shutdown behavior or cross-connection atomicity.

Execution order, failure handling and rollback outcome SHALL be deterministic and reportable.

## 15. Platform profiles

The initial platform catalog SHALL include:

### PostgreSQL

- double-quoted identifiers;
- `LIMIT` and `OFFSET`;
- `RETURNING` where enabled;
- transactional behavior declared by capability;
- native boolean handling.

### MySQL

- backtick-quoted identifiers;
- `LIMIT` and `OFFSET`;
- generated identity through supported PDO behavior;
- explicit handling of transaction and affected-row differences.

### SQL Server

- bracket or standards-compatible quoting selected consistently;
- deterministic `ORDER BY` requirement for offset pagination;
- `OFFSET ... FETCH` for supported versions;
- generated identity strategy declared explicitly;
- capability profile separated from legacy SQL Server support.

SQL Server 2000 compatibility SHALL NOT be claimed by the modern default profile. Any legacy adapter requires a distinct profile, query compiler and test matrix.

## 16. Failure taxonomy and redaction

WP-219 SHALL translate PDO and driver failures into typed persistence exceptions rooted in WP-209-compatible failure categories.

At minimum, translation SHALL distinguish:

- connectivity failure;
- authentication or authorization failure;
- statement preparation failure;
- constraint violation;
- deadlock or serialization conflict when safely identifiable;
- invalid transaction state;
- unsupported platform capability;
- result cardinality failure;
- mapping failure;
- unknown provider failure.

Default diagnostics SHALL exclude:

- passwords and DSNs with credentials;
- bound parameter values;
- complete raw SQL when it may contain literals;
- server filesystem paths;
- vendor stack traces intended only for controlled debugging.

## 17. Runtime integration

WP-219 MAY publish optional runtime services such as:

```text
persistence
persistence.pdo
```

Registration SHALL be additive, lazy and side-effect free.

Runtime boot SHALL NOT:

- open database connections;
- execute health queries;
- mutate schema or data;
- begin transactions;
- discover unrestricted entity classes.

## 18. Compatibility

WP-219 is additive.

The WP-209 in-memory adapter remains valid. Applications that do not register PDO persistence services observe no behavior change.

No WP-209 public contract may be weakened to accommodate PDO. Any required contract extension SHALL be separately specified, additive where possible and covered by compatibility tests.

## 19. Testing strategy

Testing SHALL include:

1. pure unit tests for values, planners, compilers and translators;
2. reusable compiler conformance tests;
3. executor tests using controlled PDO doubles where technically honest;
4. SQLite only for generic PDO mechanics, never as proof of vendor dialect support;
5. conditional PostgreSQL integration tests;
6. conditional MySQL integration tests;
7. conditional SQL Server integration tests;
8. transaction ownership and rollback-failure tests;
9. secret-redaction tests;
10. deterministic compilation and fingerprint tests;
11. runtime registration tests proving no eager I/O;
12. full Composer, PHPUnit, PHPStan and Builder validation.

A vendor SHALL be documented as supported only after its integration suite passes against declared versions.

## 20. Increment roadmap

WP-219 SHALL be delivered in eight increments:

### I1 — Architecture

Governed boundaries, SQL safety model, platform profiles, failure policy and implementation roadmap.

### I2 — Connection, platform and SQL value model

Immutable connection ownership, platform capability, identifier, parameter and compiled-statement values. No database I/O.

### I3 — Query AST and criteria translation

Select, count, insert, update and delete plans plus translation from WP-209 criteria, sorting and pagination.

### I4 — Platform SQL compilers

Deterministic PostgreSQL, MySQL and SQL Server compilation with identifier quoting and pagination policies.

### I5 — PDO statement execution and result sets

Preparation, binding, execution, cursor/result adaptation, generated identity and typed failure translation.

### I6 — Connection and transaction adapters

WP-209 connection/transaction implementations, ownership semantics, savepoints and rollback-failure handling.

### I7 — Repository, mapper and Unit of Work composition

Explicit metadata, composite keys, repository operations and bounded transaction coordination.

### I8 — Runtime integration and product completion

Service provider, optional runtime publication, examples, complete reviews, conformance evidence and governed closure.

## 21. Completion criteria

WP-219 is complete when:

- increments I1 through I8 are integrated;
- no SQL value is interpolated as an identifier;
- supported operations compile deterministically for each declared platform;
- repository and transaction adapters satisfy WP-209 contracts;
- runtime composition performs no eager database I/O;
- vendor support claims are backed by integration evidence;
- the full PHPUnit suite passes;
- PHPStan level 8 reports zero errors;
- Builder reports zero diagnostics and deterministic generation;
- `git diff --check` passes;
- implementation and completion reviews are present.

## 22. Deferred work

The following remain separate work packages:

- BaseModel 2.0 integration;
- relationships and lazy loading;
- identity map and automatic dirty tracking;
- schema/query DSL expansion;
- legacy SQL Server adapters;
- connection pooling;
- retry policies;
- cache integration;
- read/write connection routing;
- telemetry specific to database operations.
