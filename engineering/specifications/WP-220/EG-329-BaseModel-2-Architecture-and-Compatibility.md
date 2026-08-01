---
id: EG-329
title: BaseModel 2.0 Architecture and Compatibility
summary: Defines the governed architecture, persistence boundary, model lifecycle, metadata strategy, compatibility policy and eight-increment delivery roadmap for BaseModel 2.0.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-31
updated: 2026-07-31
work_package: WP-220
tags:
  - foundation
  - basemodel
  - persistence
  - active-record
  - audit
  - events
  - compatibility
  - architecture
depends_on:
  - EG-213
  - EG-226
  - EG-233
  - EG-238
  - EG-241
  - EG-245
  - EG-248
  - EG-321
  - EG-328
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-329 — BaseModel 2.0 Architecture and Compatibility

## 1. Purpose

WP-220 establishes BaseModel 2.0 as the governed, high-level model API of SIF over the provider-neutral Persistence contracts and the PDO persistence adapters completed in WP-219.

BaseModel 2.0 SHALL provide a productive model-oriented API without making the Foundation persistence layer depend on active-record semantics, global database state, unrestricted reflection or a specific SQL driver.

The subsystem SHALL preserve a deliberate migration path for existing SIF applications that use the legacy BaseModel conventions while making new behavior explicit, testable and compatible with audit, events, execution context, repositories, transactions and Unit of Work.

## 2. Architectural objectives

WP-220 SHALL provide:

1. explicit immutable model metadata;
2. typed attribute storage and guarded mass assignment;
3. deterministic hydration and serialization;
4. original-value snapshots and dirty tracking;
5. simple and composite key support;
6. repository-backed CRUD without SQL in model classes;
7. query entry points over the existing provider-neutral Query model;
8. pagination, ordering and soft-delete policies;
9. lifecycle hooks with deterministic ordering;
10. model lifecycle events through the Event Dispatcher;
11. execution-context propagation and automatic audit change sets;
12. relationship metadata and bounded relationship loading;
13. optional Unit of Work participation;
14. runtime composition without eager database access;
15. an explicit compatibility and deprecation strategy for legacy BaseModel consumers.

## 3. Dependency direction

The mandatory dependency direction is:

```text
Application models
      ↓
WP-220 BaseModel 2.0
      ↓
WP-209 Persistence contracts
      ↓
WP-219 PDO Persistence adapters (optional concrete runtime)
```

For cross-cutting behavior:

```text
BaseModel 2.0 → Event Dispatcher contracts
BaseModel 2.0 → Execution Context contracts
BaseModel 2.0 → Audit contracts
```

Event, Context, Audit, Persistence and PDO adapters SHALL NOT depend on BaseModel 2.0.

BaseModel 2.0 SHALL NOT execute SQL directly. All persistence operations SHALL pass through repositories, mappers, transactions and Unit of Work contracts.

## 4. Positioning

BaseModel 2.0 is an application-facing abstraction, not a replacement for Persistence.

Persistence remains authoritative for:

- queries;
- repositories;
- storage records;
- mapping;
- connections;
- transactions;
- Unit of Work;
- capabilities and failures.

BaseModel 2.0 owns:

- model metadata;
- attributes;
- hydration and serialization conventions;
- dirty tracking;
- lifecycle hooks;
- soft-delete policy;
- relationship declarations;
- developer-facing CRUD and query ergonomics.

Applications MAY use Persistence repositories directly without BaseModel.

## 5. Non-goals

WP-220 SHALL NOT implement:

- a general-purpose full ORM;
- transparent lazy-loading proxies;
- implicit database connections;
- automatic schema generation from model classes;
- implicit migrations;
- arbitrary SQL fragments in model APIs;
- hidden persistence during object destruction or shutdown;
- cross-request identity maps;
- distributed Unit of Work;
- automatic cascade operations without explicit metadata;
- unrestricted reflection-based field discovery;
- dynamic properties;
- public mutable global connection state;
- magic relationship loading that performs unnoticed I/O;
- silent compatibility emulation for every historical BaseModel behavior.

## 6. Core model contract

A BaseModel 2.0 instance SHALL represent application state and SHALL NOT own a PDO connection.

The model runtime SHALL provide the repository and metadata required for persistence. Model instances SHALL remain usable for local attribute operations when no runtime is configured, but persistence methods SHALL fail explicitly.

The initial public model surface is expected to include concepts equivalent to:

```text
attributes
original attributes
exists state
wasRecentlyCreated state
dirty fields
primary key values
fill / forceFill
hydrate
serialize / toArray
save / delete / restore
refresh
query entry point
```

Exact method names become normative in the implementation increments.

## 7. Metadata model

### 7.1 Explicit metadata

Every model SHALL resolve immutable metadata containing at least:

- managed model class;
- repository name or resolver;
- table identity when required by concrete composition;
- key fields;
- fillable fields;
- guarded fields;
- hidden fields;
- visible fields;
- casts;
- date/time fields;
- soft-delete configuration;
- relationship definitions;
- audit policy;
- lifecycle policy.

Metadata MAY be declared by protected static methods, dedicated metadata classes or a registry. The final implementation SHALL normalize all declarations into one immutable metadata value.

### 7.2 No unrestricted reflection

Reflection MAY support validated declarations, but SHALL NOT persist every object property automatically.

Dynamic properties are prohibited.

### 7.3 Caching

Normalized metadata MAY be cached per model class. Cache construction SHALL be deterministic and side-effect free.

## 8. Attributes and mass assignment

Attributes SHALL be stored in a controlled internal collection.

Mass assignment SHALL obey explicit fillable and guarded policies. Unknown attributes SHALL either be rejected or handled by an explicit compatibility policy; they SHALL NOT create dynamic PHP properties.

`forceFill`-equivalent behavior MAY bypass mass-assignment guards but SHALL still validate attribute names and supported values.

Attribute casting SHALL be deterministic and reversible where possible. Casting failures SHALL identify the model and field but SHALL redact sensitive values.

## 9. Hydration and serialization

Hydration SHALL be distinct from mass assignment.

Hydration from persistence SHALL:

1. accept trusted mapped storage values;
2. apply inbound casts;
3. set the original snapshot;
4. mark the model as existing;
5. clear dirty state;
6. avoid lifecycle persistence hooks.

Serialization SHALL:

- apply outbound casts;
- honor visible and hidden fields;
- produce deterministic key ordering;
- exclude internal runtime objects;
- support audit snapshots without leaking guarded secrets;
- avoid triggering database access.

## 10. Dirty tracking

Every hydrated or successfully persisted model SHALL retain an original attribute snapshot.

The model SHALL expose:

- whether any attribute is dirty;
- whether specific attributes are dirty;
- current dirty values;
- original values;
- a structured change set.

Comparison SHALL occur after normalization so equivalent casted values do not produce false changes.

A successful save or refresh SHALL synchronize the original snapshot. Failed persistence SHALL preserve the pre-operation snapshot and dirty state.

## 11. Keys and identity

BaseModel 2.0 SHALL support:

- single-column keys;
- composite keys;
- assigned keys;
- database-generated keys when the repository reports the capability.

Composite keys SHALL remain structured values and SHALL NOT be concatenated into ambiguous strings.

Model identity within one Unit of Work MAY use the managed type plus the canonical structured key.

## 12. CRUD boundary

CRUD methods SHALL delegate to a model repository adapter over Persistence.

The flow for save is:

```text
model metadata and state
        ↓
model mapper / repository adapter
        ↓
Persistence repository
        ↓
transaction / Unit of Work
        ↓
concrete persistence adapter
```

The model SHALL NOT construct SQL, quote identifiers or bind PDO parameters.

Save SHALL distinguish insert and update from explicit existence state and repository results, not solely from whether a key value is null.

Delete SHALL support physical deletion and configured soft deletion. Restore SHALL only be available when soft deletion is configured.

## 13. Query API

A model query entry point SHALL create or obtain a provider-neutral query builder backed by `Query`, `QueryCriteria`, `SortOrder`, `Projection` and `Pagination`.

The BaseModel API MAY provide convenience methods corresponding to:

```text
find
findOrFail
where
orderBy
limit
paginate
first
all
count
```

Convenience methods SHALL compile to the existing provider-neutral query model. They SHALL NOT introduce arbitrary SQL strings.

## 14. Soft deletion

Soft deletion SHALL be an explicit metadata policy containing:

- deletion marker field;
- marker strategy, initially timestamp or boolean;
- default query scope;
- restore behavior;
- force-delete behavior.

Queries SHALL exclude soft-deleted rows by default only when the model metadata enables the policy.

APIs equivalent to `withTrashed`, `onlyTrashed` and `withoutTrashed` MAY be provided through explicit query-scope values.

## 15. Lifecycle hooks

Lifecycle hooks SHALL be explicit and deterministic.

The initial lifecycle is expected to cover:

```text
hydrating / hydrated
saving / saved
creating / created
updating / updated
deleting / deleted
restoring / restored
refreshing / refreshed
```

Pre-operation hooks MAY veto an operation through an explicit result or exception. Hook failures SHALL abort persistence and preserve model state.

Hook ordering SHALL be documented and tested. Static global callback registries are not the primary composition mechanism.

## 16. Events

Model lifecycle events SHALL be emitted through `EventDispatcherInterface`.

Events SHALL carry:

- model type;
- structured model identity when available;
- operation;
- change set where applicable;
- execution context reference or safe context metadata;
- timestamp or clock-derived occurrence metadata.

Events SHALL NOT serialize the complete model object by default.

No event SHALL be emitted as successfully completed before the persistence operation and transaction boundary have succeeded.

## 17. Execution context and audit

BaseModel 2.0 SHALL integrate with existing Context and Audit contracts rather than reading HTTP globals, sessions or authentication implementations.

Auditable models SHALL provide or adapt:

- audit subject;
- before snapshot;
- after snapshot;
- structured change set;
- safe metadata.

Audit emission SHALL occur after successful persistence, respecting the selected transaction policy. Sensitive attributes SHALL be redacted through the existing audit redaction boundary.

Applications without Audit or Context configured SHALL continue to function through optional or null compositions.

## 18. Relationships

Relationships SHALL be declared through immutable metadata.

The initial supported relationship forms are planned as:

```text
belongs-to
has-one
has-many
```

Relationship metadata SHALL define:

- related model type;
- local fields;
- foreign fields;
- cardinality;
- default ordering where applicable;
- loading policy.

I/O SHALL be explicit. Accessing an unloaded relationship SHALL not silently query the database unless a future opt-in lazy-loading policy is explicitly enabled.

Batch/eager loading SHALL avoid one-query-per-record behavior where supported.

## 19. Unit of Work participation

Models MAY be registered as new, dirty or removed in the existing Unit of Work.

Flush SHALL remain explicit. BaseModel SHALL NOT automatically flush on object destruction or application shutdown.

The Unit of Work SHALL use repositories and transaction managers; model hooks and events SHALL observe one documented order around transaction completion.

## 20. Runtime composition

Runtime composition SHALL be optional and side-effect free.

Registering BaseModel services SHALL NOT:

- open a database connection;
- run a query;
- inspect a database schema;
- migrate a database;
- persist a model;
- flush a Unit of Work.

A model runtime is expected to compose:

- metadata registry;
- repository resolver;
- mapper registry or resolver;
- transaction manager;
- Unit of Work factory;
- event dispatcher;
- execution-context provider;
- audit service or emitter;
- clock and diagnostics.

## 21. Compatibility strategy

### 21.1 Additive introduction

BaseModel 2.0 SHALL initially be additive. Existing applications SHALL not change behavior unless they opt into the new model runtime or extend the new BaseModel class.

### 21.2 Legacy compatibility layer

A dedicated compatibility layer MAY expose selected legacy conventions, including:

- static-style `find` and query entry points;
- `fillable` declarations;
- `toArray`;
- simple and composite primary-key declarations;
- soft-delete conventions;
- familiar save/delete method names.

The compatibility layer SHALL delegate to the new runtime and SHALL emit deprecation diagnostics for unsupported or ambiguous behavior.

### 21.3 Prohibited compatibility behavior

Compatibility SHALL NOT reintroduce:

- dynamic properties;
- hidden global connections;
- direct SQL concatenation;
- automatic persistence of undeclared fields;
- silent type coercion that loses data;
- implicit cross-connection transactions;
- database access during metadata resolution.

### 21.4 Migration guide

WP-220 SHALL deliver a migration guide mapping legacy BaseModel declarations and common methods to BaseModel 2.0 equivalents.

## 22. Failure model

BaseModel failures SHALL extend the Foundation exception hierarchy and distinguish at least:

- invalid metadata;
- unknown or guarded attribute;
- cast failure;
- missing runtime or repository;
- invalid model state;
- key resolution failure;
- hydration failure;
- lifecycle veto or hook failure;
- relationship definition or loading failure;
- unsupported capability;
- persistence failure.

Public messages SHALL not include credentials, bound values, full row data or sensitive attribute contents.

## 23. Testing strategy

WP-220 SHALL include:

1. unit tests for metadata and attributes;
2. hydration, casting and serialization tests;
3. dirty-tracking and snapshot tests;
4. CRUD tests using in-memory Persistence;
5. PDO-backed integration through WP-219 doubles and conditional database suites;
6. simple and composite key tests;
7. soft-delete scope tests;
8. lifecycle ordering and veto tests;
9. event, context and audit integration tests;
10. relationship and eager-loading tests;
11. Unit of Work transaction-order tests;
12. compatibility tests for documented legacy behavior;
13. PHPStan level 8 and governed Builder validation.

## 24. Delivery roadmap

WP-220 SHALL be delivered in eight increments:

### I1 — Architecture and compatibility

- dependency boundaries;
- metadata and lifecycle design;
- compatibility policy;
- delivery roadmap.

### I2 — Immutable metadata and attribute model

- model metadata;
- attribute names and collections;
- fillable/guarded policy;
- casts and key metadata.

### I3 — Hydration, serialization and dirty tracking

- hydration pipeline;
- original snapshots;
- change sets;
- visibility and serialization.

### I4 — CRUD and model repository bridge

- repository resolver;
- save/insert/update/delete/refresh;
- simple and composite keys;
- generated-key handling.

### I5 — Query API, pagination and soft deletion

- model query builder;
- provider-neutral criteria;
- pagination;
- default soft-delete scopes and restore.

### I6 — Hooks, events, context and audit

- lifecycle hooks;
- event publication;
- execution-context propagation;
- audit snapshots and changes.

### I7 — Relationships and Unit of Work

- relationship metadata;
- explicit and eager loading;
- Unit of Work registration and flush coordination.

### I8 — Runtime integration, compatibility migration and completion

- Service Provider and runtime composition;
- compatibility adapter;
- migration guide;
- end-to-end acceptance and product review.

## 25. Acceptance criteria for WP-220

WP-220 is complete only when:

- all eight increments are implemented and reviewed;
- BaseModel performs no direct PDO or SQL work;
- metadata, hydration and dirty tracking are deterministic;
- simple and composite keys are supported;
- CRUD operates through Persistence repositories;
- query, pagination and soft delete are covered;
- lifecycle events occur in documented order;
- audit and context remain optional and redacted;
- relationships avoid hidden I/O by default;
- Unit of Work flushing is explicit;
- the compatibility guide is delivered;
- the full PHPUnit suite passes;
- PHPStan level 8 reports no errors;
- SIF Builder reports zero diagnostics and idempotent governed artifacts.
